<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
   public function assets()
{
    return $this->belongsToMany(
        Asset::class,
        'asset_labs',
        'lab_id',
        'asset_id'
    )->withPivot([
        'total_asset_lab'
    ]);
}

    public function labs() {
        return $this->belongsToMany(Laboratory::class, 'asset_labs');
    }

    public function assetlogs()
    {
        return $this->hasMany(AssetLog::class);
    }
}
