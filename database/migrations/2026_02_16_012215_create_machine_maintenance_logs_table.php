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
        Schema::create('machine_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_maintenance_request_id')->constrained('machine_maintenance_requests')->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('restrict');
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->longText('catatan_teknisi')->nullable();
            $table->decimal('biaya_service', 15, 2)->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'completed'])->default('in_progress');
            $table->timestamps();
            $table->index('machine_maintenance_request_id');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_maintenance_logs');
    }
};
