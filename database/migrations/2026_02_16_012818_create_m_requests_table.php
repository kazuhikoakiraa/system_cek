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
        Schema::create('m_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('mesin_id')->constrained('mesins', 'id')->onDelete('cascade')->name('request_mesin_fk');
            $table->foreignId('komponen_id')->nullable()->constrained('m_components', 'id')->onDelete('set null')->name('request_komponen_fk');
            $table->foreignId('created_by')->constrained('users', 'id')->onDelete('cascade')->name('request_creator_fk');
            $table->dateTime('requested_at');
            $table->text('problema_deskripsi');
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users', 'id')->onDelete('set null')->name('request_approver_fk');
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_requests');
    }
};
