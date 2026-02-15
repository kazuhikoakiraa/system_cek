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
        Schema::create('maint_log_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_maintenance_log_id')
                  ->constrained('machine_maintenance_logs', 'id')
                  ->onDelete('cascade')
                  ->name('maint_log_spare_maint_log_fk');
            $table->foreignId('spare_part_id')
                  ->constrained('spare_parts', 'id')
                  ->onDelete('restrict')
                  ->name('maint_log_spare_part_fk');
            $table->integer('jumlah_digunakan');
            $table->decimal('harga_satuan', 15, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('machine_maintenance_log_id', 'maint_log_id_idx');
            $table->index('spare_part_id', 'spare_part_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maint_log_spare_parts');
    }
};
