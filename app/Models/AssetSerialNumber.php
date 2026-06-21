<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetSerialNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'serial_number',
        'condition',
        'status',
        'lab_id',
        'pc_id',
        'slot',
        'notes',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
    }

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Pc::class);
    }

    /** Scope: hanya unit yang masih bebas dipakai (belum terpasang di PC). */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /** Scope: hanya unit yang berada di lab tertentu. */
    public function scopeInLab($query, int $labId)
    {
        return $query->where('lab_id', $labId);
    }
}