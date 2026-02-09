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
        // Drop foreign key constraint yang lama
        Schema::table('mesins', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Ubah kolom user_id menjadi nullable
        Schema::table('mesins', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        // Tambah foreign key baru dengan nullOnDelete
        // Ketika user dihapus, user_id di mesin akan di-set menjadi NULL (tidak ada operator)
        Schema::table('mesins', function (Blueprint $table) {
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
        // Drop foreign key constraint
        Schema::table('mesins', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Ubah kolom user_id kembali menjadi NOT NULL
        Schema::table('mesins', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        // Tambah foreign key dengan cascadeOnDelete
        Schema::table('mesins', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }
};
