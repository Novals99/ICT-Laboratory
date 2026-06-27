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
        DB::statement("ALTER TABLE transfer_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'completed', 'partial') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE return_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'completed', 'partial') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE transfer_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE return_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");
    }
};
