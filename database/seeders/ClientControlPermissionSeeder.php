<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientControlPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'client_control.view' => 'View Client Control administration',
            'client_control.manage_access' => 'Manage client identities and project grants',
            'client_control.publish' => 'Publish approved client-safe records',
            'client_control.withdraw' => 'Withdraw or supersede published records',
            'client_control.audit' => 'View Client Control audit history',
            'client_control.decisions.manage' => 'Manage named client decisions',
            'client_control.quality.publish' => 'Publish QA-released client proof',
            'client_control.documents.publish' => 'Publish approved client documents',
            'client_control.photos.publish' => 'Publish curated site photos',
            'client_control.commercial.view' => 'View approved client commercial records',
            'client_control.cases.manage' => 'Manage formal client cases',
            'client_control.files.request' => 'Request and review specific client files',
        ];
        foreach ($permissions as $name => $description) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                ['module' => 'Client Control', 'description' => $description, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
        // Deliberately no automatic role assignment: grant through existing Role Permissions UI.
    }
}
