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
        Schema::table('m_components', function (Blueprint $table) {
            $table->dateTime('tanggal_pengadaan')->nullable()->after('part_number');
            $table->string('manufacturer')->nullable()->after('nama_komponen');
            $table->text('catatan')->nullable()->after('status_komponen');
            $table->integer('stok_minimal')->default(1)->after('status_komponen');
            $table->integer('jumlah_terpasang')->default(1)->after('stok_minimal');
            $table->string('lokasi_pemasangan')->nullable()->after('jumlah_terpasang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_components', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_pengadaan',
                'manufacturer',
                'catatan',
                'stok_minimal',
                'jumlah_terpasang',
                'lokasi_pemasangan',
            ]);
        });
    }
};
