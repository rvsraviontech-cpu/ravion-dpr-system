<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class ClientDecisionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view' => 'View decision requests and responses',
            'create' => 'Create decision requests',
            'update' => 'Edit draft decision requests',
            'review' => 'Review and approve requests',
            'publish' => 'Publish approved requests to clients',
            'acknowledge' => 'Acknowledge client responses',
            'close' => 'Close resolved decisions',
            'withdraw' => 'Withdraw published requests',
        ];
        foreach ($permissions as $key => $description) {
            Permission::updateOrCreate(
                ['name' => 'client_control.decisions.'.$key],
                ['module' => 'Client Control — My Decisions', 'description' => $description, 'is_active' => true]
            );
        }
    }
}
