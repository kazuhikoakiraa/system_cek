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
        Schema::create('machine_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->onDelete('set null');
            $table->foreignId('machine_maintenance_request_id')->nullable()->constrained('machine_maintenance_requests')->onDelete('set null');
            $table->enum('action_type', ['request_created', 'admin_approved', 'admin_rejected', 'teknisi_started', 'teknisi_completed', 'stock_deducted', 'component_replaced'])->default('request_created');
            $table->longText('deskripsi_perubahan')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index('machine_id');
            $table->index('action_type');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_audit_trails');
    }
};
