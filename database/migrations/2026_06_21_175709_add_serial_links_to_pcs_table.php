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
 *
 * CATATAN: migration ini IDEMPOTENT — tiap kolom dicek dulu sebelum dibuat,
 * jadi aman dijalankan ulang walau sebagian kolom sudah terlanjur ada
 * (mis. 'ram2' dari percobaan migrate sebelumnya).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah kolom string ram2 dulu (kalau belum ada), commit terpisah
        //    agar bisa dipakai sebagai anchor ->after('ram2') di langkah berikutnya.
        if (! Schema::hasColumn('pcs', 'ram2')) {
            Schema::table('pcs', function (Blueprint $table) {
                $table->string('ram2')->nullable()->after('ram');
            });
        }

        // 2) Tambah kolom *_serial_id (FK) hanya yang belum ada.
        Schema::table('pcs', function (Blueprint $table) {
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
                if (Schema::hasColumn('pcs', $column)) {
                    continue;
                }

                $definition = $table->foreignId($column)->nullable();

                if (Schema::hasColumn('pcs', $after)) {
                    $definition->after($after);
                }

                $definition->constrained('asset_serial_numbers')->nullOnDelete();
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
                if (Schema::hasColumn('pcs', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            if (Schema::hasColumn('pcs', 'ram2')) {
                $table->dropColumn('ram2');
            }
        });
    }
};
