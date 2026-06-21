<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hubungkan tiap slot komponen PC ke unit serial number tertentu.
 *
 * Kolom string lama (processor, ram, ssd, ...) TETAP dipertahankan supaya
 * tampilan & kode lama tidak rusak — kolom *_serial_id baru hanya MENAMBAH
 * keterangan "unit fisik mana (S/N berapa)" yang dipasang di slot itu.
 *
 * Tambahan slot RAM kedua (ram2) karena 1 PC bisa pakai 2 keping RAM.
 * ram2 & semua *_serial_id NULLABLE → boleh kosong.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pcs', function (Blueprint $table) {
            // Slot RAM kedua (nullable) — mirror kolom string lama.
            $table->string('ram2')->nullable()->after('ram');

            // Link tiap slot ke unit serial number.
            foreach ([
                'processor_serial_id'   => 'processor',
                'ram_serial_id'         => 'ram',
                'ram2_serial_id'        => 'ram2',
                'ssd_serial_id'         => 'ssd',
                'motherboard_serial_id' => 'motherboard',
                'vga_serial_id'         => 'vga',
                'cpu_fan_serial_id'     => 'cpu_fan',
                'powersupply_serial_id' => 'powersupply',
            ] as $column => $after) {
                $table->foreignId($column)
                      ->nullable()
                      ->after($after)
                      ->constrained('asset_serial_numbers')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pcs', function (Blueprint $table) {
            foreach ([
                'processor_serial_id',
                'ram_serial_id',
                'ram2_serial_id',
                'ssd_serial_id',
                'motherboard_serial_id',
                'vga_serial_id',
                'cpu_fan_serial_id',
                'powersupply_serial_id',
            ] as $column) {
                $table->dropConstrainedForeignId($column);
            }
            $table->dropColumn('ram2');
        });
    }
};