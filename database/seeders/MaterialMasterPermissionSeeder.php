<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class MaterialMasterPermissionSeeder extends Seeder
{
    /**
     * Add permissions for the new reusable Material Masters.
     */
    public function run(): void
    {
        $permissions = [
            /*
            |--------------------------------------------------------------------------
            | Material Type Master
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'material_types.view',
                'module' => 'Material Masters',
                'description' => 'View Material Type Master records.',
            ],
            [
                'name' => 'material_types.manage',
                'module' => 'Material Masters',
                'description' => 'Create, edit, activate and deactivate Material Type Master records.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Material Brand Master
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'brand_masters.view',
                'module' => 'Material Masters',
                'description' => 'View Material Brand Master records.',
            ],
            [
                'name' => 'brand_masters.manage',
                'module' => 'Material Masters',
                'description' => 'Create, edit, activate and deactivate Material Brand Master records.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Material Specification Master
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'material_specifications.view',
                'module' => 'Material Masters',
                'description' => 'View Material Specification Master records.',
            ],
            [
                'name' => 'material_specifications.manage',
                'module' => 'Material Masters',
                'description' => 'Create, edit, activate and deactivate Material Specification Master records.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Material Grade / Rating Master
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'material_grades.view',
                'module' => 'Material Masters',
                'description' => 'View Material Grade and Rating Master records.',
            ],
            [
                'name' => 'material_grades.manage',
                'module' => 'Material Masters',
                'description' => 'Create, edit, activate and deactivate Material Grade and Rating Master records.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                ],
                [
                    'module' => $permission['module'],
                    'description' => $permission['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}