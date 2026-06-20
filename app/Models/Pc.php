<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pc extends Model
{
    use HasFactory;
    protected $fillable = [
        'lab_id',
        'asset_id',
        'sku',
        'type_pc',
        'status_pc',
        'pc_entry',
        'processor',
        'ram',
        'ssd',
        'motherboard',
        'vga',
        'cpu_fan',
        'powersupply'
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }
}
