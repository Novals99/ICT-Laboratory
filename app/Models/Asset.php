<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $fillable = [
        'asset_name',
        'asset_category',
        'total_asset',
        'total_good',
        'total_damaged',
        'total_loss'
    ];

    protected static function booted(): void
    {
        static::saving(function (Asset $asset) {
            $asset->total_asset =
                (int) ($asset->total_good ?? 0)
                + (int) ($asset->total_damaged ?? 0)
                + (int) ($asset->total_loss ?? 0);
        });
    }

    public function labs()
    {
        return $this->belongsToMany(Laboratory::class, 'asset_labs', 'asset_id', 'lab_id')
            ->withPivot(['total_asset_lab', 'total_good_lab', 'total_damaged_lab', 'total_loss_lab']);
    }

    public function logs() {
        return $this->hasMany(AssetLog::class);
    }

}
