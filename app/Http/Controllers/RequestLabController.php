<?php

namespace App\Http\Controllers;

use App\Exports\RequestLabExport;
use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\Laboratory;
use App\Models\RequestItem;
use App\Models\RequestLab;
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

        $requests = $query
            ->latest()
            ->paginate(11)
            ->withQueryString();

        $laboratories = Laboratory::orderBy('lab_name')->get();
        $assets = Asset::orderBy('asset_name')->get();

        return view('pages.requestlab.index', compact('requests', 'laboratories', 'assets'));
    }

    public function detail($id)
    {
        $labRequest = RequestLab::with([
            'user',
            'request_items.asset',
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
        abort_unless(in_array(auth()->user()->role, ['admin', 'pic', 'assistant'], true), 403);

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
        });

        return redirect()->route('requestlab.index')
            ->with('success', 'Request berhasil ditambahkan.');
    }

    public function updateItemStatus(Request $request, $itemId)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $item = RequestItem::findOrFail($itemId);

        $labRequest = RequestLab::findOrFail($item->request_lab_id);

        try {
            DB::transaction(function () use ($item, $validated, $labRequest) {
                $this->applyItemStatus($item, $labRequest, $validated['status']);

                // Recalculate status request
                $requestStatus = $this->resolveRequestStatus($labRequest->fresh()->request_items);
                $labRequest->update(['request_status' => $requestStatus]);
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
                    $this->applyItemStatus($item, $labRequest, $validated['status']);
                }

                $requestStatus = $this->resolveRequestStatus($labRequest->fresh()->request_items);
                $labRequest->update(['request_status' => $requestStatus]);
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
        if ($approved === $total) return 'approved';
        if ($rejected === $total) return 'rejected';
        return 'partial';
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

        DB::transaction(function () use ($id) {
            RequestLab::findOrFail($id)->delete();
        });

        return redirect()->route('requestlab.index')
            ->with('success', 'Request berhasil dihapus.');
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
                'asset_name' => $item->asset->asset_name ?? '-',
                'quantity' => $item->total_request,
                'status' => $item->status ?? 'pending',
            ])
            ->toArray();
    }

    private function applyItemStatus(RequestItem $item, RequestLab $labRequest, string $newStatus): void
    {
        $oldStatus = $item->status ?? 'pending';
        $qty = (int) $item->total_request;

        if ($oldStatus === $newStatus) {
            $item->update(['status' => $newStatus]);
            return;
        }

        if ($oldStatus === 'approved') {
            $asset = Asset::findOrFail($item->asset_id);
            $asset->total_good += $qty;
            $asset->save();

            $assetLab = AssetLab::where('lab_id', $labRequest->lab_id)
                ->where('asset_id', $item->asset_id)
                ->first();

            if ($assetLab) {
                $assetLab->total_good_lab = max(0, (int) $assetLab->total_good_lab - $qty);
                $assetLab->save();
            }
        }

        if ($newStatus === 'approved') {
            $asset = Asset::findOrFail($item->asset_id);

            if ($asset->total_good < $qty) {
                throw new \Exception("Stok {$asset->asset_name} tidak mencukupi (tersedia: {$asset->total_good}).");
            }

            $asset->total_good -= $qty;
            $asset->save();

            $assetLab = AssetLab::firstOrCreate(
                ['lab_id' => $labRequest->lab_id, 'asset_id' => $item->asset_id],
                ['total_good_lab' => 0, 'total_damaged_lab' => 0, 'total_loss_lab' => 0]
            );
            $assetLab->total_good_lab += $qty;
            $assetLab->save();
        }

        $item->update(['status' => $newStatus]);
    }
}
