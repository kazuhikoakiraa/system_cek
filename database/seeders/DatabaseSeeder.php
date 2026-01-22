<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call([
            RoleSeeder::class,
        ]);

        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@system-cek.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'employee_id' => 'SA001',
                'department' => 'IT',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');

        $this->command->info('✓ Super Admin created!');
        $this->command->info('  Email: admin@system-cek.com');
        $this->command->info('  Password: password');
    }
}
