<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientPaymentPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'client_control.payments.view' => 'View client contracts, payment schedules and receipts',
            'client_control.payments.manage' => 'Create and edit draft client contracts and payment milestones',
            'client_control.payments.submit' => 'Submit client payment schedules for verification',
            'client_control.payments.verify' => 'Verify submitted client payment schedules',
            'client_control.payments.approve' => 'Approve verified client payment schedules',
            'client_control.payments.publish' => 'Publish approved client payment information',
            'client_control.payments.withdraw' => 'Withdraw published client payment information',
            'client_control.payments.receipts.record' => 'Record client payments received',
            'client_control.payments.receipts.verify' => 'Verify recorded client payment receipts',
            'client_control.payments.retention.manage' => 'Manage client contract retention milestones',
            'client_control.payments.revisions.approve' => 'Approve revised contractual payment schedules',
            'client_control.payments.audit' => 'View client payment and schedule audit history',
        ];

        DB::transaction(function () use ($permissions): void {
            foreach ($permissions as $name => $description) {
                // Never reactivate an intentionally disabled permission or overwrite
                // existing metadata/role assignments when the seeder is rerun.
                if (DB::table('permissions')->where('name', $name)->exists()) {
                    continue;
                }

                DB::table('permissions')->insert([
                    'name' => $name,
                    'module' => 'Client Control — Payments & Milestones',
                    'description' => $description,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        // Permissions are deliberately assigned using Administration > Role Permissions.
        // Do not automatically expose contractual amounts to Engineer or other roles.
    }
}
