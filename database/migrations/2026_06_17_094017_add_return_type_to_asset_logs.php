<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION: Tambah tipe 'return' ke enum asset_logs.type
 *
 * Tabel asset_logs sudah ada di project ini dan dipakai untuk
 * mencatat semua pergerakan aset. Untuk fitur Retur ke Gudang,
 * kita perlu menambahkan nilai 'return' ke enum type-nya.
 *
 * Tipe yang sudah ada:
 *   stock_in, stock_out, transfer, adjustment, damaged, lost, repaired
 *
 * Ditambahkan:
 *   return  ← pengembalian barang dari lab ke gudang
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM di MySQL untuk tambah nilai 'return'
        // (di MySQL, ALTER TABLE MODIFY adalah cara untuk update ENUM)
        DB::statement("
            ALTER TABLE asset_logs
            MODIFY COLUMN type ENUM(
                'stock_in',
                'stock_out',
                'transfer',
                'adjustment',
                'damaged',
                'lost',
                'repaired',
                'return'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // Rollback: hapus 'return' dari enum
        // PERHATIAN: Pastikan tidak ada data dengan type='return' sebelum rollback
        DB::statement("
            ALTER TABLE asset_logs
            MODIFY COLUMN type ENUM(
                'stock_in',
                'stock_out',
                'transfer',
                'adjustment',
                'damaged',
                'lost',
                'repaired'
            ) NOT NULL
        ");
    }
};
