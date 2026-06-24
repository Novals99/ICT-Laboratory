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
        DB::statement("ALTER TABLE request_labs MODIFY request_status ENUM('pending', 'partial', 'done', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE request_labs MODIFY request_status ENUM('pending', 'partial', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
