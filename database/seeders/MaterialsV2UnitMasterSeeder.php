<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialsV2UnitMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Materials V2 Additional Unit Master
        |--------------------------------------------------------------------------
        |
        | Existing units are intentionally NOT renamed or recreated.
        |
        | These units fill the gaps required by the finalized
        | Ravion Materials ERP catalogue.
        |
        */

        $units = [
            [
                'unit_name' => 'Bottle',
                'unit_code' => 'BTL',
                'symbol' => 'btl',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Box',
                'unit_code' => 'BOX',
                'symbol' => 'box',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Can',
                'unit_code' => 'CAN',
                'symbol' => 'can',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Coil',
                'unit_code' => 'COIL',
                'symbol' => 'coil',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Cylinder',
                'unit_code' => 'CYL',
                'symbol' => 'cyl',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Kilolitre',
                'unit_code' => 'KL',
                'symbol' => 'kl',
                'unit_type' => 'Liquid',
                'decimal_allowed' => true,
            ],
            [
                'unit_name' => 'Kit',
                'unit_code' => 'KIT',
                'symbol' => 'kit',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Length',
                'unit_code' => 'LENGTH',
                'symbol' => 'length',
                'unit_type' => 'Length',
                'decimal_allowed' => true,
            ],
            [
                'unit_name' => 'Lot',
                'unit_code' => 'LOT',
                'symbol' => 'lot',
                'unit_type' => 'Count',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Millilitre',
                'unit_code' => 'ML',
                'symbol' => 'ml',
                'unit_type' => 'Liquid',
                'decimal_allowed' => true,
            ],
            [
                'unit_name' => 'Pack',
                'unit_code' => 'PACK',
                'symbol' => 'pack',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Pair',
                'unit_code' => 'PAIR',
                'symbol' => 'pair',
                'unit_type' => 'Count',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Ream',
                'unit_code' => 'REAM',
                'symbol' => 'ream',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Roll',
                'unit_code' => 'ROLL',
                'symbol' => 'roll',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Set',
                'unit_code' => 'SET',
                'symbol' => 'set',
                'unit_type' => 'Count',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Sheet',
                'unit_code' => 'SHEET',
                'symbol' => 'sheet',
                'unit_type' => 'Count',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Square Meter',
                'unit_code' => 'SQM',
                'symbol' => 'sqm',
                'unit_type' => 'Area',
                'decimal_allowed' => true,
            ],
            [
                'unit_name' => 'Tin',
                'unit_code' => 'TIN',
                'symbol' => 'tin',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
            [
                'unit_name' => 'Tube',
                'unit_code' => 'TUBE',
                'symbol' => 'tube',
                'unit_type' => 'Packaging',
                'decimal_allowed' => false,
            ],
        ];

        foreach ($units as $unit) {
            /*
            |--------------------------------------------------------------------------
            | Safe idempotent insert/update
            |--------------------------------------------------------------------------
            |
            | We match first by unit_code because that is the stable business
            | identifier. If it already exists, we update its descriptive fields.
            |
            */

            DB::table('unit_masters')->updateOrInsert(
                [
                    'unit_code' => $unit['unit_code'],
                ],
                [
                    'unit_name' => $unit['unit_name'],
                    'symbol' => $unit['symbol'],
                    'unit_type' => $unit['unit_type'],
                    'decimal_allowed' => $unit['decimal_allowed'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}