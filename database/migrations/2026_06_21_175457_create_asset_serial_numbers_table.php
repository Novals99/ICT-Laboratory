<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BACKBONE: Serial number per-unit untuk tiap Asset.
 *
 * Konsep:
 * - 1 baris `assets` = 1 jenis barang (mis. "VGEN 256GB", total_good = 8).
 * - 1 baris `asset_serial_numbers` = 1 unit fisik dari jenis itu (8 baris S/N).
 *
 * Dipakai oleh:
 *  - Modal Create Stock / Edit Asset (input + edit S/N tiap unit).
 *  - Modal Add PC / Edit PC + Create Laboratory step-2 (pilih komponen by S/N,
 *    difilter per component_type, 1 S/N tidak bisa dipakai 2 PC).
 *  - Modal Asset Information di halaman Laboratory (lihat / edit S/N per lab).
 *
 * SEMUA NULLABLE & ADDITIVE → aman untuk DB yang sudah berisi data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_serial_numbers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')
                  ->constrained('assets')
                  ->cascadeOnDelete();

            // Nomor seri unik tiap unit fisik.
            $table->string('serial_number', 100);

            // Kondisi unit (mengikuti good / damaged / loss di assets).
            $table->enum('condition', ['good', 'damaged', 'loss'])->default('good');

            // available = masih di gudang/lab & bebas dipakai.
            // in_use    = sudah terpasang di sebuah PC (tidak boleh dipilih lagi).
            $table->enum('status', ['available', 'in_use'])->default('available');

            // Lab tempat unit ini berada (saat sudah didistribusi ke lab). Nullable.
            $table->foreignId('lab_id')
                  ->nullable()
                  ->constrained('laboratories')
                  ->nullOnDelete();

            // PC tempat komponen ini terpasang + slot komponennya
            // (processor, ram, ram2, ssd, motherboard, vga, cpu_fan, powersupply).
            $table->foreignId('pc_id')
                  ->nullable()
                  ->constrained('pcs')
                  ->nullOnDelete();
            $table->string('slot', 30)->nullable();

            $table->string('notes')->nullable();

            $table->timestamps();

            // S/N unik per asset (boleh sama persis lintas asset berbeda).
            $table->unique(['asset_id', 'serial_number']);
            $table->index(['lab_id', 'status']);
            $table->index('pc_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_serial_numbers');
    }
};