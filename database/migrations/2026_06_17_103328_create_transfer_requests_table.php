<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: transfer_requests
 *
 * Header dari request mutasi barang antar laboratorium (Lab A → Lab B).
 *
 * Perbedaan utama dari return_requests:
 *   - Ada dua lab: from_lab_id (asal) dan to_lab_id (tujuan)
 *   - Tidak ada field condition (barang diasumsikan dalam kondisi baik)
 *   - Stok tidak pernah masuk gudang — langsung dari satu lab ke lab lain
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();

            // Format kode: TRF-YYYYMMDD-0001
            $table->string('request_code', 30)->unique();

            // Lab asal (yang mengirim barang)
            // Pengurus yang membuat request HARUS pengurus dari lab ini
            $table->foreignId('from_lab_id')
                  ->constrained('laboratories')
                  ->onDelete('restrict');

            // Lab tujuan (yang menerima barang)
            $table->foreignId('to_lab_id')
                  ->constrained('laboratories')
                  ->onDelete('restrict');

            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->onDelete('restrict');

            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])
                  ->default('pending');

            // Catatan dari Pengurus Lab (alasan transfer, konteks kebutuhan)
            $table->text('notes')->nullable();

            // Alasan jika SPV menolak transfer
            $table->text('rejection_reason')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_requests');
    }
};
