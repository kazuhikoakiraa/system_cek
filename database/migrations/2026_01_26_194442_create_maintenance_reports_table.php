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
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_pengecekan_mesin_id')->constrained('detail_pengecekan_mesins')->cascadeOnDelete();
            $table->foreignId('mesin_id')->constrained('mesins')->cascadeOnDelete();
            $table->foreignId('komponen_mesin_id')->constrained('komponen_mesins')->cascadeOnDelete();
            $table->text('issue_description');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->text('catatan_teknisi')->nullable();
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
    }
};
