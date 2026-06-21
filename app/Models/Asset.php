<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_name',
        'sku',
        'asset_category',
        'component_type',
        'total_asset',
        'total_good',
        'total_damaged',
        'total_loss',
    ];

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            if (empty($asset->sku)) {
                $asset->sku = self::generateSku($asset->asset_category, $asset->component_type);
            }
        });

        static::saving(function (Asset $asset) {
            $asset->total_asset =
                (int) ($asset->total_good ?? 0)
                + (int) ($asset->total_damaged ?? 0)
                + (int) ($asset->total_loss ?? 0);
        });
    }

    /** Generate SKU unik: prefix per kategori/komponen + nomor urut. */
    public static function generateSku(?string $category, ?string $componentType = null): string
    {
        $prefix = match ($category) {
            'electronic'     => 'ELC',
            'non-electronic' => 'NEL',
            'pc'             => 'PC',
            'component-pc'   => match ($componentType) {
                'processor'   => 'CPU',
                'ram'         => 'RAM',
                'ssd'         => 'SSD',
                'motherboard' => 'MBO',
                'vga'         => 'VGA',
                'cpu_fan'     => 'FAN',
                'powersupply' => 'PSU',
                default       => 'CMP',
            },
            default => 'AST',
        };

        $last = self::where('sku', 'like', $prefix . '-%')->orderByDesc('id')->value('sku');
        $seq  = $last ? ((int) substr($last, strrpos($last, '-') + 1) + 1) : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function labs()
    {
        return $this->belongsToMany(Laboratory::class, 'asset_labs', 'asset_id', 'lab_id')
            ->withPivot(['total_asset_lab', 'total_good_lab', 'total_damaged_lab', 'total_loss_lab']);
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class);
    }

    /* ─────────────────────────────────────────────
       SERIAL NUMBER
       ───────────────────────────────────────────── */
    public function serialNumbers(): HasMany
    {
        return $this->hasMany(AssetSerialNumber::class);
    }

    /** S/N yang masih bebas dipakai (belum terpasang di PC). */
    public function availableSerials(): HasMany
    {
        return $this->serialNumbers()->where('status', 'available');
    }

    /**
     * Apakah kategori ini memang memakai serial number?
     * Hanya electronic & component-pc (& pc) yang punya unit fisik ber-S/N.
     */
    public function usesSerial(): bool
    {
        return in_array($this->asset_category, ['electronic', 'component-pc', 'pc'], true);
    }
}