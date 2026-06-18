<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: return_requests
 *
 * Header/induk dari request retur barang (Lab → Gudang Utama).
 * Satu request bisa berisi banyak item (lihat tabel return_request_items).
 *
 * Alur status:
 *   pending → approved/rejected → completed
 *
 * Yang bisa membuat: Pengurus Lab
 * Yang bisa menyetujui: Superadmin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();

            // Kode unik untuk identifikasi (contoh: RET-20250115-0001)
            // Digunakan di UI dan laporan agar mudah dirujuk
            $table->string('request_code', 30)->unique();

            // Lab yang melakukan retur
            // restrict → tidak bisa hapus lab jika masih ada request aktif
            $table->foreignId('lab_id')
                  ->constrained('laboratories')
                  ->onDelete('restrict');

            // Siapa yang membuat request (Pengurus Lab)
            $table->foreignId('requested_by')
                  ->constrained('users')
                  ->onDelete('restrict');

            // Siapa yang memproses request (Superadmin)
            // nullable karena belum tentu sudah diproses saat record dibuat
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null'); // Jika user SPV dihapus, kolom jadi NULL (bukan error)

            // Status perjalanan request
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])
                  ->default('pending');

            // Catatan opsional dari Pengurus Lab saat submit
            $table->text('notes')->nullable();

            // Alasan penolakan — wajib diisi oleh SPV jika status = rejected
            // Validasi ini ada di ApproveReturnRequestRequest.php
            $table->text('rejection_reason')->nullable();

            // Kapan request ini diproses oleh SPV
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
