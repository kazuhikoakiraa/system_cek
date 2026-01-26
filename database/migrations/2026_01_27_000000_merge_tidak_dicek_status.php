<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menggabungkan status 'tidak_dicek' dengan konsep 'tidak ada data'
     * Menghapus status 'tidak_dicek' dari enum dan mengubah semua data yang ada
     */
    public function up(): void
    {
        // 1. Update semua record yang memiliki status 'tidak_dicek' di tabel pengecekan_mesins
        // Hapus record tersebut karena 'tidak dicek' = 'tidak ada data pengecekan'
        DB::table('pengecekan_mesins')
            ->where('status', 'tidak_dicek')
            ->delete();

        // 2. Ubah enum status di tabel pengecekan_mesins untuk menghapus 'tidak_dicek'
        DB::statement("ALTER TABLE pengecekan_mesins MODIFY COLUMN status ENUM('selesai', 'dalam_proses') NOT NULL DEFAULT 'dalam_proses'");

        // 3. Update semua record detail yang memiliki status 'tidak_dicek'
        // Hapus detail tersebut juga karena tidak ada record induknya
        DB::table('detail_pengecekan_mesins')
            ->whereIn('pengecekan_mesin_id', function($query) {
                $query->select('id')
                    ->from('pengecekan_mesins')
                    ->whereNull('id'); // Ini akan menghapus detail yang parent-nya sudah dihapus
            })
            ->orWhere('status_sesuai', 'tidak_dicek')
            ->delete();

        // 4. Ubah enum status_sesuai di tabel detail_pengecekan_mesins untuk menghapus 'tidak_dicek'
        DB::statement("ALTER TABLE detail_pengecekan_mesins MODIFY COLUMN status_sesuai ENUM('sesuai', 'tidak_sesuai') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum dengan 'tidak_dicek'
        DB::statement("ALTER TABLE pengecekan_mesins MODIFY COLUMN status ENUM('selesai', 'dalam_proses', 'tidak_dicek') NOT NULL DEFAULT 'dalam_proses'");
        
        DB::statement("ALTER TABLE detail_pengecekan_mesins MODIFY COLUMN status_sesuai ENUM('sesuai', 'tidak_sesuai', 'tidak_dicek') NOT NULL");
    }
};
