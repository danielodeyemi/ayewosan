<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ThirdBatchRolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

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

        foreach ($models as $model) {
            Permission::create(['group' => $model, 'name' => 'viewOwn' . $model]);
        }
    }
}
