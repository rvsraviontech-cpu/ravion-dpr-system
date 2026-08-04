<?php

namespace Database\Seeders;

use App\Models\ManpowerSource;
use Illuminate\Database\Seeder;

class ManpowerSourceSeeder extends Seeder
{
    /**
     * Seed the manpower source master.
     */
    public function run(): void
    {
        $manpowerSources = [
            [
                'code' => 'COMPANY',
                'name' => 'Company Labour',
                'requires_contractor' => false,
                'is_system' => true,
                'sort_order' => 10,
                'is_active' => true,
                'remarks' => 'Labour directly engaged and managed by the company.',
            ],
            [
                'code' => 'CONTRACTOR',
                'name' => 'Contractor Labour',
                'requires_contractor' => true,
                'is_system' => true,
                'sort_order' => 20,
                'is_active' => true,
                'remarks' => 'Labour supplied and managed through a registered contractor.',
            ],
            [
                'code' => 'SUBCONTRACTOR',
                'name' => 'Subcontractor Labour',
                'requires_contractor' => true,
                'is_system' => true,
                'sort_order' => 30,
                'is_active' => true,
                'remarks' => 'Labour supplied through a subcontractor or secondary contracting arrangement.',
            ],
            [
                'code' => 'DAILY_WAGE',
                'name' => 'Daily Wage Labour',
                'requires_contractor' => false,
                'is_system' => true,
                'sort_order' => 40,
                'is_active' => true,
                'remarks' => 'Labour directly engaged on a daily wage basis.',
            ],
            [
                'code' => 'AGENCY',
                'name' => 'Labour Agency',
                'requires_contractor' => true,
                'is_system' => true,
                'sort_order' => 50,
                'is_active' => true,
                'remarks' => 'Labour supplied through an external manpower or labour agency.',
            ],
            [
                'code' => 'TEMPORARY',
                'name' => 'Temporary Labour',
                'requires_contractor' => false,
                'is_system' => true,
                'sort_order' => 60,
                'is_active' => true,
                'remarks' => 'Temporary manpower engaged for short-term project or site requirements.',
            ],
        ];

        foreach ($manpowerSources as $manpowerSource) {
            ManpowerSource::updateOrCreate(
                ['code' => $manpowerSource['code']],
                $manpowerSource
            );
        }
    }
}