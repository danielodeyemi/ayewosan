<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SecondBatchRolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $models = [
            'LabTestsCategory',
            'LabTestsGroup',
            'LabTestsResults',
            'LabTestsResultsTemplate',
        ];

        foreach ($models as $model) {
            Permission::create(['group' => $model, 'name' => 'viewAny' . $model]);
            Permission::create(['group' => $model, 'name' => 'view' . $model]);
            Permission::create(['group' => $model, 'name' => 'update' . $model]);
            Permission::create(['group' => $model, 'name' => 'create' . $model]);
            Permission::create(['group' => $model, 'name' => 'delete' . $model]);
            Permission::create(['group' => $model, 'name' => 'destroy' . $model]);
        }
    }
}
