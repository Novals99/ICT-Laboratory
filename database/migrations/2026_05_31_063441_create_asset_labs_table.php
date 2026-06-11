<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('laboratories')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->integer('total_good_lab')->default(0);
            $table->integer('total_damaged_lab')->default(0);
            $table->integer('total_loss_lab')->default(0);
            $table->integer('total_asset_lab')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_labs');
    }
};
