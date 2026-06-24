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
        'ram2',
        'ssd',
        'hdd',
        'motherboard',
        'vga',
        'cpu_fan',
        'powersupply',
        // serial links
        'pc_serial_id',
        'processor_serial_id',
        'ram_serial_id',
        'ram2_serial_id',
        'ssd_serial_id',
        'hdd_serial_id',
        'motherboard_serial_id',
        'vga_serial_id',
        'cpu_fan_serial_id',
        'powersupply_serial_id',
    ];

    /** Daftar slot komponen + label-nya (dipakai modal Add/Edit PC & Create Lab). */
    public const COMPONENT_SLOTS = [
        'processor'   => 'Processor',
        'ram'         => 'RAM',
        'ram2'        => 'RAM 2',
        'ssd'         => 'SSD',
        'hdd'         => 'HDD',
        'motherboard' => 'Motherboard',
        'vga'         => 'VGA',
        'cpu_fan'     => 'CPU Fan',
        'powersupply' => 'Power Supply',
    ];

    /** Peta slot -> component_type asset (ram2 juga mencari asset bertipe 'ram'). */
    public const SLOT_COMPONENT_TYPE = [
        'processor'   => 'processor',
        'ram'         => 'ram',
        'ram2'        => 'ram',
        'ssd'         => 'ssd',
        'hdd'         => 'hdd',
        'motherboard' => 'motherboard',
        'vga'         => 'vga',
        'cpu_fan'     => 'cpu_fan',
        'powersupply' => 'powersupply',
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

     /* ─────────────────────────────────────────────
        SERIAL NUMBER tiap slot
        ───────────────────────────────────────────── */
    public function pcSerial(): BelongsTo           { return $this->belongsTo(AssetSerialNumber::class, 'pc_serial_id'); }
    public function processorSerial(): BelongsTo   { return $this->belongsTo(AssetSerialNumber::class, 'processor_serial_id'); }
    public function ramSerial(): BelongsTo          { return $this->belongsTo(AssetSerialNumber::class, 'ram_serial_id'); }
    public function ram2Serial(): BelongsTo         { return $this->belongsTo(AssetSerialNumber::class, 'ram2_serial_id'); }
    public function ssdSerial(): BelongsTo          { return $this->belongsTo(AssetSerialNumber::class, 'ssd_serial_id'); }
    public function hddSerial(): BelongsTo          { return $this->belongsTo(AssetSerialNumber::class, 'hdd_serial_id'); }
    public function motherboardSerial(): BelongsTo  { return $this->belongsTo(AssetSerialNumber::class, 'motherboard_serial_id'); }
    public function vgaSerial(): BelongsTo          { return $this->belongsTo(AssetSerialNumber::class, 'vga_serial_id'); }
    public function cpuFanSerial(): BelongsTo       { return $this->belongsTo(AssetSerialNumber::class, 'cpu_fan_serial_id'); }
    public function powersupplySerial(): BelongsTo  { return $this->belongsTo(AssetSerialNumber::class, 'powersupply_serial_id'); }

    /** Semua serial id yang sedang dipakai PC ini (untuk release saat update/hapus). */
    public function usedSerialIds(): array
    {
        return array_filter([
            $this->pc_serial_id,
            $this->processor_serial_id,
            $this->ram_serial_id,
            $this->ram2_serial_id,
            $this->ssd_serial_id,
            $this->hdd_serial_id,
            $this->motherboard_serial_id,
            $this->vga_serial_id,
            $this->cpu_fan_serial_id,
            $this->powersupply_serial_id,
        ]);
    }
}