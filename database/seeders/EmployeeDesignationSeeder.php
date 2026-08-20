<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeDesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentIds = DB::table('departments')
            ->pluck('id', 'code');

        $designations = [
            // Management
            [
                'code' => 'CEO',
                'name' => 'Chief Executive Officer',
                'department_code' => 'MGT',
                'sort_order' => 10,
            ],
            [
                'code' => 'DIR',
                'name' => 'Director',
                'department_code' => 'MGT',
                'sort_order' => 20,
            ],
            [
                'code' => 'DGM',
                'name' => 'DGM',
                'department_code' => 'MGT',
                'sort_order' => 30,
            ],

            // PMO
            [
                'code' => 'PMGR',
                'name' => 'Project Manager',
                'department_code' => 'PMO',
                'sort_order' => 10,
            ],
            [
                'code' => 'PMOX',
                'name' => 'PMO Executive',
                'department_code' => 'PMO',
                'sort_order' => 20,
            ],

            // Projects
            [
                'code' => 'PENG',
                'name' => 'Project Engineer',
                'department_code' => 'PRJ',
                'sort_order' => 10,
            ],
            [
                'code' => 'SENG',
                'name' => 'Site Engineer',
                'department_code' => 'PRJ',
                'sort_order' => 20,
            ],
            [
                'code' => 'SSUP',
                'name' => 'Site Supervisor',
                'department_code' => 'PRJ',
                'sort_order' => 30,
            ],

            // Accounts & Finance
            [
                'code' => 'ACC',
                'name' => 'Accountant',
                'department_code' => 'FIN',
                'sort_order' => 10,
            ],
            [
                'code' => 'AEXE',
                'name' => 'Accounts Executive',
                'department_code' => 'FIN',
                'sort_order' => 20,
            ],

            // Procurement
            [
                'code' => 'PEXE',
                'name' => 'Procurement Executive',
                'department_code' => 'PUR',
                'sort_order' => 10,
            ],
            [
                'code' => 'PUEX',
                'name' => 'Purchase Executive',
                'department_code' => 'PUR',
                'sort_order' => 20,
            ],

            // Stores
            [
                'code' => 'STKP',
                'name' => 'Store Keeper',
                'department_code' => 'STR',
                'sort_order' => 10,
            ],
            [
                'code' => 'STEX',
                'name' => 'Store Executive',
                'department_code' => 'STR',
                'sort_order' => 20,
            ],

            // HR & Administration
            [
                'code' => 'HREX',
                'name' => 'HR Executive',
                'department_code' => 'HRA',
                'sort_order' => 10,
            ],
            [
                'code' => 'ADEX',
                'name' => 'Admin Executive',
                'department_code' => 'HRA',
                'sort_order' => 20,
            ],

            // Sales & Marketing
            [
                'code' => 'SLEX',
                'name' => 'Sales Executive',
                'department_code' => 'SLS',
                'sort_order' => 10,
            ],
            [
                'code' => 'SLRP',
                'name' => 'Sales Representative',
                'department_code' => 'SLS',
                'sort_order' => 20,
            ],
        ];

        foreach ($designations as $designation) {
            $departmentId = $departmentIds[$designation['department_code']] ?? null;

            DB::table('employee_designations')->updateOrInsert(
                [
                    'code' => $designation['code'],
                ],
                [
                    'name' => $designation['name'],
                    'department_id' => $departmentId,
                    'sort_order' => $designation['sort_order'],
                    'is_active' => true,
                    'remarks' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}