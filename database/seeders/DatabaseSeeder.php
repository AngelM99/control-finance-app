<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RoleAndPermissionSeeder::class,
        ]);

        // Create default admin user
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@controlfinance.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
                'dni' => '12345678',
                'is_approved' => true,
                'approved_at' => now(),
                'provider' => 'manual',
            ]
        );
        $admin->assignRole('Administrador');

        // Create a test active user
        $activeUser = \App\Models\User::firstOrCreate(
            ['email' => 'usuario@controlfinance.com'],
            [
                'name' => 'Usuario de Prueba',
                'password' => bcrypt('usuario123'),
                'dni' => '87654321',
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => $admin->id,
                'provider' => 'manual',
            ]
        );
        $activeUser->assignRole('Usuario Activo');

        $this->command->info('');
        $this->command->info('Default users created:');
        $this->command->info('Admin: admin@controlfinance.com / admin123');
        $this->command->info('User: usuario@controlfinance.com / usuario123');
    }
}
