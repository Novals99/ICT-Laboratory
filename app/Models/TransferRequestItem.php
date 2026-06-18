<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Detail item dalam satu transfer request antar lab. */
class TransferRequestItem extends Model
{
    protected $fillable = [
        'transfer_request_id',
        'asset_id',
        'quantity_requested',
        'quantity_approved',
        'notes',
    ];

    public function transferRequest(): BelongsTo
    {
        return $this->belongsTo(TransferRequest::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
