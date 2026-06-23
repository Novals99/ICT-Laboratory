<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfer_request_items', function (Blueprint $table) {
            $table->foreignId('serial_number_id')
                  ->nullable()
                  ->constrained('asset_serial_numbers')
                  ->onDelete('set null');
            $table->string('status')->default('pending'); // pending, approved, rejected
        });

        Schema::table('return_request_items', function (Blueprint $table) {
            $table->foreignId('serial_number_id')
                  ->nullable()
                  ->constrained('asset_serial_numbers')
                  ->onDelete('set null');
            $table->string('status')->default('pending'); // pending, approved, rejected
        });
    }

    public function down(): void
    {
        Schema::table('transfer_request_items', function (Blueprint $table) {
            $table->dropForeign(['serial_number_id']);
            $table->dropColumn(['serial_number_id', 'status']);
        });

        Schema::table('return_request_items', function (Blueprint $table) {
            $table->dropForeign(['serial_number_id']);
            $table->dropColumn(['serial_number_id', 'status']);
        });
    }
};
