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
        Schema::table('mesins', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->after('kode_mesin');
            $table->string('manufacturer')->nullable()->after('nama_mesin');
            $table->string('model_number')->nullable()->after('manufacturer');
            $table->year('tahun_pembuatan')->nullable()->after('model_number');
            $table->string('supplier')->nullable()->after('lokasi_instalasi');
            $table->decimal('harga_pengadaan', 15, 2)->nullable()->after('tanggal_pengadaan');
            $table->string('nomor_invoice')->nullable()->after('harga_pengadaan');
            $table->integer('umur_ekonomis_tahun')->nullable()->after('tanggal_waranty_expired');
            $table->dateTime('estimasi_penggantian')->nullable()->after('umur_ekonomis_tahun');
            $table->text('dokumen_pendukung')->nullable()->after('foto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesins', function (Blueprint $table) {
            $table->dropColumn([
                'serial_number',
                'manufacturer',
                'model_number',
                'tahun_pembuatan',
                'supplier',
                'harga_pengadaan',
                'nomor_invoice',
                'umur_ekonomis_tahun',
                'estimasi_penggantian',
                'dokumen_pendukung',
            ]);
        });
    }
};
