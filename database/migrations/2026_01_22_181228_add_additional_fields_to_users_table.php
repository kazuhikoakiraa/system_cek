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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->string('employee_id', 50)->unique()->nullable()->after('phone');
            $table->string('department', 100)->nullable()->after('employee_id');
            $table->enum('shift', ['pagi', 'siang', 'malam'])->nullable()->after('department');
            $table->boolean('is_active')->default(true)->after('shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'phone',
                'employee_id',
                'department',
                'shift',
                'is_active',
            ]);
        });
    }
};
