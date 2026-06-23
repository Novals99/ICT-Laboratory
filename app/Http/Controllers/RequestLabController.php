<?php

namespace App\Http\Controllers;

use App\Exports\RequestLabExport;
use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\AssetLog;
use App\Models\Laboratory;
use App\Models\RequestItem;
use App\Models\RequestLab;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestLabController extends Controller
{
    public function index(Request $request)
    {
        $query = RequestLab::with([
            'user',
            'lab',
            'request_items.asset',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT('REQ-', LPAD(id, 3, '0')) = ?", [$search])
                    ->orWhereHas('user', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('request_status', (array) $request->status);
        }

        if ($request->filled('role')) {
            $query->whereHas('user', function ($user) use ($request) {
                $user->where('role', $request->role);
            });
        }

        if ($request->filled('request_role')) {
            $query->whereHas('user', function ($user) use ($request) {
                $user->whereIn('role', (array) $request->request_role);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereDate('request_date', $request->date_to);
        }

        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        if ($request->sort === 'asc') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $requests = $query
            ->orderBy('id', request('sort') === 'oldest' ? 'asc' : 'desc')
            ->paginate(11)
            ->withQueryString();

        $user = auth()->user();
        $laboratories = Laboratory::orderBy('lab_name')->get();
        $assets = Asset::orderBy('asset_name')->get();

        return view('pages.requestlab.index', compact('requests', 'laboratories', 'assets'));
    }

    public function detail($id)
    {
        $labRequest = RequestLab::with([
            'user',
            'request_items.asset',
            'request_items.serialNumbers',
        ])->findOrFail($id);

        return response()->json([
            'request_id' => 'REQ-'.str_pad($labRequest->id, 3, '0', STR_PAD_LEFT),
            'user_name' => $labRequest->user->name ?? '-',
            'total_request' => $labRequest->request_items->sum('total_request'),
            'electronic' => $this->itemsForCategory($labRequest, 'electronic'),
            'non_electronic' => $this->itemsForCategory($labRequest, 'non-electronic'),
            'component_pc' => $this->itemsForCategory($labRequest, 'component-pc'),
        ]);
    }

    public function store(Request $request)
    {
            abort_unless(
        auth()->user()->labs->pluck('id')->contains($request->lab_id),
        403, 'Lab tersebut bukan milik Anda.'
    );
        $validated = $request->validate([
            'lab_id' => ['required', 'exists:laboratories,id'],
            'request_date' => ['required', 'date'],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'array'],
            'items.*.asset_id' => ['nullable', 'exists:assets,id'],
            'items.*.total_request' => ['nullable', 'integer', 'min:1'],
            'items.*.*.asset_id' => ['nullable', 'exists:assets,id'],
            'items.*.*.total_request' => ['nullable', 'integer', 'min:1'],
        ]);

        $items = collect($validated['items'])
            ->flatMap(function ($itemOrCategoryItems) {
                if (isset($itemOrCategoryItems['asset_id']) || isset($itemOrCategoryItems['total_request'])) {
                    return [$itemOrCategoryItems];
                }

                return $itemOrCategoryItems ?? [];
            })
            ->filter(fn ($item) => ! empty($item['asset_id']) && ! empty($item['total_request']))
            ->values();

        if ($items->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Pilih minimal satu asset yang ingin direquest.']);
        }

        DB::transaction(function () use ($validated, $items) {
            $labRequest = RequestLab::create([
                'user_id' => auth()->id(),
                'lab_id' => $validated['lab_id'],
                'request_date' => $validated['request_date'],
                'request_status' => 'pending',
            ]);

            foreach ($items as $item) {
                $labRequest->request_items()->create([
                    'asset_id' => $item['asset_id'],
                    'total_request' => $item['total_request'],
                    'status' => 'pending',
                ]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => 'Created laboratory request: REQ-' .
                    str_pad($labRequest->id, 3, '0', STR_PAD_LEFT),
            ]);

        });

        return redirect()->route('requestlab.index')
            ->with('success', 'Request berhasil ditambahkan.');
    }

    public function updateItemStatus(Request $request, $itemId)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'serial_ids' => 'nullable|array',
            'serial_ids.*' => 'exists:asset_serial_numbers,id',
        ]);

        $item = RequestItem::findOrFail($itemId);
        $labRequest = RequestLab::findOrFail($item->request_lab_id);

        $asset = $item->asset;
        $usesSerial = in_array($asset->asset_category, ['electronic', 'component-pc', 'pc']);

        if ($validated['status'] === 'approved' && $usesSerial) {
            $serialIds = $validated['serial_ids'] ?? [];
            if (count($serialIds) > $item->total_request) {
                return response()->json([
                    'success' => false,
                    'message' => "Number of selected serial numbers cannot exceed the requested quantity ({$item->total_request})."
                ], 422);
            }
            
            // Check that they match the asset and are available in SPV warehouse (lab_id is null) or already assigned to this request item
            $validSerialsCount = \App\Models\AssetSerialNumber::where('asset_id', $item->asset_id)
                ->where(function($q) use ($item) {
                    $q->whereNull('lab_id')
                      ->orWhere('request_item_id', $item->id);
                })
                ->whereIn('id', $serialIds)
                ->count();
            if ($validSerialsCount !== count($serialIds)) {
                return response()->json([
                    'success' => false,
                    'message' => "Some selected serial numbers are invalid or no longer available."
                ], 422);
            }
        }

        try {
            DB::transaction(function () use ($item, $validated, $labRequest) {
                $this->applyItemStatus($item, $labRequest, $validated['status'], $validated['serial_ids'] ?? []);

                // Recalculate status request
                $requestStatus = $this->resolveRequestStatus($labRequest->fresh()->request_items);
                $labRequest->update(['request_status' => $requestStatus]);
                $assetName = $item->asset->asset_name ?? 'Unknown Asset';

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'activity' => ucfirst($validated['status']) .
                        ' requested asset: ' . $assetName .
                        ' (REQ-' . str_pad($labRequest->id, 3, '0', STR_PAD_LEFT) . ')',
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'request_status' => $labRequest->fresh()->request_status,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $labRequest = RequestLab::with('request_items')->findOrFail($id);

        try {
            DB::transaction(function () use ($labRequest, $validated) {
                foreach ($labRequest->request_items as $item) {
                    $serialIds = [];
                    $asset = $item->asset;
                    $usesSerial = in_array($asset->asset_category, ['electronic', 'component-pc', 'pc']);
                    if ($validated['status'] === 'approved' && $usesSerial) {
                        $serialIds = \App\Models\AssetSerialNumber::where('asset_id', $item->asset_id)
                            ->whereNull('lab_id')
                            ->where('status', 'available')
                            ->limit($item->total_request)
                            ->pluck('id')
                            ->toArray();
                        if (count($serialIds) < $item->total_request) {
                            throw new \Exception("Stok serial number untuk {$asset->asset_name} tidak mencukupi di gudang.");
                        }
                    }
                    $this->applyItemStatus($item, $labRequest, $validated['status'], $serialIds);
                }

                $requestStatus = $this->resolveRequestStatus($labRequest->fresh()->request_items);
                $labRequest->update(['request_status' => $requestStatus]);

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'activity' => ucfirst($validated['status']) .
                        ' laboratory request: REQ-' .
                        str_pad($labRequest->id, 3, '0', STR_PAD_LEFT),
                ]);

            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'request_status' => $labRequest->fresh()->request_status,
        ]);
    }

    private function resolveRequestStatus($items): string
    {
        $total = $items->count();
        if ($total === 0) return 'pending';

        $pending  = $items->where('status', 'pending')->count();
        $approved = $items->where('status', 'approved')->count();
        $rejected = $items->where('status', 'rejected')->count();

        if ($pending === $total) return 'pending';
        if ($pending > 0) return 'partial';
        return 'done';
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

        DB::beginTransaction();
        try {
            $requestLab = RequestLab::findOrFail($id);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity' => 'Deleted laboratory request: REQ-' .
                    str_pad($requestLab->id, 3, '0', STR_PAD_LEFT),
            ]);

            $requestLab->delete();
            
            DB::commit();

            return redirect()->route('requestlab.index')
                ->with('success', 'Request berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('requestlab.index')
                ->with('error', 'Request gagal dihapus.');
        }
    }

    public function edit($id)
    {
        return redirect()->route('requestlab.index');
    }

    public function update(Request $request, $id)
    {

        return redirect()->route('requestlab.index')
            ->with('error', 'Edit request lab dilakukan melalui status item di popup detail.');
    }

    public function exportPdf()
    {
        return $this->export('pdf');
    }

    public function export(string $format)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

        $export = new RequestLabExport();

        return match ($format) {
            'pdf' => $export->downloadPdf(),
            'excel' => $export->downloadExcel(),
            'csv' => $export->downloadCsv(),
            default => abort(404),
        };
    }

    private function itemsForCategory(RequestLab $labRequest, string $category): array
    {
        return $labRequest->request_items()
            ->whereHas('asset', fn ($q) => $q->where('asset_category', $category))
            ->get()
            ->map(fn ($item) => [
                'item_id' => $item->id,
                'asset_id' => $item->asset_id,
                'asset_name' => $item->asset->asset_name ?? '-',
                'quantity' => $item->total_request,
                'status' => $item->status ?? 'pending',
                'category' => $category,
                'serials' => $item->serialNumbers->map(fn($s) => [
                    'id' => $s->id,
                    'serial_number' => $s->serial_number
                ])->toArray()
            ])
            ->toArray();
    }

    private function applyItemStatus(RequestItem $item, RequestLab $labRequest, string $newStatus, array $serialIds = []): void
    {
        $oldStatus = $item->status ?? 'pending';
        $qty = (int) $item->total_request;

        if ($oldStatus === $newStatus) {
            $item->update(['status' => $newStatus]);
            return;
        }

        if ($oldStatus === 'approved') {
            $assetLab = AssetLab::where('lab_id', $labRequest->lab_id)
                ->where('asset_id', $item->asset_id)
                ->first();

            $availableInLab = $assetLab->total_good_lab ?? 0;
            if (!$assetLab || $availableInLab < $qty) {
                $assetName = Asset::find($item->asset_id)?->asset_name ?? "Asset #{$item->asset_id}";
                throw new \Exception(
                    "Tidak bisa membatalkan approval {$assetName}: stok di lab sudah berubah (tersedia {$availableInLab}, butuh {$qty}). Kemungkinan stok sudah terpakai."
                );
            }

            $asset = Asset::findOrFail($item->asset_id);
            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;

            $asset->total_good += $qty;
            $asset->total_asset = $asset->total_good + $asset->total_damaged + $asset->total_loss;
            $asset->save();

            $assetLab->total_good_lab -= $qty;
            $assetLab->total_asset_lab = $assetLab->total_good_lab + $assetLab->total_damaged_lab + $assetLab->total_loss_lab;
            $assetLab->save();

            // Reclaim serial numbers back to SPV warehouse
            \App\Models\AssetSerialNumber::where('request_item_id', $item->id)
                ->update([
                    'lab_id' => null,
                    'request_item_id' => null,
                    'status' => 'available'
                ]);

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'distribution',
                'quantity' => $qty,
                'to_lab_id' => $labRequest->lab_id,
                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,
                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,
                'before_total_damaged' => $asset->total_damaged,
                'after_total_damaged' => $asset->total_damaged,
                'before_total_loss' => $asset->total_loss,
                'after_total_loss' => $asset->total_loss,
                'source' => 'requestlab:REQ-' . str_pad($labRequest->id, 3, '0', STR_PAD_LEFT),
                'notes' => 'Pembatalan approval request — stok ditarik balik dari lab ke gudang.',
            ]);
        }

        if ($newStatus === 'approved') {
            $asset = Asset::findOrFail($item->asset_id);

            if ($asset->total_good < $qty) {
                throw new \Exception("Stok {$asset->asset_name} tidak mencukupi (tersedia: {$asset->total_good}).");
            }

            $beforeTotalAsset = $asset->total_asset;
            $beforeGood = $asset->total_good;

            $asset->total_good -= $qty;
            $asset->total_asset = $asset->total_good + $asset->total_damaged + $asset->total_loss;
            $asset->save();

            $assetLab = AssetLab::firstOrCreate(
                ['lab_id' => $labRequest->lab_id, 'asset_id' => $item->asset_id],
                ['total_good_lab' => 0, 'total_damaged_lab' => 0, 'total_loss_lab' => 0, 'total_asset_lab' => 0]
            );
            $assetLab->total_good_lab += $qty;
            $assetLab->total_asset_lab = $assetLab->total_good_lab + $assetLab->total_damaged_lab + $assetLab->total_loss_lab;
            $assetLab->save();

            // Reclaim any currently linked serials for this item first to avoid duplicates
            \App\Models\AssetSerialNumber::where('request_item_id', $item->id)
                ->update([
                    'lab_id' => null,
                    'request_item_id' => null,
                    'status' => 'available'
                ]);

            // Link selected serial numbers to the laboratory and request item
            if (!empty($serialIds)) {
                \App\Models\AssetSerialNumber::whereIn('id', $serialIds)
                    ->update([
                        'lab_id' => $labRequest->lab_id,
                        'request_item_id' => $item->id,
                        'status' => 'available'
                    ]);
            }

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'type' => 'distribution',
                'quantity' => $qty,
                'to_lab_id' => $labRequest->lab_id,
                'before_total_asset' => $beforeTotalAsset,
                'after_total_asset' => $asset->total_asset,
                'before_total_good' => $beforeGood,
                'after_total_good' => $asset->total_good,
                'before_total_damaged' => $asset->total_damaged,
                'after_total_damaged' => $asset->total_damaged,
                'before_total_loss' => $asset->total_loss,
                'after_total_loss' => $asset->total_loss,
                'source' => 'requestlab:REQ-' . str_pad($labRequest->id, 3, '0', STR_PAD_LEFT),
                'notes' => 'Distribusi stok ke lab via approval Request Lab.',
            ]);
        }

        $item->update(['status' => $newStatus]);
    }
}
