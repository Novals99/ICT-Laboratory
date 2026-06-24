<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE asset_logs
            MODIFY COLUMN type ENUM(
                'stock_in', 'stock_out', 'transfer', 'adjustment',
                'damaged', 'lost', 'repaired', 'return', 'distribution'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE asset_logs
            MODIFY COLUMN type ENUM(
                'stock_in', 'stock_out', 'transfer', 'adjustment',
                'damaged', 'lost', 'repaired', 'return'
            ) NOT NULL
        ");
    }
};
