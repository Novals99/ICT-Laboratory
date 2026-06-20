<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLab;
use App\Models\AssetLog;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use App\Models\TransferRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * StockMutationService — v2
 *
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║  Semua logika pergerakan stok ada di sini.                     ║
 * ║  Controller hanya tangani HTTP, Service tangani bisnis logic.  ║
 * ╚══════════════════════════════════════════════════════════════════╝
 *
 * PERBEDAAN v1 vs v2:
 *   ❌ v1: pakai WarehouseStock (tabel baru)
 *   ✅ v2: pakai Asset model (assets.total_asset = stok gudang)
 *
 *   ❌ v1: tulis ke activity_logs (tabel baru)
 *   ✅ v2: tulis ke asset_logs (tabel yang sudah ada)
 *
 *   ❌ v1: asset_labs.quantity
 *   ✅ v2: asset_labs.total_asset_lab / total_good_lab / dll.
 */
class StockMutationService
{
    // ── VALIDASI STOK ─────────────────────────────────────────────────────────

    /**
     * Cek apakah stok lab mencukupi sebelum request dibuat.
     * Dipanggil di Controller->store() agar user langsung tahu jika kurang.
     *
     * Menggunakan total_asset_lab (total semua kondisi di lab).
     *
     * @throws \Exception jika stok tidak mencukupi
     */
    public function validateLabStock(int $labId, int $assetId, int $requestedQty, string $field = 'total_asset_lab'): void
    {
        $currentQty = AssetLab::where('lab_id', $labId)
            ->where('asset_id', $assetId)
            ->value($field) ?? 0;

        if ($currentQty < $requestedQty) {
            $assetName = Asset::find($assetId)?->asset_name ?? "Aset #{$assetId}";

            throw new \Exception(
                "Stok {$assetName} tidak mencukupi. " .
                "Tersedia: {$currentQty}, Diminta: {$requestedQty}."
            );
        }
    }

    // ── APPROVE RETURN REQUEST (Lab → Gudang) ─────────────────────────────────

    /**
     * Eksekusi persetujuan retur dari Lab ke Gudang.
     *
     * Apa yang terjadi per item:
     * ┌─────────────────┬──────────────────────────────────────────────────┐
     * │ Kondisi         │ Perubahan Stok                                   │
     * ├─────────────────┼──────────────────────────────────────────────────┤
     * │ good (baik)     │ lab ↓ qty | gudang total_asset ↑ | total_good ↑ │
     * │ damaged (rusak) │ lab ↓ qty | gudang total_asset ↑ | total_damaged↑│
     * │ lost (hilang)   │ lab ↓ qty | assets.total_loss ↑  | gudang tetap │
     * └─────────────────┴──────────────────────────────────────────────────┘
     *
     * Semua dalam satu DB transaction. Jika satu item gagal → semua rollback.
     *
     * @throws \Exception jika stok lab tidak mencukupi saat approval
     */
    public function approveReturnRequest(ReturnRequest $returnRequest): void
    {
        DB::transaction(function () use ($returnRequest) {

            foreach ($returnRequest->items as $item) {
                // Qty yang disetujui SPV. Jika tidak diisi, gunakan qty request.
                $qtyApproved = $item->quantity_approved ?? $item->quantity_requested;

                // Skip item yang qty_approved = 0 (SPV tolak item ini)
                if ($qtyApproved <= 0) {
                    continue;
                }

                // ── Lock row untuk cegah race condition ──────────────────────
                // lockForUpdate() = DB-level lock, jika 2 SPV approve bersamaan
                // salah satu akan tunggu sampai yang pertama selesai

                $assetLab = AssetLab::where('lab_id', $returnRequest->lab_id)
                    ->where('asset_id', $item->asset_id)
                    ->lockForUpdate()
                    ->first();

                $asset = Asset::where('id', $item->asset_id)
                    ->lockForUpdate()
                    ->first();

                // Guard: record harus ada
                if (!$assetLab || !$asset) {
                    throw new \Exception(
                        "Data stok tidak ditemukan untuk {$item->asset->asset_name}."
                    );
                }

                $conditionField = match ($item->condition) {
                    ReturnRequestItem::CONDITION_GOOD    => 'total_good_lab',
                    ReturnRequestItem::CONDITION_DAMAGED => 'total_damaged_lab',
                    ReturnRequestItem::CONDITION_LOST    => 'total_loss_lab',
                    default                               => 'total_good_lab',
                };

                if ($assetLab->$conditionField < $qtyApproved) {
                    throw new \Exception(
                        "Stok {$asset->asset_name} ({$item->condition}) di lab sudah berubah. " .
                        "Tersedia: {$assetLab->$conditionField}, " .
                        "Disetujui: {$qtyApproved}. Silakan review ulang."
                    );
                }

                // ── Snapshot SEBELUM perubahan (untuk asset_logs) ────────────
                $snap = [
                    'before_from_lab'     => $assetLab->total_asset_lab,
                    'before_total_asset'  => $asset->total_asset,
                    'before_total_good'   => $asset->total_good,
                    'before_total_damaged'=> $asset->total_damaged,
                    'before_total_loss'   => $asset->total_loss,
                ];

                // ── Kurangi stok lab ─────────────────────────────────────────
                // Asumsikan: barang yang diretur berasal dari total_good_lab
                // (jika item sudah rusak di lab, seharusnya sudah dicatat sebelumnya)
                $assetLab->decrement('total_asset_lab', $qtyApproved);
                $assetLab->decrement($conditionField, $qtyApproved);

                // ── Update stok gudang (assets) sesuai kondisi ───────────────
                if ($item->condition === ReturnRequestItem::CONDITION_GOOD) {
                    // Barang baik → kembali ke stok gudang sebagai "good"
                    $asset->increment('total_asset', $qtyApproved);
                    $asset->increment('total_good', $qtyApproved);

                } elseif ($item->condition === ReturnRequestItem::CONDITION_DAMAGED) {
                    // Barang rusak → masuk gudang sebagai "damaged"
                    $asset->increment('total_asset', $qtyApproved);
                    $asset->increment('total_damaged', $qtyApproved);

                } elseif ($item->condition === ReturnRequestItem::CONDITION_LOST) {
                    // Barang hilang → TIDAK masuk gudang, hanya catat ke total_loss
                    // total_asset tidak berubah (tidak ada barang fisik yang kembali)
                    $asset->increment('total_loss', $qtyApproved);
                }

                // ── Refresh untuk dapat nilai SESUDAH perubahan ──────────────
                $assetLab->refresh();
                $asset->refresh();

                // ── Catat ke asset_logs ───────────────────────────────────────
                $this->writeAssetLog(
                    assetId:      $item->asset_id,
                    type:         'return',
                    quantity:     $qtyApproved,
                    fromLabId:    $returnRequest->lab_id,
                    toLabId:      null, // tidak ke lab lain, kembali ke gudang
                    snapBefore:   $snap,
                    asset:        $asset,
                    assetLab:     $assetLab,
                    source:       "return_request:{$returnRequest->request_code}",
                    notes:        $item->reason,
                );
            }

            // Update status request → COMPLETED
            $returnRequest->update([
                'status'      => ReturnRequest::STATUS_COMPLETED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });
    }

    // ── APPROVE TRANSFER REQUEST (Lab A → Lab B) ──────────────────────────────

    /**
     * Eksekusi persetujuan transfer antar lab.
     *
     * Apa yang terjadi:
     *   Lab Asal: total_asset_lab ↓, total_good_lab ↓
     *   Lab Tujuan: total_asset_lab ↑, total_good_lab ↑
     *   assets (gudang): TIDAK BERUBAH
     *
     * Jika aset belum pernah ada di lab tujuan → buat record baru.
     *
     * @throws \Exception jika stok lab asal tidak mencukupi
     */
    public function approveTransferRequest(TransferRequest $transferRequest): void
    {
        DB::transaction(function () use ($transferRequest) {

            foreach ($transferRequest->items as $item) {
                $qtyApproved = $item->quantity_approved ?? $item->quantity_requested;

                if ($qtyApproved <= 0) {
                    continue;
                }

                // ── Lock & ambil stok lab ASAL ────────────────────────────────
                $fromLabStock = AssetLab::where('lab_id', $transferRequest->from_lab_id)
                    ->where('asset_id', $item->asset_id)
                    ->lockForUpdate()
                    ->first();

                if (!$fromLabStock || $fromLabStock->total_good_lab < $qtyApproved) {
                    $available = $fromLabStock?->total_good_lab ?? 0;
                    throw new \Exception(
                        "Stok {$item->asset->asset_name} di lab asal tidak mencukupi. " .
                        "Tersedia: {$available}, Disetujui: {$qtyApproved}."
                    );
                }

                // Snapshot sebelum
                $snapFrom = $fromLabStock->total_asset_lab;

                // ── Kurangi stok lab ASAL ─────────────────────────────────────
                $fromLabStock->decrement('total_asset_lab', $qtyApproved);
                $fromLabStock->decrement('total_good_lab', $qtyApproved);

                // ── Tambah/buat stok lab TUJUAN ───────────────────────────────
                // firstOrCreate: buat record baru jika aset belum pernah ada di lab tujuan
                $toLabStock = AssetLab::where('lab_id', $transferRequest->to_lab_id)
                    ->where('asset_id', $item->asset_id)
                    ->lockForUpdate()
                    ->first();

                $snapTo = $toLabStock?->total_asset_lab ?? 0;

                if ($toLabStock) {
                    $toLabStock->increment('total_asset_lab', $qtyApproved);
                    $toLabStock->increment('total_good_lab', $qtyApproved);
                } else {
                    // Buat record baru untuk lab tujuan
                    AssetLab::create([
                        'lab_id'          => $transferRequest->to_lab_id,
                        'asset_id'        => $item->asset_id,
                        'total_asset_lab' => $qtyApproved,
                        'total_good_lab'  => $qtyApproved,
                        'total_damaged_lab' => 0,
                        'total_loss_lab'    => 0,
                    ]);
                }

                // Ambil asset untuk snapshot (tidak berubah pada transfer)
                $asset = Asset::find($item->asset_id);

                // ── Catat ke asset_logs ───────────────────────────────────────
                AssetLog::create([
                    'asset_id'              => $item->asset_id,
                    'user_id'               => Auth::id(),
                    'type'                  => 'transfer',
                    'quantity'              => $qtyApproved,
                    'from_lab_id'           => $transferRequest->from_lab_id,
                    'to_lab_id'             => $transferRequest->to_lab_id,

                    // Stok gudang tidak berubah pada transfer antar lab
                    'before_total_asset'    => $asset->total_asset,
                    'after_total_asset'     => $asset->total_asset,
                    'before_total_good'     => $asset->total_good,
                    'after_total_good'      => $asset->total_good,
                    'before_total_damaged'  => $asset->total_damaged,
                    'after_total_damaged'   => $asset->total_damaged,
                    'before_total_loss'     => $asset->total_loss,
                    'after_total_loss'      => $asset->total_loss,

                    // Stok lab asal
                    'before_from_lab_stock' => $snapFrom,
                    'after_from_lab_stock'  => $snapFrom - $qtyApproved,

                    // Stok lab tujuan
                    'before_to_lab_stock'   => $snapTo,
                    'after_to_lab_stock'    => $snapTo + $qtyApproved,

                    'source' => "transfer_request:{$transferRequest->request_code}",
                    'notes'  => $item->notes,
                ]);
            }

            // Update status request
            $transferRequest->update([
                'status'      => TransferRequest::STATUS_COMPLETED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });
    }

    // ── PRIVATE: Tulis ke asset_logs ─────────────────────────────────────────

    /**
     * Tulis satu record ke tabel asset_logs (existing table).
     * Dipanggil setelah perubahan stok dilakukan.
     *
     * @param array $snapBefore Snapshot stok sebelum perubahan
     * @param Asset $asset      Model Asset SETELAH update (untuk after values)
     * @param AssetLab $assetLab Model AssetLab SETELAH update (untuk after values)
     */
    private function writeAssetLog(
        int      $assetId,
        string   $type,
        int      $quantity,
        ?int     $fromLabId,
        ?int     $toLabId,
        array    $snapBefore,
        Asset    $asset,
        AssetLab $assetLab,
        ?string  $source = null,
        ?string  $notes  = null,
    ): void {
        AssetLog::create([
            'asset_id' => $assetId,
            'user_id'  => Auth::id(),
            'type'     => $type,
            'quantity' => $quantity,

            'from_lab_id' => $fromLabId,
            'to_lab_id'   => $toLabId,

            // Stok gudang SEBELUM dan SESUDAH
            'before_total_asset'    => $snapBefore['before_total_asset'],
            'after_total_asset'     => $asset->total_asset,
            'before_total_good'     => $snapBefore['before_total_good'],
            'after_total_good'      => $asset->total_good,
            'before_total_damaged'  => $snapBefore['before_total_damaged'],
            'after_total_damaged'   => $asset->total_damaged,
            'before_total_loss'     => $snapBefore['before_total_loss'],
            'after_total_loss'      => $asset->total_loss,

            // Stok lab asal SEBELUM dan SESUDAH
            'before_from_lab_stock' => $snapBefore['before_from_lab'],
            'after_from_lab_stock'  => $assetLab->total_asset_lab,

            // Tidak ada lab tujuan untuk return
            'before_to_lab_stock'   => null,
            'after_to_lab_stock'    => null,

            'source' => $source,
            'notes'  => $notes,
        ]);
    }
}
