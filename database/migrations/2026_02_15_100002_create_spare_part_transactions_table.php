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
        Schema::create('spare_part_transactions', function (Blueprint $table) {
            $table->id();
            
            // Nomor Transaksi (auto-generate, unique)
            $table->string('nomor_transaksi')->unique();
            
            // Spare Part Reference
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            
            // Tipe Transaksi
            $table->enum('tipe_transaksi', ['IN', 'OUT', 'ADJUSTMENT', 'RETURN'])->comment('IN=Masuk, OUT=Keluar, ADJUSTMENT=Penyesuaian, RETURN=Retur');
            
            // Tanggal & User
            $table->dateTime('tanggal_transaksi');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->comment('User yang melakukan transaksi');
            
            // Jumlah & Stok
            $table->integer('jumlah')->comment('Jumlah (+/-) sesuai tipe transaksi');
            $table->integer('stok_sebelum')->comment('Stok sebelum transaksi (untuk audit trail)');
            $table->integer('stok_sesudah')->comment('Stok sesudah transaksi (untuk audit trail)');
            
            // Reference (Polymorphic untuk flexibility)
            $table->string('reference_type')->nullable()->comment('Model class: MaintenanceReport, StockOpname, dll');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID dari reference model');
            
            // Detail Transaksi
            $table->text('keterangan')->nullable();
            $table->string('dokumen')->nullable()->comment('Upload PO, Faktur, BON, dll');
            
            // Approval Workflow
            $table->enum('status_approval', ['pending', 'approved', 'rejected'])->default('pending')->comment('Status persetujuan transaksi');
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete()->comment('User yang menyetujui');
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes untuk performa
            $table->index(['spare_part_id', 'tanggal_transaksi']);
            $table->index(['tipe_transaksi', 'status_approval']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_transactions');
    }
};
