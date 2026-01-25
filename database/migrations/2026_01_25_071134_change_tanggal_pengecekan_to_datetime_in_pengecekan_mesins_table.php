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
        // First drop foreign key that uses the index
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->dropForeign(['mesin_id']);
        });

        // Now drop the unique index
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->dropUnique(['mesin_id', 'tanggal_pengecekan']);
        });

        // Change column type from date to datetime
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->dateTime('tanggal_pengecekan')->change();
        });

        // Re-add foreign key
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->foreign('mesin_id')->references('id')->on('mesins')->onDelete('cascade');
        });

        // Re-add unique constraint with date only (using raw index on date part)
        \Illuminate\Support\Facades\DB::statement('CREATE UNIQUE INDEX pengecekan_mesins_mesin_id_tanggal_pengecekan_unique ON pengecekan_mesins (mesin_id, (DATE(tanggal_pengecekan)))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First drop foreign key that uses the index
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->dropForeign(['mesin_id']);
        });

        // Drop the functional unique index
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE pengecekan_mesins DROP INDEX pengecekan_mesins_mesin_id_tanggal_pengecekan_unique');

        // Change column back to date
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->date('tanggal_pengecekan')->change();
        });

        // Re-add foreign key
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->foreign('mesin_id')->references('id')->on('mesins')->onDelete('cascade');
        });

        // Re-add regular unique constraint
        Schema::table('pengecekan_mesins', function (Blueprint $table) {
            $table->unique(['mesin_id', 'tanggal_pengecekan']);
        });
    }
};
