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
        Schema::create('machine_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->string('nama_komponen');
            $table->string('part_number')->nullable();
            $table->longText('spesifikasi_teknis')->nullable();
            $table->integer('jadwal_ganti_bulan')->nullable()->comment('Interval dalam bulan untuk ganti komponen');
            $table->date('tanggal_perawatan_terakhir')->nullable();
            $table->date('estimasi_tanggal_ganti_berikutnya')->nullable();
            $table->string('nama_supplier')->nullable();
            $table->decimal('harga_komponen', 15, 2)->nullable();
            $table->enum('status_component', ['normal', 'perlu_ganti', 'rusak'])->default('normal');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_components');
    }
};
