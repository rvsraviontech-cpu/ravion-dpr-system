<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientControlApprovePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Register only. Do not assign approval authority automatically.
        DB::table('permissions')->updateOrInsert(
            ['name' => 'client_control.approve'],
            [
                'module' => 'Client Control',
                'description' => 'Independently approve verified client publications',
                'is_active' => 1,
            ]
        );
    }
}
