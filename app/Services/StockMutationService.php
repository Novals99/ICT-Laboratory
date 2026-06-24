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
            // Handle PC return if pc_id is present
            if ($returnRequest->pc_id) {
                $pc = $returnRequest->pc;
                if (!$pc) {
                    throw new \Exception("PC not found for this return request.");
                }

                // Return all components of the PC to lab stock first
                $components = array_filter([
                    $pc->processor, $pc->ram, $pc->ssd, $pc->motherboard,
                    $pc->vga, $pc->cpu_fan, $pc->powersupply
                ]);
                foreach ($components as $name) {
                    $al = AssetLab::where('lab_id', $returnRequest->lab_id)
                        ->whereHas('asset', function ($q) use ($name) {
                            $q->where('asset_category', 'component-pc')
                              ->whereRaw('LOWER(asset_name) = ?', [strtolower($name)]);
                        })
                        ->lockForUpdate()
                        ->first();
                    if ($al) {
                        $al->increment('total_good_lab');
                        $al->update([
                            'total_asset_lab' => $al->total_good_lab + $al->total_damaged_lab + $al->total_loss_lab
                        ]);
                    }
                }

                // Now, return those components from lab stock to warehouse
                foreach ($components as $name) {
                    $asset = Asset::where('asset_category', 'component-pc')
                        ->whereRaw('LOWER(asset_name) = ?', [strtolower($name)])
                        ->lockForUpdate()
                        ->first();
                    $al = AssetLab::where('lab_id', $returnRequest->lab_id)
                        ->whereHas('asset', function ($q) use ($name) {
                            $q->where('asset_category', 'component-pc')
                              ->whereRaw('LOWER(asset_name) = ?', [strtolower($name)]);
                        })
                        ->lockForUpdate()
                        ->first();

                    if ($asset && $al && $al->total_good_lab > 0) {
                        $snap = [
                            'before_from_lab'     => $al->total_asset_lab,
                            'before_total_asset'  => $asset->total_asset,
                            'before_total_good'   => $asset->total_good,
                            'before_total_damaged'=> $asset->total_damaged,
                            'before_total_loss'   => $asset->total_loss,
                        ];

                        // Decrement lab stock
                        $al->decrement('total_asset_lab', 1);
                        $al->decrement('total_good_lab', 1);

                        // Increment warehouse stock
                        $asset->increment('total_asset', 1);
                        $asset->increment('total_good', 1);

                        $al->refresh();
                        $asset->refresh();

                        $this->writeAssetLog(
                            assetId:      $asset->id,
                            type:         'return',
                            quantity:     1,
                            fromLabId:    $returnRequest->lab_id,
                            toLabId:      null,
                            snapBefore:   $snap,
                            asset:        $asset,
                            assetLab:     $al,
                            source:       "return_request:{$returnRequest->request_code}",
                            notes:        "Retur komponen dari PC",
                        );
                    }
                }

                // Return PC asset to warehouse (if pc has asset_id)
                if ($pc->asset_id) {
                    $pcAsset = Asset::where('id', $pc->asset_id)->lockForUpdate()->first();
                    if ($pcAsset) {
                        $pcAsset->increment('total_asset', 1);
                        $pcAsset->increment('total_good', 1);
                        $pcAsset->refresh();
                    }
                }

                // Delete the PC from lab
                $pc->delete();

                // Update lab capacity
                $returnRequest->laboratory->update(['capacity' => $returnRequest->laboratory->pcs()->count()]);
            }

            // Handle regular items (assets)
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

            $hasDebt = false;
            foreach ($returnRequest->items as $item) {
                $qtyApproved = $item->quantity_approved ?? $item->quantity_requested;
                if ($qtyApproved < $item->quantity_requested) {
                    $hasDebt = true;
                }
                $item->update([
                    'status' => 'approved',
                    'quantity_approved' => $qtyApproved
                ]);
            }

            // Update status request → COMPLETED
            $status = $hasDebt ? ReturnRequest::STATUS_PARTIAL : ReturnRequest::STATUS_COMPLETED;
            $returnRequest->update([
                'status'      => $status,
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

            $hasDebt = false;
            foreach ($transferRequest->items as $item) {
                $qtyApproved = $item->quantity_approved ?? $item->quantity_requested;
                if ($qtyApproved < $item->quantity_requested) {
                    $hasDebt = true;
                }
                $item->update([
                    'status' => 'approved',
                    'quantity_approved' => $qtyApproved
                ]);
            }

            // Update status request
            $status = $hasDebt ? TransferRequest::STATUS_PARTIAL : TransferRequest::STATUS_COMPLETED;
            $transferRequest->update([
                'status'      => $status,
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

    public function processTransferRequestItem(\App\Models\TransferRequestItem $item, string $newStatus, int $customQtyApproved = null): void
    {
        if ($item->status !== 'pending') {
            return;
        }

        if ($newStatus === 'rejected') {
            $item->update([
                'status' => 'rejected',
                'quantity_approved' => 0
            ]);
            return;
        }

        if ($newStatus === 'approved') {
            $transferRequest = $item->transferRequest;
            $qtyApproved = $customQtyApproved ?? $item->quantity_requested;

            DB::transaction(function () use ($item, $transferRequest, $qtyApproved) {
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

                $snapFrom = $fromLabStock->total_asset_lab;
                $fromLabStock->decrement('total_asset_lab', $qtyApproved);
                $fromLabStock->decrement('total_good_lab', $qtyApproved);

                $toLabStock = AssetLab::where('lab_id', $transferRequest->to_lab_id)
                    ->where('asset_id', $item->asset_id)
                    ->lockForUpdate()
                    ->first();

                $snapTo = $toLabStock?->total_asset_lab ?? 0;

                if ($toLabStock) {
                    $toLabStock->increment('total_asset_lab', $qtyApproved);
                    $toLabStock->increment('total_good_lab', $qtyApproved);
                } else {
                    AssetLab::create([
                        'lab_id'            => $transferRequest->to_lab_id,
                        'asset_id'          => $item->asset_id,
                        'total_asset_lab'   => $qtyApproved,
                        'total_good_lab'    => $qtyApproved,
                        'total_damaged_lab' => 0,
                        'total_loss_lab'    => 0,
                    ]);
                }

                if ($item->serial_number_id) {
                    $serial = \App\Models\AssetSerialNumber::find($item->serial_number_id);
                    if ($serial) {
                        if ($serial->pc_id) {
                            $pc = \App\Models\Pc::find($serial->pc_id);
                            if ($pc) {
                                if ($pc->pc_serial_id === $serial->id) {
                                    $pc->pc_serial_id = null;
                                    $pc->asset_id = null;
                                }
                                foreach (array_keys(\App\Models\Pc::COMPONENT_SLOTS) as $slot) {
                                    if ($pc->{$slot . '_serial_id'} === $serial->id) {
                                        $pc->{$slot} = null;
                                        $pc->{$slot . '_serial_id'} = null;
                                    }
                                }
                                $pc->save();
                            }
                        }

                        $serial->update([
                            'lab_id' => $transferRequest->to_lab_id,
                            'status' => 'available',
                            'pc_id' => null,
                            'slot' => null
                        ]);
                    }
                }

                $asset = Asset::find($item->asset_id);
                AssetLog::create([
                    'asset_id'              => $item->asset_id,
                    'user_id'               => Auth::id(),
                    'type'                  => 'transfer',
                    'quantity'              => $qtyApproved,
                    'from_lab_id'           => $transferRequest->from_lab_id,
                    'to_lab_id'             => $transferRequest->to_lab_id,
                    'before_total_asset'    => $asset->total_asset,
                    'after_total_asset'     => $asset->total_asset,
                    'before_total_good'     => $asset->total_good,
                    'after_total_good'      => $asset->total_good,
                    'before_total_damaged'  => $asset->total_damaged,
                    'after_total_damaged'   => $asset->total_damaged,
                    'before_total_loss'     => $asset->total_loss,
                    'after_total_loss'      => $asset->total_loss,
                    'before_from_lab_stock' => $snapFrom,
                    'after_from_lab_stock'  => $snapFrom - $qtyApproved,
                    'before_to_lab_stock'   => $snapTo,
                    'after_to_lab_stock'    => $snapTo + $qtyApproved,
                    'source'                => "transfer_request:{$transferRequest->request_code}",
                    'notes'                 => $item->notes,
                ]);

                $item->update([
                    'status' => 'approved',
                    'quantity_approved' => $qtyApproved
                ]);
            });
        }
    }

    public function processReturnRequestItem(\App\Models\ReturnRequestItem $item, string $newStatus, int $customQtyApproved = null): void
    {
        if ($item->status !== 'pending') {
            return;
        }

        if ($newStatus === 'rejected') {
            $item->update([
                'status' => 'rejected',
                'quantity_approved' => 0
            ]);
            return;
        }

        if ($newStatus === 'approved') {
            $returnRequest = $item->returnRequest;
            $qtyApproved = $customQtyApproved ?? $item->quantity_requested;

            DB::transaction(function () use ($item, $returnRequest, $qtyApproved) {
                $assetLab = AssetLab::where('lab_id', $returnRequest->lab_id)
                    ->where('asset_id', $item->asset_id)
                    ->lockForUpdate()
                    ->first();

                $asset = Asset::where('id', $item->asset_id)
                    ->lockForUpdate()
                    ->first();

                if (!$assetLab || !$asset) {
                    throw new \Exception("Data stok tidak ditemukan untuk {$item->asset->asset_name}.");
                }

                $conditionField = match ($item->condition) {
                    ReturnRequestItem::CONDITION_GOOD    => 'total_good_lab',
                    ReturnRequestItem::CONDITION_DAMAGED => 'total_damaged_lab',
                    ReturnRequestItem::CONDITION_LOST    => 'total_loss_lab',
                    default                              => 'total_good_lab',
                };

                if ($assetLab->$conditionField < $qtyApproved) {
                    throw new \Exception(
                        "Stok {$asset->asset_name} ({$item->condition}) di lab tidak mencukupi. " .
                        "Tersedia: {$assetLab->$conditionField}, Disetujui: {$qtyApproved}."
                    );
                }

                $snap = [
                    'before_from_lab'     => $assetLab->total_asset_lab,
                    'before_total_asset'  => $asset->total_asset,
                    'before_total_good'   => $asset->total_good,
                    'before_total_damaged'=> $asset->total_damaged,
                    'before_total_loss'   => $asset->total_loss,
                ];

                $assetLab->decrement('total_asset_lab', $qtyApproved);
                $assetLab->decrement($conditionField, $qtyApproved);

                if ($item->condition === ReturnRequestItem::CONDITION_GOOD) {
                    $asset->increment('total_asset', $qtyApproved);
                    $asset->increment('total_good', $qtyApproved);
                } elseif ($item->condition === ReturnRequestItem::CONDITION_DAMAGED) {
                    $asset->increment('total_asset', $qtyApproved);
                    $asset->increment('total_damaged', $qtyApproved);
                } elseif ($item->condition === ReturnRequestItem::CONDITION_LOST) {
                    $asset->increment('total_loss', $qtyApproved);
                }

                $assetLab->refresh();
                $asset->refresh();

                if ($item->serial_number_id) {
                    $serial = \App\Models\AssetSerialNumber::find($item->serial_number_id);
                    if ($serial) {
                        if ($serial->pc_id) {
                            $pc = \App\Models\Pc::find($serial->pc_id);
                            if ($pc) {
                                if ($pc->pc_serial_id === $serial->id) {
                                    $pc->pc_serial_id = null;
                                    $pc->asset_id = null;
                                }
                                foreach (array_keys(\App\Models\Pc::COMPONENT_SLOTS) as $slot) {
                                    if ($pc->{$slot . '_serial_id'} === $serial->id) {
                                        $pc->{$slot} = null;
                                        $pc->{$slot . '_serial_id'} = null;
                                    }
                                }
                                $pc->save();
                            }
                        }

                        $serial->update([
                            'lab_id' => null,
                            'status' => 'available',
                            'pc_id' => null,
                            'slot' => null,
                            'condition' => $item->condition
                        ]);
                    }
                }

                $this->writeAssetLog(
                    assetId:      $item->asset_id,
                    type:         'return',
                    quantity:     $qtyApproved,
                    fromLabId:    $returnRequest->lab_id,
                    toLabId:      null,
                    snapBefore:   $snap,
                    asset:        $asset,
                    assetLab:     $assetLab,
                    source:       "return_request:{$returnRequest->request_code}",
                    notes:        $item->reason,
                );

                $item->update([
                    'status' => 'approved',
                    'quantity_approved' => $qtyApproved
                ]);
            });
        }
    }
}
