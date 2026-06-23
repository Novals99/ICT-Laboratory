<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pcs', function (Blueprint $table) {
            if (!Schema::hasColumn('pcs', 'pc_serial_id')) {
                $table->foreignId('pc_serial_id')
                      ->nullable()
                      ->after('asset_id')
                      ->constrained('asset_serial_numbers')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pcs', function (Blueprint $table) {
            if (Schema::hasColumn('pcs', 'pc_serial_id')) {
                $table->dropConstrainedForeignId('pc_serial_id');
            }
        });
    }
};
