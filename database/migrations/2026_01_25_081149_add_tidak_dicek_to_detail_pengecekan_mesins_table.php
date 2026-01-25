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
        // Ubah enum status_sesuai untuk menambahkan 'tidak_dicek'
        DB::statement("ALTER TABLE detail_pengecekan_mesins MODIFY COLUMN status_sesuai ENUM('sesuai', 'tidak_sesuai', 'tidak_dicek') NOT NULL DEFAULT 'tidak_dicek'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum ke semula
        DB::statement("ALTER TABLE detail_pengecekan_mesins MODIFY COLUMN status_sesuai ENUM('sesuai', 'tidak_sesuai') NOT NULL");
    }
};
