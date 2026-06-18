<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveReturnRequestRequest;
use App\Http\Requests\StoreReturnRequestRequest;
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
            $userLabIds = Auth::user()->labs()->pluck('id');
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

        return view('return-requests.index', compact('returnRequests', 'labs'));
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    public function create()
    {
        // Ambil lab yang di-assign ke user via relation `labs()`
        $userLabs = \App\Models\Laboratory::whereIn('id',
            Auth::user()->labs()->pluck('id')
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
            $userLabIds = Auth::user()->labs()->pluck('id');
            if (!$userLabIds->contains($labId)) {
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }
        }

        $assets = AssetLab::with('asset:id,asset_name,asset_category')
            ->where('lab_id', $labId)
            ->where('total_asset_lab', '>', 0) // hanya yang ada stoknya
            ->get()
            ->map(fn($item) => [
                'asset_id' => $item->asset_id,
                'name'     => $item->asset->asset_name,    // assets.asset_name (bukan name!)
                'category' => $item->asset->asset_category,
                'stock'    => $item->total_asset_lab,       // kolom yang benar
            ]);

        return response()->json($assets);
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(StoreReturnRequestRequest $request)
    {
        $validated = $request->validated();

        // Security: pastikan lab_id adalah lab milik user ini
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('id');
            if (!$userLabIds->contains($validated['lab_id'])) {
                abort(403, 'Anda tidak memiliki akses ke laboratorium ini.');
            }
        }

        try {
            DB::transaction(function () use ($validated) {
                // Validasi stok tiap item SEBELUM buat request
                foreach ($validated['items'] as $item) {
                    $this->mutationService->validateLabStock(
                        labId:        $validated['lab_id'],
                        assetId:      $item['asset_id'],
                        requestedQty: $item['quantity'],
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
            $userLabIds = Auth::user()->labs()->pluck('id');
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
}
