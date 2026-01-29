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
        // Define role-specific permissions
        $rolePermissions = [
            'Receptionist' => [
                'viewAnyPatient', 'viewPatient', 'createPatient', 'updatePatient',
                'viewAnyBills', 'viewBills', 'createBills',
                'viewAnyPatientTransactions', 'viewPatientTransactions', 'createPatientTransactions',
                'viewOwnPatient', 'viewOwnBills', 'viewOwnPatientTransactions',
            ],
            'Accountant' => [
                'viewAnyPatient', 'viewPatient',
                'viewAnyBills', 'viewBills', 'updateBills',
                'viewAnyPatientTransactions', 'viewPatientTransactions', 'updatePatientTransactions',
                'viewAnyReferralTransactions', 'viewReferralTransactions', 'updateReferralTransactions',
                'viewOwnPatient', 'viewOwnBills', 'viewOwnPatientTransactions', 'viewOwnReferralTransactions',
            ],
            'Laboratory Technician' => [
                'viewAnyPatient', 'viewPatient',
                'viewAnyBills', 'viewBills',
                'viewAnyLabTests', 'viewLabTests', 'createLabTests', 'updateLabTests',
                'viewAnyLabTestsResults', 'viewLabTestsResults', 'createLabTestsResults', 'updateLabTestsResults',
                'viewAnyLabTestsCategory', 'viewLabTestsCategory',
                'viewAnyLabTestsGroup', 'viewLabTestsGroup',
                'viewAnyLabTestsResultsTemplate', 'viewLabTestsResultsTemplate',
                'viewOwnPatient', 'viewOwnBills', 'viewOwnLabTests', 'viewOwnLabTestsResults',
            ],
            'Non-technical Admin' => [
                'viewAnyPatient', 'viewPatient', 'createPatient', 'updatePatient',
                'viewAnyBills', 'viewBills', 'createBills', 'updateBills',
                'viewAnyPatientTransactions', 'viewPatientTransactions', 'createPatientTransactions',
                'viewAnyUser', 'viewUser', 'createUser', 'updateUser',
                'viewOwnPatient', 'viewOwnBills', 'viewOwnPatientTransactions', 'viewOwnUser',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            // Create role
            $role = Role::findOrCreate($roleName);
            
            // Assign specific permissions to role
            $role->syncPermissions($permissions);

            // Create corresponding user
            $user = User::firstOrCreate(
                ['email' => strtolower(str_replace(' ', '', $roleName)) . '@example.com'],
                ['name' => $roleName, 'password' => bcrypt('password')]
            );

            // Assign role to user
            $user->assignRole($roleName);
        }
    }
}
