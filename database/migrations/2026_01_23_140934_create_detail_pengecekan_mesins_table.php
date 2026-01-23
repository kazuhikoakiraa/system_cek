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
        Schema::create('detail_pengecekan_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengecekan_mesin_id')->constrained('pengecekan_mesins')->cascadeOnDelete();
            $table->foreignId('komponen_mesin_id')->constrained('komponen_mesins')->cascadeOnDelete();
            $table->enum('status_sesuai', ['sesuai', 'tidak_sesuai']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pengecekan_mesins');
    }
};
