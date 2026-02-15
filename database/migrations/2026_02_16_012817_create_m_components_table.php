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
        Schema::create('m_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesins', 'id')->onDelete('cascade')->name('component_mesin_fk');
            $table->string('nama_komponen');
            $table->string('part_number')->nullable();
            $table->text('spesifikasi_teknis')->nullable();
            $table->integer('jadwal_ganti_bulan')->nullable();
            $table->dateTime('tanggal_perawatan_terakhir')->nullable();
            $table->dateTime('estimasi_tanggal_ganti_berikutnya')->nullable();
            $table->string('nama_supplier')->nullable();
            $table->decimal('harga_komponen', 12, 2)->nullable();
            $table->enum('status_komponen', ['normal', 'perlu_ganti', 'rusak'])->default('normal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_components');
    }
};
