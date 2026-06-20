<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah dukungan SKU + sub-kategori komponen ke tabel assets.
 *
 * - sku            : kode unik tiap jenis aset (RAM-0001, SSD-0001, PC-0001, dst).
 *                    nullable dulu supaya data lama tidak error; di-backfill setelahnya.
 * - component_type : dipakai HANYA saat asset_category = 'component-pc'.
 *                    Memecah "component-pc" jadi: processor, ram, ssd, motherboard,
 *                    vga, cpu_fan, powersupply — sehingga dropdown PC bisa difilter
 *                    per jenis (RAM ya RAM, SSD ya SSD).
 * - enum category  : ditambah 'pc' agar 1 unit PC bisa dicatat sebagai aset.
 *
 * SEMUA ADDITIVE & NULLABLE → aman dijalankan di DB yang sudah ada, tidak menghapus data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('sku', 40)->nullable()->unique()->after('asset_name');
            $table->string('component_type', 30)->nullable()->after('asset_category');
        });

        // MySQL: perluas enum untuk menambahkan kategori 'pc'
        DB::statement("
            ALTER TABLE assets
            MODIFY COLUMN asset_category
            ENUM('electronic', 'non-electronic', 'component-pc', 'pc') NOT NULL
        ");
    }

    public function down(): void
    {
        // Kembalikan enum ke kondisi semula (pastikan tidak ada row kategori 'pc' tersisa)
        DB::statement("
            ALTER TABLE assets
            MODIFY COLUMN asset_category
            ENUM('electronic', 'non-electronic', 'component-pc') NOT NULL
        ");

        Schema::table('assets', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn(['sku', 'component_type']);
        });
    }
};
