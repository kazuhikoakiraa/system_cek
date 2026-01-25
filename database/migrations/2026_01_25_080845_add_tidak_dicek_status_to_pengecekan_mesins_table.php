<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum status untuk menambahkan 'tidak_dicek'
        DB::statement("ALTER TABLE pengecekan_mesins MODIFY COLUMN status ENUM('selesai', 'dalam_proses', 'tidak_dicek') NOT NULL DEFAULT 'dalam_proses'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum ke semula
        DB::statement("ALTER TABLE pengecekan_mesins MODIFY COLUMN status ENUM('selesai', 'dalam_proses') NOT NULL DEFAULT 'dalam_proses'");
    }
};
