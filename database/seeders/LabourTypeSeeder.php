<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LabourCategory;
use App\Models\LabourType;

class LabourTypeSeeder extends Seeder
{
    public function run(): void
    {
        $labours = [
            'Structural & Civil' => [
                'Mason / Bricklayer',
                'Bar Bender / Steel Fixer',
                'Concrete Worker / Shuttering Carpenter',
                'Scaffolder',
                'Excavation Worker / Earthwork Labour',
                'Waterproofing Worker',
            ],

            'Finishing & Interior' => [
                'Carpenter / Woodwork Carpenter',
                'Painter',
                'Tile Layer / Flooring Worker',
                'False Ceiling Worker',
                'Wall Putty Worker / Plastering Worker',
                'Polishing Worker',
                'Glass & Glazing Worker',
                'Marble / Granite Fitter',
            ],

            'MEP (Mechanical, Electrical, Plumbing)' => [
                'Electrician',
                'Plumber',
                'AC Mechanic / HVAC Technician',
                'Welder / Fabricator',
                'Pipe Fitter',
                'Fire Fighting System Worker',
                'Solar Panel Installer',
                'CCTV / Security System Installer',
                'Data & Networking Technician',
            ],

            'Heavy Equipment & Machinery' => [
                'Crane Operator',
                'JCB / Excavator Operator',
                'Concrete Mixer Operator',
                'Forklift Operator',
                'Dumper / Tipper Driver',
            ],

            'Specialized Trades' => [
                'Surveyor / Layout Worker',
                'Driller / Boring Worker',
                'Pile Foundation Worker',
                'Waterproofing Applicator',
                'Insulation Worker',
                'Roofing Worker',
                'Road / Pavement Worker',
                'Pre-Fabricated Structure Installer',
            ],

            'Support & General' => [
                'General Labour / Helper',
                'Cleaning & Housekeeping Worker',
                'Security Guard',
                'Store Keeper / Material Handler',
                'Safety Officer / Supervisor',
            ],
        ];

        foreach ($labours as $categoryName => $types) {
            $category = LabourCategory::where('category_name', $categoryName)->first();

            if (!$category) {
                continue;
            }

            foreach ($types as $typeName) {
                LabourType::updateOrCreate(
                    ['labour_type_name' => $typeName],
                    [
                        'labour_category_id' => $category->id,
                    ]
                );
            }
        }
    }
}