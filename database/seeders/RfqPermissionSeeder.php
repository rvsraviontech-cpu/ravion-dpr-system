<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RfqPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'rfqs.view' => 'View RFQ register and details',
            'rfqs.create' => 'Create RFQ drafts',
            'rfqs.edit' => 'Edit RFQ drafts',
            'rfqs.invitations.manage' => 'Manage vendor invitations',
            'rfqs.compare' => 'View vendor quotations and financial comparisons',
            'rfqs.awards.approve' => 'Approve vendor quotation awards',
        ];

        foreach ($permissions as $name => $description) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'module' => 'Quotations (RFQ)',
                    'description' => $description,
                    'is_active' => 1,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
