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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mesin')->unique();
            $table->string('nama_mesin');
            $table->string('jenis_mesin')->nullable();
            $table->string('lokasi_instalasi')->nullable();
            $table->date('tanggal_pengadaan')->nullable();
            $table->date('tanggal_warranty_expired')->nullable();
            $table->enum('status', ['aktif', 'non-aktif', 'maintenance'])->default('aktif');
            $table->enum('kondisi_terakhir', ['baik', 'warning', 'kritis'])->default('baik');
            $table->longText('spesifikasi_teknis')->nullable();
            $table->string('pemilik')->nullable();
            $table->string('no_seri')->nullable();
            $table->string('manufacturer')->nullable();
            $table->decimal('harga_pembelian', 15, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index('kode_mesin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
