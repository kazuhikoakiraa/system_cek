<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks to allow renaming
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Rename tables
            DB::statement('RENAME TABLE mesins TO daftar_pengecekan');
            DB::statement('RENAME TABLE komponen_mesins TO komponen_daftar_pengecekan');
            DB::statement('RENAME TABLE detail_pengecekan_mesins TO detail_pengecekan_daftar');
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks to allow renaming
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Reverse table renames
            DB::statement('RENAME TABLE detail_pengecekan_daftar TO detail_pengecekan_mesins');
            DB::statement('RENAME TABLE komponen_daftar_pengecekan TO komponen_mesins');
            DB::statement('RENAME TABLE daftar_pengecekan TO mesins');
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};
