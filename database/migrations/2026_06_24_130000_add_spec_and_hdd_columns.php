<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'specification')) {
                $table->string('specification', 255)->nullable()->after('component_type');
            }
        });

        Schema::table('pcs', function (Blueprint $table) {
            if (!Schema::hasColumn('pcs', 'hdd')) {
                $table->string('hdd')->nullable()->after('ssd');
            }
            if (!Schema::hasColumn('pcs', 'hdd_serial_id')) {
                $table->foreignId('hdd_serial_id')
                      ->nullable()
                      ->after('ssd_serial_id')
                      ->constrained('asset_serial_numbers')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pcs', function (Blueprint $table) {
            if (Schema::hasColumn('pcs', 'hdd_serial_id')) {
                $table->dropConstrainedForeignId('hdd_serial_id');
            }
            if (Schema::hasColumn('pcs', 'hdd')) {
                $table->dropColumn('hdd');
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'specification')) {
                $table->dropColumn('specification');
            }
        });
    }
};
