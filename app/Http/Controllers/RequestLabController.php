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
            'request_id' => 'REQ-' . str_pad($labRequest->id, 3, '0', STR_PAD_LEFT),
            'user_name' => $labRequest->user->name ?? '-',
            'total_request' => $labRequest->request_items->sum('total_request'),
            'electronic' => $this->itemsForCategory($labRequest, 'electronic'),
            'non_electronic' => $this->itemsForCategory($labRequest, 'non-electronic'),
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
            'items.*.*.asset_id' => ['nullable', 'exists:assets,id'],
            'items.*.*.total_request' => ['nullable', 'integer', 'min:1'],
        ]);

        $items = collect($validated['items'])
            ->flatMap(fn ($categoryItems) => $categoryItems ?? [])
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

    public function updateStatus(Request $request, $id)
    {
        abort_unless(auth()->user()->role === 'spv inventory', 403);

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
        abort_unless(auth()->user()->role === 'spv inventory', 403);

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
        abort_unless(auth()->user()->role === 'spv inventory', 403);

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
            ->whereHas('asset', fn($q) => $q->where('asset_category', $category))
            ->get()
            ->map(fn($item) => [
                'item_id' => $item->id,
                'asset_name' => $item->asset->asset_name ?? '-',
                'quantity' => $item->total_request,
                'status' => $item->status ?? 'pending',
            ])
            ->toArray();
    }

    private function resolveRequestStatus($items): string
    {
        $allApproved = $items->every(fn($item) => $item->status === 'approved');
        $allRejected = $items->every(fn($item) => $item->status === 'rejected');

        return match (true) {
            $allApproved => 'approved',
            $allRejected => 'rejected',
            default => 'partial',
        };
    }
}
