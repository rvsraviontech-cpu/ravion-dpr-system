<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientDocumentVaultSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::transaction(function () use ($now) {
            $categories = [
                'Contracts & Agreements',
                'Statutory Approvals & Permissions',
                'Certificates & Reports',
                'Official Correspondence',
                'Warranties & Manuals',
                'Handover Records',
                'Other Project Documents',
            ];
            foreach ($categories as $order => $name) {
                DB::table('client_vault_categories')->updateOrInsert(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'sort_order' => $order + 1, 'is_active' => 1, 'updated_at' => $now]
                );
            }

            $permissions = [
                'client_control.documents.view' => 'View internal project document register',
                'client_control.documents.upload' => 'Upload new documents and revisions',
                'client_control.documents.manage' => 'Manage document metadata and categories',
                'client_control.documents.verify' => 'Verify submitted document revisions',
                'client_control.documents.approve' => 'Approve verified document revisions',
                'client_control.documents.publish' => 'Publish approved documents to client portal',
                'client_control.documents.withdraw' => 'Withdraw client-visible documents',
                'client_control.documents.audit' => 'View document audit history',
            ];

            foreach ($permissions as $name => $description) {
                $existing = DB::table('permissions')->where('name', $name)->first();
                if ($existing) {
                    // Preserve existing metadata, activation and role assignments.
                    continue;
                }
                DB::table('permissions')->insert([
                    'name' => $name,
                    'module' => 'Client Control',
                    'description' => $description,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Grant initial Document Vault permissions to Admin and PMO only.
            // Existing assignments for other roles are never removed.
            $permissionIds = DB::table('permissions')->whereIn('name', array_keys($permissions))
                ->where('is_active', 1)->pluck('id');
            $roles = DB::table('roles')->whereIn('name', ['Admin', 'PMO'])->pluck('id');
            foreach ($roles as $roleId) {
                foreach ($permissionIds as $permissionId) {
                    $exists = DB::table('permission_role')
                        ->where('role_id', $roleId)->where('permission_id', $permissionId)->exists();
                    if (!$exists) {
                        DB::table('permission_role')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                        ]);
                    }
                }
            }
        });
    }
}
