<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: AssetLab
 *
 * Menyimpan stok aset per laboratorium.
 * Satu record = satu jenis aset di satu lab.
 *
 * KOLOM STOK:
 *   total_asset_lab   = total semua kondisi di lab ini
 *   total_good_lab    = kondisi baik
 *   total_damaged_lab = kondisi rusak
 *   total_loss_lab    = hilang dari lab ini
 *
 * ATURAN: total_asset_lab = total_good_lab + total_damaged_lab + total_loss_lab
 */
class AssetLab extends Model
{
    use HasFactory;


    protected $fillable = [
    'lab_id',
    'asset_id',
    'total_asset_lab',

];

    public function lab() {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    // ── Helper Methods ────────────────────────────────────────────────────────

    /**
     * Cek apakah stok lab mencukupi untuk sejumlah qty.
     * Dipakai sebelum membuat request agar user langsung tahu.
     */
    public function hasEnoughStock(int $qty): bool
    {
        return $this->total_asset_lab >= $qty;
    }

    /**
     * Kurangi stok saat barang keluar dari lab (return/transfer).
     * Asumsi: barang yang keluar berasal dari total_good_lab.
     *
     * @param int $qty jumlah yang keluar
     */
    public function deductStock(int $qty): void
    {
        $this->decrement('total_asset_lab', $qty);
        $this->decrement('total_good_lab', $qty);
    }

    /**
     * Tambah stok saat barang masuk ke lab (transfer masuk / distribusi).
     *
     * @param int $qty jumlah yang masuk
     */
    public function addStock(int $qty): void
    {
        $this->increment('total_asset_lab', $qty);
        $this->increment('total_good_lab', $qty);
    }
}
