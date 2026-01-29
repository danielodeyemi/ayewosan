<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // All models that need permissions
        $models = [
            'Patient',
            'PatientTransactions',
            'Bills',
            'LabTests',
            'ReferralTransactions',
            'User',
            'Role',
            'Permission',
            'LabTestsCategory',
            'LabTestsGroup',
            'LabTestsResults',
            'LabTestsResultsTemplate',
        ];

        // Create standard permissions for each model
        foreach ($models as $model) {
            Permission::create(['group' => $model, 'name' => 'viewAny' . $model]);
            Permission::create(['group' => $model, 'name' => 'view' . $model]);
            Permission::create(['group' => $model, 'name' => 'update' . $model]);
            Permission::create(['group' => $model, 'name' => 'create' . $model]);
            Permission::create(['group' => $model, 'name' => 'delete' . $model]);
            Permission::create(['group' => $model, 'name' => 'destroy' . $model]);
        }

        // Create viewOwn permissions for each model
        foreach ($models as $model) {
            Permission::create(['group' => $model, 'name' => 'viewOwn' . $model]);
        }

        // Create super-admin role with all permissions
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Create other roles without permissions (they will be assigned in UsersSeeder)
        Role::create(['name' => 'Receptionist']);
        Role::create(['name' => 'Accountant']);
        Role::create(['name' => 'Laboratory Technician']);
        Role::create(['name' => 'Non-technical Admin']);

        // Create super-admin user
        $user = User::firstOrCreate(
            ['email' => 'danieltheone09@gmail.com'],
            ['name' => 'Daniel', 'password' => bcrypt('Daniel')]
        );

        $user->assignRole('super-admin');
    }
}
