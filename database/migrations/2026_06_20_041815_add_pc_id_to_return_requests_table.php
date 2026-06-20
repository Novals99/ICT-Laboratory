<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hubungkan return request ke unit PC tertentu (bukan cuma ke 1 jenis asset).
 *
 * pc_id terisi → request ini berarti "bongkar/retur PC unit ini ke gudang"
 *                (PC dihapus + semua komponennya dikembalikan saat di-approve).
 * pc_id null   → return request biasa (retur asset/komponen, perilaku lama).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->foreignId('pc_id')
                  ->nullable()
                  ->after('lab_id')
                  ->constrained('pcs')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pc_id');
        });
    }
};