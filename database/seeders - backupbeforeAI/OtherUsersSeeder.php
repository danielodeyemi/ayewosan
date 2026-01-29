<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Create roles and users for each role
        $roles = ['Receptionist', 'Accountant', 'Laboratory Technician', 'Non-technical Admin'];
        
        foreach ($roles as $roleName) {
            // Create role with all permissions
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo(Permission::all());

            // Create corresponding user
            $user = User::firstOrCreate(
                ['email' => strtolower($roleName) . '@example.com'],
                ['name' => $roleName, 'password' => bcrypt('password')]
            );

            // Assign role to user
            $user->assignRole($roleName);
        }
    }
}
