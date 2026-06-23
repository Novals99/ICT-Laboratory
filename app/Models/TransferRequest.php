<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Header dari request mutasi barang antar lab (Lab A → Lab B).
 */
class TransferRequest extends Model
{
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PARTIAL   = 'partial';

    protected $fillable = [
        'request_code',
        'from_lab_id',
        'to_lab_id',
        'requested_by',
        'approved_by',
        'status',
        'notes',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function fromLab(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class, 'from_lab_id');
    }

    public function toLab(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class, 'to_lab_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferRequestItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /** [label, tailwind_classes] untuk badge status */
    public function getStatusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING   => ['Pending',   'bg-yellow-100 text-yellow-700'],
            self::STATUS_PARTIAL   => ['Partial',   'bg-blue-100 text-blue-700'],
            self::STATUS_APPROVED  => ['Approved',  'bg-indigo-100 text-indigo-700'],
            self::STATUS_COMPLETED => ['Completed', 'bg-green-100 text-green-700'],
            self::STATUS_REJECTED  => ['Rejected',  'bg-red-100 text-red-700'],
            default                => ['Unknown',   'bg-gray-100 text-gray-700'],
        };
    }

    /** Generate kode: TRF-YYYYMMDD-0001 */
    public static function generateCode(): string
    {
        $date     = now()->format('Ymd');
        $lastCode = static::whereDate('created_at', today())
            ->orderByDesc('id')
            ->value('request_code');
        $seq = $lastCode ? (intval(substr($lastCode, -4)) + 1) : 1;
        return 'TRF-' . $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
