<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: return_request_items
 *
 * Detail item barang yang diretur dalam satu return request.
 * Satu request bisa punya banyak item (one-to-many dari return_requests).
 *
 * Contoh:
 *   Request RET-0001 berisi:
 *     - 3 unit Keyboard (kondisi: GOOD, alasan: surplus)
 *     - 1 unit Monitor (kondisi: DAMAGED, alasan: layar retak)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_request_items', function (Blueprint $table) {
            $table->id();

            // Referensi ke header request
            // cascade → jika request dihapus, semua item ikut terhapus
            $table->foreignId('return_request_id')
                  ->constrained('return_requests')
                  ->onDelete('cascade');

            // Barang yang diretur
            $table->foreignId('asset_id')
                  ->constrained('assets')
                  ->onDelete('restrict');

            // Jumlah yang diminta oleh Pengurus Lab untuk diretur
            $table->unsignedInteger('quantity_requested');

            // Jumlah yang disetujui SPV
            // nullable → belum diisi sebelum SPV review
            // bisa lebih kecil dari quantity_requested (partial approval per item)
            $table->unsignedInteger('quantity_approved')->nullable();

            // Kondisi barang yang diretur — menentukan ke mana barang dialokasikan:
            //   good    → masuk kembali ke stok gudang
            //   damaged → dicatat sebagai barang rusak, TIDAK masuk stok gudang
            //   lost    → dicatat sebagai barang hilang, TIDAK masuk stok gudang
            $table->enum('condition', ['good', 'damaged', 'lost'])->default('good');

            // Alasan retur (misal: surplus, tidak terpakai, rusak terbentur)
            $table->text('reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_items');
    }
};
