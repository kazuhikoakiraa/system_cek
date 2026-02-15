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
        Schema::table('spare_parts', function (Blueprint $table) {
            // Kategori
            $table->foreignId('category_id')->nullable()->after('id')->constrained('spare_part_categories')->nullOnDelete();
            
            // Management Stok
            $table->integer('stok_minimum')->default(10)->after('stok');
            $table->integer('stok_maksimum')->default(100)->after('stok_minimum');
            $table->string('lokasi_penyimpanan')->nullable()->after('stok_maksimum');
            
            // Harga & Nilai
            $table->decimal('harga_satuan', 15, 2)->nullable()->after('lokasi_penyimpanan');
            
            // Tracking & Identifikasi
            $table->string('batch_number')->nullable()->after('harga_satuan');
            $table->string('serial_number')->nullable()->after('batch_number');
            
            // Supplier & Pengadaan
            $table->string('supplier')->nullable()->after('serial_number');
            $table->date('tanggal_pengadaan')->nullable()->after('supplier');
            $table->year('tahun_pengadaan')->nullable()->after('tanggal_pengadaan');
            
            // Warranty & Maintenance
            $table->date('tanggal_warranty_mulai')->nullable()->after('tahun_pengadaan');
            $table->date('tanggal_warranty_expired')->nullable()->after('tanggal_warranty_mulai');
            $table->integer('warranty_bulan')->nullable()->after('tanggal_warranty_expired')->comment('Durasi warranty dalam bulan');
            
            // Status & Dokumentasi
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->after('warranty_bulan');
            $table->string('foto')->nullable()->after('status');
            $table->text('spesifikasi_teknis')->nullable()->after('foto');
            
            // Part Number & Manufacturer
            $table->string('part_number')->nullable()->after('spesifikasi_teknis');
            $table->string('manufacturer')->nullable()->after('part_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'stok_minimum',
                'stok_maksimum',
                'lokasi_penyimpanan',
                'harga_satuan',
                'batch_number',
                'serial_number',
                'supplier',
                'tanggal_pengadaan',
                'tahun_pengadaan',
                'tanggal_warranty_mulai',
                'tanggal_warranty_expired',
                'warranty_bulan',
                'status',
                'foto',
                'spesifikasi_teknis',
                'part_number',
                'manufacturer',
            ]);
        });
    }
};
