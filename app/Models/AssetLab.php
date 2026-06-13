<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_id',
        'asset_id',
        'total_asset_lab',
        'total_good_lab',
        'total_damaged_lab',
        'total_loss_lab',
    ];

    // AUTO-CALCULATE total_asset_lab sebelum save
    protected static function booted(): void
    {
        static::saving(function (AssetLab $assetLab) {
            $assetLab->total_asset_lab = 
                (int) ($assetLab->total_good_lab ?? 0) 
                + (int) ($assetLab->total_damaged_lab ?? 0) 
                + (int) ($assetLab->total_loss_lab ?? 0);
        });
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}