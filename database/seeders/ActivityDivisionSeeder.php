<?php

namespace Database\Seeders;

use App\Models\ActivityDivision;
use Illuminate\Database\Seeder;

class ActivityDivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['code' => '00-00-000', 'name' => 'PRE-CONSTRUCTION & APPROVALS'],
            ['code' => '01-00-000', 'name' => 'SITE ESTABLISHMENT'],
            ['code' => '02-00-000', 'name' => 'SITE PREPARATION'],
            ['code' => '03-00-000', 'name' => 'EARTHWORK'],
            ['code' => '04-00-000', 'name' => 'FOUNDATION WORKS'],
            ['code' => '05-00-000', 'name' => 'RCC STRUCTURE'],
            ['code' => '06-00-000', 'name' => 'MASONRY'],
            ['code' => '07-00-000', 'name' => 'PLASTERING'],
            ['code' => '08-00-000', 'name' => 'WATERPROOFING'],
            ['code' => '09-00-000', 'name' => 'FLOORING'],
            ['code' => '10-00-000', 'name' => 'DOORS & WINDOWS'],
            ['code' => '11-00-000', 'name' => 'ELECTRICAL SYSTEM'],
            ['code' => '12-00-000', 'name' => 'PLUMBING SYSTEM'],
            ['code' => '13-00-000', 'name' => 'HVAC SYSTEM'],
            ['code' => '14-00-000', 'name' => 'SUBCONTRACT LABOUR'],
            ['code' => '15-00-000', 'name' => 'MACHINERY & EQUIPMENT'],
            ['code' => '16-00-000', 'name' => 'FUEL & ENERGY'],
            ['code' => '17-00-000', 'name' => 'HAND TOOLS'],
            ['code' => '18-00-000', 'name' => 'ENGINEERING EQUIPMENT'],
            ['code' => '19-00-000', 'name' => 'SITE CONSUMABLES'],
            ['code' => '20-00-000', 'name' => 'SCAFFOLDING & TEMPORARY WORKS'],
            ['code' => '21-00-000', 'name' => 'SAFETY EQUIPMENT'],
            ['code' => '22-00-000', 'name' => 'SITE OVERHEADS'],
            ['code' => '23-00-000', 'name' => 'TESTING & QUALITY'],
            ['code' => '24-00-000', 'name' => 'HANDOVER & CLOSEOUT'],
        ];

        foreach ($divisions as $index => $division) {
            ActivityDivision::updateOrCreate(
                ['code' => $division['code']],
                [
                    'name' => $division['name'],
                    'sequence' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}