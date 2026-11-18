<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientGrantApprovalPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'client_control.grants.approve'],
            ['module' => 'Client Control', 'description' => 'Independently approve client project grants', 'is_active' => 1, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
