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
        Schema::create('asset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['stock_in', 'stock_out', 'transfer', 'adjustment', 'status_change' ]);
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->integer('quantity');

            $table->foreignId('from_lab_id')->nullable()->constrained('laboratories')->nullOnDelete();
            $table->foreignId('to_lab_id')->nullable()->constrained('laboratories')->nullOnDelete();

            $table->enum('condition_before', ['good', 'damaged', 'loss'])->nullable();
            $table->enum('condition_after', ['good', 'damaged', 'loss'])->nullable();

            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_logs');
    }
};
