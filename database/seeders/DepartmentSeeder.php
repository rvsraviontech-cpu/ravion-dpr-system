<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'code' => 'MGT',
                'name' => 'Management',
                'sort_order' => 10,
                'is_active' => true,
                'remarks' => null,
            ],
            [
                'code' => 'PMO',
                'name' => 'PMO',
                'sort_order' => 20,
                'is_active' => true,
                'remarks' => null,
            ],
            [
                'code' => 'PRJ',
                'name' => 'Projects',
                'sort_order' => 30,
                'is_active' => true,
                'remarks' => null,
            ],
            [
                'code' => 'FIN',
                'name' => 'Accounts & Finance',
                'sort_order' => 40,
                'is_active' => true,
                'remarks' => null,
            ],
            [
                'code' => 'PUR',
                'name' => 'Procurement',
                'sort_order' => 50,
                'is_active' => true,
                'remarks' => null,
            ],
            [
                'code' => 'STR',
                'name' => 'Stores',
                'sort_order' => 60,
                'is_active' => true,
                'remarks' => null,
            ],
            [
                'code' => 'HRA',
                'name' => 'HR & Administration',
                'sort_order' => 70,
                'is_active' => true,
                'remarks' => null,
            ],
            [
                'code' => 'SLS',
                'name' => 'Sales & Marketing',
                'sort_order' => 80,
                'is_active' => true,
                'remarks' => null,
            ],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                [
                    'code' => $department['code'],
                ],
                [
                    'name' => $department['name'],
                    'sort_order' => $department['sort_order'],
                    'is_active' => $department['is_active'],
                    'remarks' => $department['remarks'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}