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
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            // Drop foreign key constraint yang lama
            $table->dropForeign(['user_id']);
        });

        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            // Ubah kolom user_id menjadi nullable
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            // Tambah foreign key baru dengan nullOnDelete
            // Ketika user dihapus, user_id akan di-set menjadi NULL
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['user_id']);
        });

        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            // Ubah kolom user_id kembali menjadi NOT NULL
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            // Tambah foreign key dengan cascadeOnDelete
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }
};
