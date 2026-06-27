<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->integer('qty_approved')->default(0)->after('total_request');
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn('qty_approved');
        });
    }
};
