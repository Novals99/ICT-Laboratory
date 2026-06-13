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

            $table->enum('type', [
                'stock_in',
                'stock_out',
                'transfer',
                'adjustment',
                'damaged',
                'lost',
                'repaired',
            ]);
            $table->integer('quantity');

            $table->foreignId('from_lab_id')->nullable()->constrained('laboratories')->nullOnDelete();
            $table->foreignId('to_lab_id')->nullable()->constrained('laboratories')->nullOnDelete();

            $table->integer('before_total_asset')->default(0);
            $table->integer('after_total_asset')->default(0);
            $table->integer('before_total_good')->default(0);
            $table->integer('after_total_good')->default(0);
            $table->integer('before_total_damaged')->default(0);
            $table->integer('after_total_damaged')->default(0);
            $table->integer('before_total_loss')->default(0);
            $table->integer('after_total_loss')->default(0);
            $table->integer('before_from_lab_stock')->nullable();
            $table->integer('after_from_lab_stock')->nullable();
            $table->integer('before_to_lab_stock')->nullable();
            $table->integer('after_to_lab_stock')->nullable();

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
