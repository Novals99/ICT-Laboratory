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
        Schema::create('pcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('laboratories')->cascadeOnDelete();
            $table->enum('type_pc', ['dosen', 'mahasiswa']);
            $table->enum('status_pc', ['active', 'inactive']);
            $table->date('pc_entry')->nullable(true);
            $table->string('processor')->nullable();
            $table->string('ram')->nullable();
            $table->string('ssd')->nullable();
            $table->string('motherboard')->nullable();
            $table->string('vga')->nullable();
            $table->string('cpu_fan')->nullable();
            $table->string('powersupply')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pcs');
    }
};
