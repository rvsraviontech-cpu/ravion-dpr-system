<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSupportPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [            'view' => 'View support register',
            'create' => 'Create tickets on behalf of clients',
            'update' => 'Edit ticket details',
            'assign' => 'Assign responsible personnel',
            'reply' => 'Respond to clients',
            'internal_notes' => 'Manage confidential internal notes',
            'resolve' => 'Resolve tickets',
            'close' => 'Close tickets',
            'reopen' => 'Reopen tickets',
            'manage_contacts' => 'Manage project contact directory',
            'manage_masters' => 'Manage support masters',
            'download' => 'Download attachments',
            'export' => 'Export reports',
            'audit' => 'View audit history'];
        DB::transaction(function () use ($permissions) {
            foreach ($permissions as $suffix => $description) {
                $name = 'client_control.support.' . $suffix;
                if (!DB::table('permissions')->where('name', $name)->exists()) {
                    DB::table('permissions')->insert([
                        'name' => $name, 'module' => 'Client Control — Contact & Support',
                        'description' => $description, 'is_active' => true,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
