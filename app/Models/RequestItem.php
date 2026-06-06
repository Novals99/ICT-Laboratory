<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_lab_id',
        'asset_id',
        'total_request',
        'status',
        'reason'
    ];

    public function request_lab() {
        return $this->belongsTo(RequestLab::class, 'request_lab_id');
    }
    
    public function asset() {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
