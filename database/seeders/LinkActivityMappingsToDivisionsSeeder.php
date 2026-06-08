<?php

namespace Database\Seeders;

use App\Models\ActivityDivision;
use App\Models\ActivityMapping;
use Illuminate\Database\Seeder;

class LinkActivityMappingsToDivisionsSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = ActivityMapping::whereNotNull('rh_cost_code')->get();

        foreach ($mappings as $mapping) {

            $rhCode = trim($mapping->rh_cost_code);

            // Example: RH-05-01-001
            $parts = explode('-', $rhCode);

            if (count($parts) < 2) {
                continue;
            }

            $divisionPrefix = $parts[1];

            $divisionCode = $divisionPrefix . '-00-000';

            $division = ActivityDivision::where('code', $divisionCode)->first();

            if ($division) {
                $mapping->update([
                    'activity_division_id' => $division->id,
                ]);
            }
        }
    }
}