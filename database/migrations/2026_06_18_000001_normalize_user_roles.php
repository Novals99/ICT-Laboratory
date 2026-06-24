<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE users SET role = 'staff' WHERE role IN ('admin','pic','assistant')");
            return;
        }

        // Step 1: Expand enum to include 'staff' while keeping old values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('spv inventory','pic','admin','assistant','staff') NOT NULL");

        // Step 2: Map legacy roles to 'staff' and keep 'spv inventory'
        DB::statement("UPDATE users SET role = 'staff' WHERE role IN ('admin','pic','assistant')");

        // Step 3: Modify enum to only allow 'spv inventory' and 'staff'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('spv inventory','staff') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert enum to previous values (best-effort)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('spv inventory','pic','admin','assistant') NOT NULL");
        // Note: we do NOT revert mapped data automatically
    }
};
