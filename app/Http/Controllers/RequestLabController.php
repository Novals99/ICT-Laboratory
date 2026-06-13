<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\Laboratory;
use App\Models\RequestItem;
use App\Models\RequestLab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RequestLabController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $isSPV = str_contains($role, 'spv inventory') || str_contains($role, 'spv');

        $query = RequestLab::with(['user', 'lab', 'items.asset'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('request_id', 'like', "%{$search}%")
                       ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(request('date_from'), fn ($q, $d) => $q->whereDate('request_date', '>=', $d))
            ->when(request('date_to'), fn ($q, $d) => $q->whereDate('request_date', '<=', $d));

        $requests = $query->latest()->paginate(11)->withQueryString();

        $assets = Asset::all()->groupBy('asset_category');
        $assetsByCategory = [
            'electronic'     => $assets->get('electronic', collect())->map(fn ($a) => ['id' => $a->id, 'name' => $a->asset_name])->values(),
            'non-electronic' => $assets->get('non-electronic', collect())->map(fn ($a) => ['id' => $a->id, 'name' => $a->asset_name])->values(),
            'component-pc'   => $assets->get('component-pc', collect())->map(fn ($a) => ['id' => $a->id, 'name' => $a->asset_name])->values(),
        ];

        $laboratories = Laboratory::orderBy('lab_name')->get(['id', 'lab_name']);

        return view('pages.dashboard.requestlab.index', compact(
            'requests', 'assetsByCategory', 'laboratories', 'isSPV', 'user', 'role'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'items'  => 'required|array|min:1',
            'items.*.asset_id' => 'required|exists:assets,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.category' => 'required|in:electronic,non-electronic,component-pc',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $nextId    = (RequestLab::max('id') ?? 0) + 1;
            $requestId = 'REQ-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            $labRequest = RequestLab::create([
                'user_id'        => auth()->id(),
                'lab_id'         => $validated['lab_id'],
                'request_id'     => $requestId,
                'request_date'   => now(),
                'request_status' => 'pending',
                'notes'          => $request->input('notes'),
            ]);

            foreach ($validated['items'] as $item) {
                $asset = Asset::find($item['asset_id']);
                RequestItem::create([
                    'request_lab_id' => $labRequest->id,
                    'asset_id'       => $item['asset_id'],
                    'asset_name'     => $asset?->asset_name,
                    'category'       => $item['category'],
                    'quantity'       => $item['total_request'],
                    'approved_qty'   => 0,
                    'rejected_qty'   => 0,
                    'status'         => 'pending',
                ]);
            }
        });

        return redirect()->route('requestlab.index')->with('success', 'Request berhasil dibuat.');
    }

    public function detail($id)
    {
        $labRequest = RequestLab::with(['user', 'lab', 'items.asset'])->findOrFail($id);

        $items = $labRequest->items->map(function ($item) {
            return [
                'id'           => $item->id,
                'asset_name'   => $item->asset_name ?? $item->asset?->asset_name ?? '-',
                'category'     => $item->category,
                'quantity'     => $item->quantity,
                'approved_qty' => $item->approved_qty,
                'rejected_qty' => $item->rejected_qty,
                'status'       => $item->status,
            ];
        });

        return response()->json([
            'id'             => $labRequest->id,
            'request_id'     => $labRequest->request_id,
            'user_name'      => $labRequest->user?->name ?? '-',
            'lab_name'       => $labRequest->lab?->lab_name ?? '-',
            'total_request'  => $labRequest->items->sum('quantity'),
            'request_date'   => $labRequest->request_date?->format('d-m-Y'),
            'status'         => $labRequest->request_status,
            'notes'          => $labRequest->notes,
            'items'          => $items,
            'electronic'     => $items->where('category', 'electronic')->values(),
            'non_electronic' => $items->where('category', 'non-electronic')->values(),
            'pc_component'   => $items->where('category', 'component-pc')->values(),
        ]);
    }

    public function updateItemStatus(Request $request, $id, $itemId)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $labRequest = RequestLab::findOrFail($id);
        $item = RequestItem::where('request_lab_id', $id)->where('id', $itemId)->firstOrFail();

        if ($item->status !== 'pending') {
            return back()->with('error', 'Item sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($item, $validated, $labRequest) {
            if ($validated['status'] === 'approved') {
                $asset = Asset::find($item->asset_id);

                if (!$asset || $asset->total_good < $item->quantity) {
                    throw new \Exception('Stok ' . ($asset?->asset_name ?? 'asset') . ' tidak mencukupi.');
                }

                $asset->decrement('total_good', $item->quantity);
                $asset->decrement('total_asset', $item->quantity);

                $assetLab = AssetLab::firstOrCreate(
                    ['lab_id' => $labRequest->lab_id, 'asset_id' => $item->asset_id],
                    ['total_asset_lab' => 0, 'total_good_lab' => 0, 'total_damaged_lab' => 0, 'total_loss_lab' => 0]
                );
                $assetLab->increment('total_good_lab', $item->quantity);
                $assetLab->increment('total_asset_lab', $item->quantity);

                $item->update([
                    'status'       => 'approved',
                    'approved_qty' => $item->quantity,
                ]);
            } else {
                $item->update([
                    'status'       => 'rejected',
                    'rejected_qty' => $item->quantity,
                ]);
            }

            $this->recalculateStatus($labRequest);
        });

        return redirect()->route('requestlab.index')->with('success', 'Item berhasil ' . $validated['status']);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
        ]);

        $labRequest = RequestLab::with('items')->findOrFail($id);
        $action = strtolower($validated['status']);

        if ($action === 'approved') {
            foreach ($labRequest->items->where('status', 'pending') as $item) {
                $asset = Asset::find($item->asset_id);
                if (!$asset || $asset->total_good < $item->quantity) {
                    return back()->with('error', 'Stok ' . ($asset?->asset_name ?? 'asset') . ' tidak mencukupi untuk approve all.');
                }
            }
        }

        DB::transaction(function () use ($labRequest, $action) {
            foreach ($labRequest->items->where('status', 'pending') as $item) {
                if ($action === 'approved') {
                    $asset = Asset::find($item->asset_id);

                    $asset->decrement('total_good', $item->quantity);
                    $asset->decrement('total_asset', $item->quantity);

                    $assetLab = AssetLab::firstOrCreate(
                        ['lab_id' => $labRequest->lab_id, 'asset_id' => $item->asset_id],
                        ['total_asset_lab' => 0, 'total_good_lab' => 0, 'total_damaged_lab' => 0, 'total_loss_lab' => 0]
                    );
                    $assetLab->increment('total_good_lab', $item->quantity);
                    $assetLab->increment('total_asset_lab', $item->quantity);

                    $item->update(['status' => 'approved', 'approved_qty' => $item->quantity]);
                } else {
                    $item->update(['status' => 'rejected', 'rejected_qty' => $item->quantity]);
                }
            }

            $this->recalculateStatus($labRequest);
        });

        return redirect()->route('requestlab.index')->with('success', 'Semua item berhasil ' . $validated['status']);
    }

    private function recalculateStatus(RequestLab $labRequest)
    {
        $items    = $labRequest->items()->get();
        $total    = $items->count();
        $approved = $items->where('status', 'approved')->count();
        $rejected = $items->where('status', 'rejected')->count();
        $pending  = $items->where('status', 'pending')->count();

        if ($pending == $total) {
            $status = 'pending';
        } elseif ($approved == $total) {
            $status = 'approved';
        } elseif ($rejected == $total) {
            $status = 'rejected';
        } elseif ($pending == 0) {
            $status = 'done';
        } else {
            $status = 'partially approved';
        }

        $labRequest->update(['request_status' => $status]);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $labRequest = RequestLab::findOrFail($id);
            $labRequest->items()->delete();
            $labRequest->delete();
        });

        return redirect()->route('requestlab.index')->with('success', 'Request dihapus.');
    }

    public function export(Request $request)
    {
        $format   = $request->get('format', 'pdf');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $query = RequestLab::with(['user', 'lab', 'items.asset']);

        if ($dateFrom) $query->whereDate('request_date', '>=', $dateFrom);
        if ($dateTo)   $query->whereDate('request_date', '<=', $dateTo);

        $requests = $query->latest()->get();

        if ($format === 'csv') {
            $filename = 'Request-Lab-' . now()->format('YmdHis') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($requests) {
                $file = fopen('php://output', 'w');
                fputcsv($file, [
                    'Request ID', 'Requester Name', 'Laboratory', 'Request Date',
                    'Asset Name', 'Category', 'Qty Requested', 'Qty Approved', 'Qty Rejected',
                    'Request Status', 'Item Status'
                ]);

                foreach ($requests as $req) {
                    foreach ($req->items as $item) {
                        fputcsv($file, [
                            $req->request_id,
                            $req->user?->name ?? '-',
                            $req->lab?->lab_name ?? '-',
                            $req->request_date?->format('d-m-Y'),
                            $item->asset_name ?? $item->asset?->asset_name ?? '-',
                            $item->category,
                            $item->quantity,
                            $item->approved_qty,
                            $item->rejected_qty,
                            $req->request_status,
                            $item->status,
                        ]);
                    }
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $pdf = Pdf::loadView('pdf.requests-export', compact('requests'));
        return $pdf->download('Request-Lab-' . now()->format('YmdHis') . '.pdf');
    }
}
