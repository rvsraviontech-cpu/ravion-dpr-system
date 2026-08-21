<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class WeeklyLabourPaymentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'weekly_labour_payments.view',
            'weekly_labour_payments.create',
            'weekly_labour_payments.calculate',
            'weekly_labour_payments.edit',
            'weekly_labour_payments.submit',
            'weekly_labour_payments.approve',
            'weekly_labour_payments.reject',
            'weekly_labour_payments.mark_paid',
            'weekly_labour_payments.export',
            'weekly_labour_payments.manage_adjustments',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'module' => 'Weekly Labour Payments',
                    'is_active' => true,
                ]
            );
        }

        /*
         * Additive assignments only.
         * Existing role permissions remain untouched.
         */
        $rolePermissionMap = [
            'Admin' => $permissions,

            'Accountant' => [
                'weekly_labour_payments.view',
                'weekly_labour_payments.create',
                'weekly_labour_payments.calculate',
                'weekly_labour_payments.edit',
                'weekly_labour_payments.submit',
                'weekly_labour_payments.mark_paid',
                'weekly_labour_payments.export',
                'weekly_labour_payments.manage_adjustments',
            ],

            'CEO' => [
                'weekly_labour_payments.view',
                'weekly_labour_payments.approve',
                'weekly_labour_payments.reject',
                'weekly_labour_payments.export',
            ],
        ];

        foreach (
            $rolePermissionMap
            as $roleName => $permissionNames
        ) {
            $role = Role::query()
                ->where('name', $roleName)
                ->first();

            if (! $role) {
                continue;
            }

            $permissionIds = Permission::query()
                ->whereIn(
                    'name',
                    $permissionNames
                )
                ->pluck('id')
                ->all();

            $role
                ->permissions()
                ->syncWithoutDetaching(
                    $permissionIds
                );
        }
    }
}
