<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'type',
        'qty_before',
        'qty_after',
        'quantity',
        'from_lab_id',
        'to_lab_id',
        'condition_before',
        'condition_after',
        'source',
        'notes'
    ];

    public function asset() {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function fromLab() {
        return $this->belongsTo(Laboratory::class, 'from_lab_id');
    }
    public function toLab() {
        return $this->belongsTo(Laboratory::class, 'to_lab_id');
    }

}
