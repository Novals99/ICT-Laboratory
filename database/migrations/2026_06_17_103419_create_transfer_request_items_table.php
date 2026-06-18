<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION: transfer_request_items
 *
 * Detail item barang dalam satu transfer request antar lab.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transfer_request_id')
                  ->constrained('transfer_requests')
                  ->onDelete('cascade');

            $table->foreignId('asset_id')
                  ->constrained('assets')
                  ->onDelete('restrict');

            // Qty yang diminta oleh pengurus
            $table->unsignedInteger('quantity_requested');

            // Qty yang disetujui SPV (bisa berbeda — partial approval)
            $table->unsignedInteger('quantity_approved')->nullable();

            // Catatan khusus per item (misal: "untuk dipakai praktikum jaringan")
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_request_items');
    }
};
