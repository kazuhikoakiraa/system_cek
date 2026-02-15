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
        Schema::create('spare_part_stock_opnames', function (Blueprint $table) {
            $table->id();
            
            // Nomor Stock Opname
            $table->string('nomor_opname')->unique();
            
            // Tanggal & Periode
            $table->date('tanggal_opname');
            $table->string('periode')->comment('Contoh: Januari 2026, Q1 2026, dll');
            
            // PIC
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->comment('User yang melakukan stock opname');
            
            // Status
            $table->enum('status', ['draft', 'completed', 'approved'])->default('draft');
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('approved_at')->nullable();
            
            // Dokumentasi
            $table->text('catatan')->nullable();
            $table->string('dokumen')->nullable()->comment('Upload hasil stock opname (Excel, PDF, Foto)');
            
            $table->timestamps();
            
            // Index
            $table->index(['tanggal_opname', 'status']);
        });

        // Detail Stock Opname per Item
        Schema::create('spare_part_stock_opname_details', function (Blueprint $table) {
            $table->id();
            
            // Reference
            $table->foreignId('stock_opname_id')->constrained('spare_part_stock_opnames')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            
            // Data Stock
            $table->integer('stok_sistem')->comment('Stok di sistem saat opname');
            $table->integer('stok_fisik')->comment('Stok hasil penghitungan fisik');
            $table->integer('selisih')->comment('Stok Fisik - Stok Sistem');
            
            // Keterangan
            $table->text('keterangan')->nullable()->comment('Alasan selisih, kondisi barang, dll');
            $table->string('foto')->nullable()->comment('Foto untuk dokumentasi');
            
            // Status Item
            $table->enum('status_item', ['match', 'over', 'short', 'damaged'])->comment('match=Sesuai, over=Lebih, short=Kurang, damaged=Rusak');
            
            $table->timestamps();
            
            // Index dengan nama pendek
            $table->index(['stock_opname_id', 'spare_part_id'], 'sp_opname_detail_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_stock_opname_details');
        Schema::dropIfExists('spare_part_stock_opnames');
    }
};
