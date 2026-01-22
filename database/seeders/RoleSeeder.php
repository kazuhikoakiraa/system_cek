<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $supervisor = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Get all permissions
        $allPermissions = Permission::all();

        // Super Admin gets all permissions
        $superAdmin->syncPermissions($allPermissions);

        // Admin gets most permissions except user management
        $admin->syncPermissions(
            $allPermissions->filter(fn($p) => !str_contains($p->name, 'role'))
        );

        // Supervisor gets view and update permissions for checklists
        $supervisor->syncPermissions(
            $allPermissions->filter(fn($p) => 
                str_contains($p->name, 'view') || 
                str_contains($p->name, 'update')
            )
        );

        // Operator gets view and create permissions
        $operator->syncPermissions(
            $allPermissions->filter(fn($p) => 
                str_contains($p->name, 'view') || 
                str_contains($p->name, 'create')
            )
        );

        // Viewer gets only view permissions
        $viewer->syncPermissions(
            $allPermissions->filter(fn($p) => str_contains($p->name, 'view'))
        );

        $this->command->info('✓ Roles created successfully!');
        $this->command->info('✓ Super Admin: Full access');
        $this->command->info('✓ Admin: All except role management');
        $this->command->info('✓ Supervisor: View & Update');
        $this->command->info('✓ Operator: View & Create');
        $this->command->info('✓ Viewer: View only');
    }
}
