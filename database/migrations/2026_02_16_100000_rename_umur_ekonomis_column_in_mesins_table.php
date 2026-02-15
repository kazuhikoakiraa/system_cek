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
            // Rename kolom dari umur_ekonomis_tahun ke umur_ekonomis_bulan
            $table->renameColumn('umur_ekonomis_tahun', 'umur_ekonomis_bulan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesins', function (Blueprint $table) {
            // Kembalikan nama kolom
            $table->renameColumn('umur_ekonomis_bulan', 'umur_ekonomis_tahun');
        });
    }
};
