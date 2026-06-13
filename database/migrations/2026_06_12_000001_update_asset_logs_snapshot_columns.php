<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_logs', 'before_total_asset')) {
                $table->integer('before_total_asset')->default(0)->after('quantity');
                $table->integer('after_total_asset')->default(0)->after('before_total_asset');
                $table->integer('before_total_good')->default(0)->after('after_total_asset');
                $table->integer('after_total_good')->default(0)->after('before_total_good');
                $table->integer('before_total_damaged')->default(0)->after('after_total_good');
                $table->integer('after_total_damaged')->default(0)->after('before_total_damaged');
                $table->integer('before_total_loss')->default(0)->after('after_total_damaged');
                $table->integer('after_total_loss')->default(0)->after('before_total_loss');
                $table->integer('before_from_lab_stock')->nullable()->after('to_lab_id');
                $table->integer('after_from_lab_stock')->nullable()->after('before_from_lab_stock');
                $table->integer('before_to_lab_stock')->nullable()->after('after_from_lab_stock');
                $table->integer('after_to_lab_stock')->nullable()->after('before_to_lab_stock');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE asset_logs MODIFY type ENUM('stock_in', 'stock_out', 'transfer', 'adjustment', 'damaged', 'lost', 'repaired') NOT NULL");

            if (Schema::hasColumn('asset_logs', 'qty_before')) {
                DB::statement('ALTER TABLE asset_logs MODIFY qty_before INT NULL');
            }

            if (Schema::hasColumn('asset_logs', 'qty_after')) {
                DB::statement('ALTER TABLE asset_logs MODIFY qty_after INT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_logs', function (Blueprint $table) {
            foreach ([
                'before_total_asset',
                'after_total_asset',
                'before_total_good',
                'after_total_good',
                'before_total_damaged',
                'after_total_damaged',
                'before_total_loss',
                'after_total_loss',
                'before_from_lab_stock',
                'after_from_lab_stock',
                'before_to_lab_stock',
                'after_to_lab_stock',
            ] as $column) {
                if (Schema::hasColumn('asset_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
