<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_serial_numbers', function (Blueprint $table) {
            $table->foreignId('request_item_id')
                  ->nullable()
                  ->constrained('request_items')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('asset_serial_numbers', function (Blueprint $table) {
            $table->dropForeign(['request_item_id']);
            $table->dropColumn(['request_item_id']);
        });
    }
};
