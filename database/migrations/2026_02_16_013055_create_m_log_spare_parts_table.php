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
        Schema::create('m_log_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('m_log_id')->constrained('m_logs', 'id')->onDelete('cascade')->name('log_spare_log_fk');
            $table->foreignId('spare_part_id')->constrained('spare_parts', 'id')->onDelete('cascade')->name('log_spare_part_fk');
            $table->integer('jumlah_digunakan');
            $table->decimal('harga_satuan', 12, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_log_spare_parts');
    }
};
