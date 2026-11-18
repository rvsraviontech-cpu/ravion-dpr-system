<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientQualityPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'client_control.quality.view' => 'View QA/QC Test Master',
            'client_control.quality.master.manage' => 'Create, edit and archive QA/QC master tests',
            'client_control.quality.project.manage' => 'Manage project quality plans (future phase)',
            'client_control.quality.results.create' => 'Enter test results (future phase)',
            'client_control.quality.results.verify' => 'Verify test results (future phase)',
            'client_control.quality.results.approve' => 'Approve test results (future phase)',
            'client_control.quality.publish' => 'Publish approved results (future phase)',
            'client_control.quality.withdraw' => 'Withdraw client publication (future phase)',
        ];
        foreach ($permissions as $name => $description) {
            DB::table('permissions')->updateOrInsert(['name' => $name], [
                'module' => 'Client Control - Quality & Testing',
                'description' => $description,
                'is_active' => true,
            ]);
        }
        // Deliberately do not guess role IDs or silently grant permissions.
        // Assign Admin/PMO in Administration > Role Permissions.
    }
}
