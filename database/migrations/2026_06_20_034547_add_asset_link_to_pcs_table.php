<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hubungkan tiap PC fisik (tabel pcs) ke aset PC + beri SKU unit sendiri.
 *
 * - asset_id : FK ke assets (kategori 'pc'). Menandakan "PC ini berasal dari
 *              aset/stok PC mana di gudang". nullOnDelete agar hapus aset tidak
 *              menghapus PC (PC tetap tercatat, hanya kehilangan link).
 * - sku      : kode unit PC itu sendiri (mis. PC-LAB04-001). Unik per unit fisik.
 *
 * Tujuan akhir: menambah lab WAJIB menyertakan PC, dan tiap PC tercatat sebagai
 * aset — tidak bisa tambah/kurang sembarangan tanpa unit fisiknya.
 *
 * ADDITIVE & NULLABLE → aman untuk DB yang sudah berisi data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pcs', function (Blueprint $table) {
            $table->foreignId('asset_id')
                  ->nullable()
                  ->after('lab_id')
                  ->constrained('assets')
                  ->nullOnDelete();

            $table->string('sku', 40)->nullable()->unique()->after('asset_id');
        });
    }

    public function down(): void
    {
        Schema::table('pcs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_id');
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};
