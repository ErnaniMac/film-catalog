<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Usuário Teste',
                'password' => Hash::make('password'),
            ]
        );

        if (!$user->hasRole('user')) {
            $user->assignRole('user');
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('User: user@example.com / password');
    }
}
