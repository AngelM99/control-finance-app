<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'approve users',

            // Financial products
            'view own financial products',
            'create financial products',
            'edit financial products',
            'delete financial products',
            'view all financial products',

            // Transactions
            'view own transactions',
            'create transactions',
            'edit transactions',
            'delete transactions',
            'view all transactions',

            // Installments
            'view own installments',
            'create installments',
            'edit installments',
            'delete installments',
            'view all installments',

            // Dashboard access
            'view admin dashboard',
            'view user dashboard',

            // Reports
            'view own reports',
            'view all reports',
            'export reports',

            // Settings
            'manage settings',
            'view settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Administrador role
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $adminRole->givePermissionTo(Permission::all());

        // Usuario Activo role
        $userRole = Role::firstOrCreate(['name' => 'Usuario Activo']);
        $userRole->givePermissionTo([
            'view own financial products',
            'create financial products',
            'edit financial products',
            'delete financial products',

            'view own transactions',
            'create transactions',
            'edit transactions',
            'delete transactions',

            'view own installments',
            'create installments',
            'edit installments',
            'delete installments',

            'view user dashboard',
            'view own reports',
            'view settings',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('');
        $this->command->info('Created roles:');
        $this->command->info('- Administrador (all permissions)');
        $this->command->info('- Usuario Activo (limited permissions)');
    }
}
