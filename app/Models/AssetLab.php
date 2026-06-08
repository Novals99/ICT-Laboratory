<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function asset() {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
