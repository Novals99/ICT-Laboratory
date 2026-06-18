<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: ReturnRequest
 *
 * Header request retur barang dari Lab ke Gudang.
 *
 * STATUS FLOW:
 *   pending → approved → completed
 *   pending → rejected
 */
class ReturnRequest extends Model
{
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'request_code',
        'lab_id',
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

    // ── Relasi ────────────────────────────────────────────────────────────────

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class, 'lab_id');
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
        return $this->hasMany(ReturnRequestItem::class);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === self::STATUS_PENDING; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }
    public function isRejected(): bool  { return $this->status === self::STATUS_REJECTED; }

    /**
     * Mengembalikan [label, tailwind_classes] untuk badge status.
     * Menggunakan Tailwind bukan Bootstrap seperti v1.
     */
    public function getStatusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING   => ['Menunggu',  'bg-yellow-100 text-yellow-700'],
            self::STATUS_APPROVED  => ['Disetujui', 'bg-blue-100 text-blue-700'],
            self::STATUS_COMPLETED => ['Selesai',   'bg-green-100 text-green-700'],
            self::STATUS_REJECTED  => ['Ditolak',   'bg-red-100 text-red-700'],
            default                => ['Unknown',   'bg-gray-100 text-gray-700'],
        };
    }

    /**
     * Auto-generate kode request.
     * Format: RET-YYYYMMDD-0001
     */
    public static function generateCode(): string
    {
        $date     = now()->format('Ymd');
        $lastCode = static::whereDate('created_at', today())
            ->orderByDesc('id')
            ->value('request_code');
        $seq = $lastCode ? (intval(substr($lastCode, -4)) + 1) : 1;
        return 'RET-' . $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
