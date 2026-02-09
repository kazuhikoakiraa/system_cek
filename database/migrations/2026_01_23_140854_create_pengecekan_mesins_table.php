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
        Schema::create('pengecekan_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_pengecekan');
            $table->enum('status', ['selesai', 'dalam_proses'])->default('dalam_proses');
            $table->timestamps();

            // Index untuk memastikan satu mesin hanya bisa dicek sekali per hari
            $table->unique(['mesin_id', 'tanggal_pengecekan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengecekan_mesins');
    }
};
