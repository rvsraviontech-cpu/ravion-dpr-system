<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class MaterialReceivedAccountantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::updateOrCreate(
            [
                'name' => 'material_received.accountant_verify',
            ],
            [
                'module' => 'Material Received',
                'description' => 'Verify supplier bills for approved material receipts.',
                'is_active' => true,
            ]
        );
    }
}