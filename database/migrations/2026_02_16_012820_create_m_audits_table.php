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
        Schema::create('m_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesin_id')->nullable()->constrained('mesins', 'id')->onDelete('set null')->name('audit_mesin_fk');
            $table->foreignId('m_request_id')->nullable()->constrained('m_requests', 'id')->onDelete('set null')->name('audit_request_fk');
            $table->foreignId('m_log_id')->nullable()->constrained('m_logs', 'id')->onDelete('set null')->name('audit_log_fk');
            $table->enum('action_type', ['request_created', 'admin_approved', 'admin_rejected', 'teknisi_started', 'teknisi_completed', 'stock_deducted'])->index();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->onDelete('set null')->name('audit_user_fk');
            $table->text('deskripsi_perubahan')->nullable();
            $table->json('perubahan_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_audits');
    }
};
