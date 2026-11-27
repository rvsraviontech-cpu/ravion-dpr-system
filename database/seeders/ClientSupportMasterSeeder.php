<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSupportMasterSeeder extends Seeder
{
    public function run(): void
    {
        $masters = [
            'client_support_contact_roles' => [            'PMO' => 'Project Management Officer',
            'DGM' => 'Deputy General Manager',
            'SE' => 'Site Engineer',
            'ARCH' => 'Architect',
            'STR' => 'Structural Consultant',
            'MEP' => 'MEP Consultant',
            'ACC' => 'Accounts & Billing',
            'QA' => 'Quality & Testing',
            'CRM' => 'Client Relationship Manager',
            'SUP' => 'Customer Support',
            'ESC' => 'Escalation Contact'],
            'client_support_categories' => [            'GEN' => 'General Enquiry',
            'PRG' => 'Construction Progress',
            'QLT' => 'Quality Concern',
            'DRW' => 'Drawings & Design',
            'PAY' => 'Payments & Billing',
            'DOC' => 'Documents & Approvals',
            'MAT' => 'Material Specifications',
            'SIT' => 'Site Visit Request',
            'CHG' => 'Change Request',
            'DLY' => 'Delay Concern',
            'COM' => 'Complaint',
            'WAR' => 'Warranty & Defects',
            'OTH' => 'Other'],
        ];
        DB::transaction(function () use ($masters) {
            foreach ($masters as $table => $entries) {
                $order = 0;
                foreach ($entries as $code => $name) {
                    $order += 10;
                    DB::table($table)->insertOrIgnore([
                        'code' => $code, 'name' => $name, 'sort_order' => $order,
                        'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
