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
        Schema::create('komponen_mesins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->constrained('mesins')->onDelete('cascade');
            $table->string('nama_komponen');
            $table->string('standar');
            $table->enum('frekuensi', ['harian', 'mingguan', 'bulanan', 'tahunan']);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen_mesins');
    }
};
