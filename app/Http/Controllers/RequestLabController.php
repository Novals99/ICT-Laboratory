<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Laboratory;
use App\Models\RequestItem;
use App\Models\RequestLab;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $electronic = $this->itemsForCategory($labRequest, 'electronic');
        $nonElectronic = $this->itemsForCategory($labRequest, 'non-electronic');

        return response()->json([
            'request_id' => 'REQ-'.str_pad($labRequest->id, 3, '0', STR_PAD_LEFT),
            'user_name' => $labRequest->user->name ?? '-',
            'total_request' => $labRequest->request_items->sum('total_request'),
            'electronic' => $electronic,
            'non_electronic' => $nonElectronic,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $labRequest = RequestLab::with('request_items')->findOrFail($id);

        DB::transaction(function () use ($labRequest, $validated) {
            $labRequest->request_items()->update([
                'status' => $validated['status'],
            ]);

            $labRequest->update([
                'request_status' => $validated['status'],
            ]);
        });

        return response()->json([
            'success' => true,
            'request_status' => $labRequest->fresh()->request_status,
        ]);
    }

    public function updateItemStatus(Request $request, $itemId)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $requestStatus = DB::transaction(function () use ($itemId, $validated) {
            $item = RequestItem::findOrFail($itemId);
            $item->update(['status' => $validated['status']]);

            $labRequest = RequestLab::with('request_items')->findOrFail($item->request_lab_id);
            $requestStatus = $this->resolveRequestStatus($labRequest->request_items);
            $labRequest->update(['request_status' => $requestStatus]);

            return $requestStatus;
        });

        return response()->json([
            'success' => true,
            'request_status' => $requestStatus,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $labRequest = RequestLab::findOrFail($id);
            $labRequest->delete();

            DB::commit();

            return redirect()->route('requestlab.index')
                ->with('success', 'Request berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('requestlab.index')
                ->with('error', 'Request gagal dihapus.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'total_request' => 'required|integer|min:1',
            'request_date'  => 'required|date',
            'status'        => 'nullable|in:Pending,Approved,Partially Approved,Rejected',
        ]);

        RequestLab::create([
            'name'          => $validated['name'],
            'total_request' => $validated['total_request'],
            'request_date'  => $validated['request_date'],
            'status'        => $validated['status'] ?? 'Pending',
        ]);

        return redirect()->route('requestlab.index')
            ->with('success', 'Request berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $labRequest = RequestLab::findOrFail($id);
        return view('pages.dashboard.requestlab.edit', compact('labRequest'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'total_request' => 'required|integer|min:1',
            'request_date'  => 'required|date',
            'status'        => 'nullable|in:Pending,Approved,Rejected',
        ]);

        $labRequest = RequestLab::findOrFail($id);
        $labRequest->update($validated);

        return redirect()->route('requestlab.index')
            ->with('error', 'Edit request lab dilakukan melalui status item di popup detail.');
    }

    public function exportPdf()
    {
        $requests = RequestLab::with(['user', 'request_items'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.lab-requests', [
            'requests' => $requests,
        ]);

        return $pdf->download('Request-Lab.pdf');
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

    private function resolveRequestStatus($items): string
    {
        $allApproved = $items->every(fn ($item) => $item->status === 'approved');
        $allRejected = $items->every(fn ($item) => $item->status === 'rejected');

        return match (true) {
            $allApproved => 'approved',
            $allRejected => 'rejected',
            default => 'partial',
        };
    }
}
