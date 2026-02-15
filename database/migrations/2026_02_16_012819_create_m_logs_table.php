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
        Schema::create('m_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('m_request_id')->constrained('m_requests', 'id')->onDelete('cascade')->name('log_request_fk');
            $table->foreignId('teknisi_id')->constrained('users', 'id')->onDelete('cascade')->name('log_teknisi_fk');
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->text('catatan_teknisi')->nullable();
            $table->string('foto_sebelum')->nullable();
            $table->string('foto_sesudah')->nullable();
            $table->decimal('biaya_service', 12, 2)->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'completed'])->default('in_progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_logs');
    }
};
