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
            if (Schema::hasColumn('mesins', 'lokasi_instalasi')) {
                $table->dropColumn('lokasi_instalasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mesins', function (Blueprint $table) {
            if (!Schema::hasColumn('mesins', 'lokasi_instalasi')) {
                $table->string('lokasi_instalasi')->nullable()->after('jenis_mesin');
            }
        });
    }
};
