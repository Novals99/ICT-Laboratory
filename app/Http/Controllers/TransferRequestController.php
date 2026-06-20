<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveTransferRequestRequest;
use App\Http\Requests\StoreTransferRequestRequest;
use App\Models\AssetLab;
use App\Models\Laboratory;
use App\Models\TransferRequest;
use App\Models\TransferRequestItem;
use App\Services\StockMutationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferRequestController extends Controller
{
    public function __construct(
        private readonly StockMutationService $mutationService
    ) {}

    private function isSPV(): bool
    {
        return Auth::user()?->role === 'spv inventory';
    }

    // ── INDEX ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = TransferRequest::with(['fromLab', 'toLab', 'requestedBy'])
            ->withCount('items');

        // Staff lihat semua transfer yang melibatkan lab mereka (asal ATAU tujuan)
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('id');
            $query->where(function ($q) use ($userLabIds) {
                $q->whereIn('from_lab_id', $userLabIds)
                  ->orWhereIn('to_lab_id', $userLabIds);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transferRequests = $query->latest()->paginate(15)->withQueryString();

        $labs = $this->isSPV()
            ? Laboratory::all()
            : Laboratory::whereIn('id',
                Auth::user()->labs()->pluck('id')
              )->get();

        return view('transfer-requests.index', compact('transferRequests', 'labs'));
    }

    // ── CREATE ────────────────────────────────────────────────────────────────

    public function create()
    {
        $userLabIds = Auth::user()->labs()->pluck('id');
        $userLabs   = Laboratory::whereIn('id', $userLabIds)->get();

        if ($userLabs->isEmpty()) {
            return redirect()
                ->route('transfer-requests.index')
                ->with('error', 'Anda belum ditugaskan ke laboratorium manapun.');
        }

        // Lab tujuan = semua lab KECUALI lab milik user ini
        $targetLabs = Laboratory::whereNotIn('id', $userLabIds)->get();

        return view('transfer-requests.create', compact('userLabs', 'targetLabs'));
    }

    // ── GET LAB ASSETS (AJAX) ─────────────────────────────────────────────────

    public function getLabAssets(int $labId)
    {
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('id');
            if (!$userLabIds->contains($labId)) {
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }
        }

        $assets = AssetLab::with('asset:id,asset_name,asset_category')
            ->where('lab_id', $labId)
            ->where('total_asset_lab', '>', 0)
            ->get()
            ->map(fn($item) => [
                'asset_id' => $item->asset_id,
                'name'     => $item->asset->asset_name,
                'category' => $item->asset->asset_category,
                'stock'    => $item->total_asset_lab,
            ]);

        return response()->json($assets);
    }

    // ── STORE ─────────────────────────────────────────────────────────────────

    public function store(StoreTransferRequestRequest $request)
    {
        $validated = $request->validated();

        // Security: from_lab harus lab milik user
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('id');
            if (!$userLabIds->contains($validated['from_lab_id'])) {
                abort(403, 'Anda hanya bisa transfer dari lab yang ditugaskan ke Anda.');
            }
        }

        if ($validated['from_lab_id'] == $validated['to_lab_id']) {
            return back()->withInput()->with('error', 'Lab asal dan tujuan tidak boleh sama.');
        }

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['items'] as $item) {
                    $this->mutationService->validateLabStock(
                        labId:        $validated['from_lab_id'],
                        assetId:      $item['asset_id'],
                        requestedQty: $item['quantity'],
                        field:        'total_good_lab',
                    );
                }

                $transferRequest = TransferRequest::create([
                    'request_code' => TransferRequest::generateCode(),
                    'from_lab_id'  => $validated['from_lab_id'],
                    'to_lab_id'    => $validated['to_lab_id'],
                    'requested_by' => Auth::id(),
                    'status'       => TransferRequest::STATUS_PENDING,
                    'notes'        => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    TransferRequestItem::create([
                        'transfer_request_id' => $transferRequest->id,
                        'asset_id'            => $item['asset_id'],
                        'quantity_requested'  => $item['quantity'],
                        'notes'               => $item['notes'] ?? null,
                    ]);
                }
            });

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('transfer-requests.index')
            ->with('success', 'Transfer request berhasil diajukan. Menunggu persetujuan SPV.');
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────

    public function show(TransferRequest $transferRequest)
    {
        if (!$this->isSPV()) {
            $userLabIds = Auth::user()->labs()->pluck('id');
            $isInvolved = $userLabIds->contains($transferRequest->from_lab_id)
                       || $userLabIds->contains($transferRequest->to_lab_id);
            if (!$isInvolved) {
                abort(403);
            }
        }

        $transferRequest->load(['fromLab', 'toLab', 'requestedBy', 'approvedBy', 'items.asset']);

        return view('transfer-requests.show', compact('transferRequest'));
    }

    // ── APPROVE ───────────────────────────────────────────────────────────────

    public function approve(ApproveTransferRequestRequest $request, TransferRequest $transferRequest)
    {
        abort_unless($this->isSPV(), 403);

        if (!$transferRequest->isPending()) {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        try {
            DB::transaction(function () use ($request, $transferRequest) {
                foreach ($request->validated()['items'] as $itemData) {
                    TransferRequestItem::where('id', $itemData['id'])
                        ->where('transfer_request_id', $transferRequest->id)
                        ->update(['quantity_approved' => $itemData['quantity_approved']]);
                }

                $transferRequest->load('items.asset', 'fromLab', 'toLab');
                $this->mutationService->approveTransferRequest($transferRequest);
            });

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }

        return redirect()
            ->route('transfer-requests.show', $transferRequest)
            ->with('success', 'Transfer request disetujui. Stok antar lab telah diperbarui.');
    }

    // ── REJECT ────────────────────────────────────────────────────────────────

    public function reject(Request $request, TransferRequest $transferRequest)
    {
        abort_unless($this->isSPV(), 403);

        if (!$transferRequest->isPending()) {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $transferRequest->update([
            'status'           => TransferRequest::STATUS_REJECTED,
            'approved_by'      => Auth::id(),
            'approved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()
            ->route('transfer-requests.show', $transferRequest)
            ->with('success', 'Transfer request telah ditolak.');
    }
}
