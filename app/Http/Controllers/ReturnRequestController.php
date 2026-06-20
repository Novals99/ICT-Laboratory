<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveReturnRequest;
use App\Http\Requests\StoreReturnRequest;
use App\Models\AssetLab;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use App\Services\StockMutationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnRequestController extends Controller
{
    public function __construct(
        private readonly StockMutationService $mutationService
    ) {}

    // ── Helper role check ─────────────────────────────────────────────────────

    /**
     * Cek apakah user yang login adalah SPV.
     *
     * Sesuaikan dengan implementasi role di project kamu:
     *   - Spatie Permission: return Auth::user()->hasRole('spv');
     *   - Kolom role:        return Auth::user()->role === 'spv';
     */
    private function isSPV(): bool
    {
        // Gunakan kolom `role` sesuai konvensi proyek: 'spv inventory' atau 'staff'
        return Auth::user()?->role === 'spv inventory';
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = ReturnRequest::with(['laboratory', 'requestedBy'])
            ->withCount('items');

        // SPV lihat semua, staff hanya lihat lab mereka
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('laboratories.id');
            $query->whereIn('lab_id', $userLabIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        $returnRequests = $query->latest()->paginate(15)->withQueryString();

        // Lab untuk filter dropdown
        $labs = $this->isSPV()
            ? \App\Models\Laboratory::all()
            : Auth::user()->labs()->orderBy('lab_name')->get();

        // User's labs for modal
        $userLabs = $this->isSPV()
            ? collect()
            : Auth::user()->labs()->orderBy('lab_name')->get();

        return view('return-requests.index', compact('returnRequests', 'labs', 'userLabs'));
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    public function create()
    {
        // Ambil lab yang di-assign ke user via relation `labs()`
        $userLabs = \App\Models\Laboratory::whereIn('id',
            Auth::user()->labs()->pluck('laboratories.id')
        )->get();

        if ($userLabs->isEmpty()) {
            return redirect()
                ->route('return-requests.index')
                ->with('error', 'Anda belum ditugaskan ke laboratorium manapun.');
        }

        return view('return-requests.create', compact('userLabs'));
    }

    // ── GET LAB ASSETS (AJAX) ─────────────────────────────────────────────────

    /**
     * Endpoint AJAX: ambil daftar aset yang ada di lab tertentu.
     * Dipanggil oleh Alpine.js fetch() saat user pilih lab di form.
     *
     * Response: JSON array aset dengan total_asset_lab > 0
     */
    public function getLabAssets(int $labId)
    {
        // Security: cek akses user ke lab ini
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('laboratories.id');
            if (!$userLabIds->contains($labId)) {
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }
        }

        $assets = AssetLab::with('asset:id,asset_name,asset_category')
            ->where('lab_id', $labId)
            ->where('total_asset_lab', '>', 0)
            ->get()
            ->map(fn($item) => [
                'asset_id'      => $item->asset_id,
                'name'          => $item->asset->asset_name,
                'category'      => $item->asset->asset_category,
                'stock'         => $item->total_good_lab,    // default = good (dipakai form Transfer)
                'stock_good'    => $item->total_good_lab,
                'stock_damaged' => $item->total_damaged_lab,
                'stock_loss'    => $item->total_loss_lab,
            ]);

        return response()->json($assets);
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(StoreReturnRequest $request)
    {
        $validated = $request->validated();

        // Security: pastikan lab_id adalah lab milik user ini
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('laboratories.id');
            if (!$userLabIds->contains($validated['lab_id'])) {
                abort(403, 'Anda tidak memiliki akses ke laboratorium ini.');
            }
        }

        try {
            DB::transaction(function () use ($validated) {
                // Validasi stok tiap item SEBELUM buat request, sesuai kondisi barang
                foreach ($validated['items'] as $item) {
                    $field = match ($item['condition']) {
                        'good'    => 'total_good_lab',
                        'damaged' => 'total_damaged_lab',
                        'lost'    => 'total_loss_lab',
                        default   => 'total_good_lab',
                    };

                    $this->mutationService->validateLabStock(
                        labId:        $validated['lab_id'],
                        assetId:      $item['asset_id'],
                        requestedQty: $item['quantity'],
                        field:        $field,
                    );
                }

                // Buat header request
                $returnRequest = ReturnRequest::create([
                    'request_code' => ReturnRequest::generateCode(),
                    'lab_id'       => $validated['lab_id'],
                    'requested_by' => Auth::id(),
                    'status'       => ReturnRequest::STATUS_PENDING,
                    'notes'        => $validated['notes'] ?? null,
                ]);

                // Buat detail item
                foreach ($validated['items'] as $item) {
                    ReturnRequestItem::create([
                        'return_request_id'  => $returnRequest->id,
                        'asset_id'           => $item['asset_id'],
                        'quantity_requested' => $item['quantity'],
                        'condition'          => $item['condition'],
                        'reason'             => $item['reason'] ?? null,
                    ]);
                }
            });

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('return-requests.index')
            ->with('success', 'Return request berhasil diajukan. Menunggu persetujuan SPV.');
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────

    public function show(ReturnRequest $returnRequest)
    {
        // Staff hanya bisa lihat request dari lab mereka
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('laboratories.id');
            if (!$userLabIds->contains($returnRequest->lab_id)) {
                abort(403);
            }
        }

        $returnRequest->load(['laboratory', 'requestedBy', 'approvedBy', 'items.asset']);

        return view('return-requests.show', compact('returnRequest'));
    }

    // ── APPROVE ───────────────────────────────────────────────────────────────

    public function approve(ApproveReturnRequestRequest $request, ReturnRequest $returnRequest)
    {
        abort_unless($this->isSPV(), 403);

        if (!$returnRequest->isPending()) {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        try {
            DB::transaction(function () use ($request, $returnRequest) {
                // Update qty_approved per item dari form SPV
                foreach ($request->validated()['items'] as $itemData) {
                    ReturnRequestItem::where('id', $itemData['id'])
                        ->where('return_request_id', $returnRequest->id)
                        ->update(['quantity_approved' => $itemData['quantity_approved']]);
                }

                // Refresh supaya items punya quantity_approved terbaru
                $returnRequest->load('items.asset', 'laboratory');

                // Eksekusi mutasi stok via service
                $this->mutationService->approveReturnRequest($returnRequest);
            });

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }

        return redirect()
            ->route('return-requests.show', $returnRequest)
            ->with('success', 'Return request disetujui. Stok telah diperbarui.');
    }

    // ── REJECT ────────────────────────────────────────────────────────────────

    public function reject(Request $request, ReturnRequest $returnRequest)
    {
        abort_unless($this->isSPV(), 403);

        if (!$returnRequest->isPending()) {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        // Reject tidak perlu perubahan stok, hanya update status
        $returnRequest->update([
            'status'           => ReturnRequest::STATUS_REJECTED,
            'approved_by'      => Auth::id(),
            'approved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()
            ->route('return-requests.show', $returnRequest)
            ->with('success', 'Return request telah ditolak.');
    }

    /**
     * Create a quick return request for PC or single asset from lab page
     */
    public function storeQuick(Request $request)
    {
        $validated = $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'pc_id' => 'nullable|exists:pcs,id',
            'asset_id' => 'nullable|exists:assets,id',
            'quantity' => 'nullable|integer|min:1',
            'condition' => 'nullable|in:good,damaged,lost',
            'notes' => 'nullable|string|max:500',
        ]);

        // Security: user must be staff assigned to lab
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('laboratories.id');
            if (!$userLabIds->contains($validated['lab_id'])) {
                abort(403, 'Anda tidak memiliki akses ke laboratorium ini.');
            }
        }

        try {
            DB::transaction(function () use ($validated) {
                $returnRequest = ReturnRequest::create([
                    'request_code' => ReturnRequest::generateCode(),
                    'lab_id' => $validated['lab_id'],
                    'pc_id' => $validated['pc_id'] ?? null,
                    'requested_by' => Auth::id(),
                    'status' => ReturnRequest::STATUS_PENDING,
                    'notes' => $validated['notes'] ?? 'Pengajuan retur dari halaman lab',
                ]);

                // If it's an asset return (not PC), create the item
                if (isset($validated['asset_id']) && !isset($validated['pc_id'])) {
                    $condition = $validated['condition'] ?? 'good';
                    $quantity = $validated['quantity'] ?? 1;

                    $this->mutationService->validateLabStock(
                        labId: $validated['lab_id'],
                        assetId: $validated['asset_id'],
                        requestedQty: $quantity,
                        field: $condition === 'good' ? 'total_good_lab' : ($condition === 'damaged' ? 'total_damaged_lab' : 'total_loss_lab')
                    );

                    ReturnRequestItem::create([
                        'return_request_id' => $returnRequest->id,
                        'asset_id' => $validated['asset_id'],
                        'quantity_requested' => $quantity,
                        'condition' => $condition,
                        'reason' => $validated['notes'] ?? null,
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan retur berhasil dikirim, menunggu persetujuan SPV!');
    }

    public function getDetail($id)
    {
        $returnRequest = ReturnRequest::with([
            'laboratory',
            'requestedBy',
            'items.asset'
        ])->findOrFail($id);

        return response()->json([
            'request_code' => $returnRequest->request_code,
            'lab_name' => $returnRequest->laboratory?->lab_name ?? '-',
            'requested_by' => $returnRequest->requestedBy?->name ?? '-',
            'items' => $returnRequest->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'asset_id' => $item->asset_id,
                    'asset_name' => $item->asset?->asset_name ?? '-',
                    'quantity' => $item->quantity_requested,
                    'condition' => ucfirst($item->condition),
                    'quantity_approved' => $item->quantity_approved,
                    'status' => $item->status ?? null
                ];
            })
        ]);
    }

    public function approveViaModal(Request $request, $id)
    {
        abort_unless($this->isSPV(), 403);
        $returnRequest = ReturnRequest::findOrFail($id);
        abort_unless($returnRequest->isPending(), 400, 'Request sudah diproses');

        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:return_request_items,id',
            'items.*.quantity_approved' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $returnRequest) {
                foreach ($validated['items'] as $itemData) {
                    $item = ReturnRequestItem::where('id', $itemData['id'])
                        ->where('return_request_id', $returnRequest->id)
                        ->first();
                    if ($item) {
                        $item->update(['quantity_approved' => $itemData['quantity_approved']]);
                    }
                }
                $this->mutationService->approveReturnRequest($returnRequest);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function rejectViaModal(Request $request, $id)
    {
        abort_unless($this->isSPV(), 403);
        $returnRequest = ReturnRequest::findOrFail($id);
        abort_unless($returnRequest->isPending(), 400, 'Request sudah diproses');
        $validated = $request->validate(['rejection_reason' => 'required|string|min:1']);
        $returnRequest->update([
            'status' => ReturnRequest::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason']
        ]);
        return response()->json(['success' => true]);
    }
}
