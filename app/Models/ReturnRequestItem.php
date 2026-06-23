<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Detail satu item dalam return request.
 * Kondisi barang menentukan ke mana ia dialokasikan setelah diapprove.
 */
class ReturnRequestItem extends Model
{
    const CONDITION_GOOD    = 'good';
    const CONDITION_DAMAGED = 'damaged';
    const CONDITION_LOST    = 'lost';

    protected $fillable = [
        'return_request_id',
        'asset_id',
        'serial_number_id',
        'status',
        'quantity_requested',
        'quantity_approved',
        'condition',
        'reason',
    ];

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(AssetSerialNumber::class, 'serial_number_id');
    }

    public function isGoodCondition(): bool
    {
        return $this->condition === self::CONDITION_GOOD;
    }

    /** Label kondisi dalam bahasa Indonesia */
    public function getConditionLabel(): string
    {
        return match ($this->condition) {
            self::CONDITION_GOOD    => 'Baik',
            self::CONDITION_DAMAGED => 'Rusak',
            self::CONDITION_LOST    => 'Hilang',
            default                 => '-',
        };
    }

    /** Tailwind badge color class untuk kondisi */
    public function getConditionColor(): string
    {
        return match ($this->condition) {
            self::CONDITION_GOOD    => 'bg-green-100 text-green-700',
            self::CONDITION_DAMAGED => 'bg-yellow-100 text-yellow-700',
            self::CONDITION_LOST    => 'bg-red-100 text-red-700',
            default                 => 'bg-gray-100 text-gray-700',
        };
    }
}
