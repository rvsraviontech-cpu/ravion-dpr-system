<?php

namespace Database\Seeders;

use App\Models\BrandSegment;
use Illuminate\Database\Seeder;

class BrandSegmentSeeder extends Seeder
{
    /**
     * Seed the Brand Segment master.
     */
    public function run(): void
    {
        $segments = [
            [
                'segment_code' => 'BLD',
                'segment_name' => 'Building Materials',
                'sort_order' => 10,
                'remarks' => 'Cement, steel, blocks, masonry products, plaster and general structural/building materials.',
            ],
            [
                'segment_code' => 'CHEM',
                'segment_name' => 'Construction Chemicals',
                'sort_order' => 20,
                'remarks' => 'Admixtures, grouts, bonding agents, adhesives, repair chemicals and related construction chemicals.',
            ],
            [
                'segment_code' => 'WPF',
                'segment_name' => 'Waterproofing & Sealants',
                'sort_order' => 30,
                'remarks' => 'Waterproofing membranes, coatings, sealants and related systems.',
            ],
            [
                'segment_code' => 'FLR',
                'segment_name' => 'Flooring, Tiles & Stone',
                'sort_order' => 40,
                'remarks' => 'Tiles, natural stone, flooring systems and associated products.',
            ],
            [
                'segment_code' => 'PNT',
                'segment_name' => 'Paints & Coatings',
                'sort_order' => 50,
                'remarks' => 'Paints, primers, putty, decorative coatings and protective coatings.',
            ],
            [
                'segment_code' => 'DWH',
                'segment_name' => 'Doors, Windows & Hardware',
                'sort_order' => 60,
                'remarks' => 'Doors, windows, glazing systems, architectural hardware and related accessories.',
            ],
            [
                'segment_code' => 'ELE',
                'segment_name' => 'Electrical',
                'sort_order' => 70,
                'remarks' => 'Wires, cables, switches, sockets, distribution boards, switchgear, lighting and electrical accessories.',
            ],
            [
                'segment_code' => 'ELV',
                'segment_name' => 'ELV, Security & Automation',
                'sort_order' => 80,
                'remarks' => 'CCTV, networking, communication, access control, automation and other ELV systems.',
            ],
            [
                'segment_code' => 'PLB',
                'segment_name' => 'Plumbing & Sanitary',
                'sort_order' => 90,
                'remarks' => 'Pipes, fittings, valves, drainage products, sanitaryware and plumbing accessories.',
            ],
            [
                'segment_code' => 'HVAC',
                'segment_name' => 'HVAC & Ventilation',
                'sort_order' => 100,
                'remarks' => 'Air-conditioning, ventilation, ducting and HVAC equipment/components.',
            ],
            [
                'segment_code' => 'FIR',
                'segment_name' => 'Fire & Life Safety Systems',
                'sort_order' => 110,
                'remarks' => 'Fire alarm, firefighting, smoke control and related fire/life-safety systems.',
            ],
            [
                'segment_code' => 'KIT',
                'segment_name' => 'Kitchen & Appliances',
                'sort_order' => 120,
                'remarks' => 'Kitchen systems, appliances and related kitchen accessories.',
            ],
            [
                'segment_code' => 'INT',
                'segment_name' => 'Interiors & Joinery',
                'sort_order' => 130,
                'remarks' => 'Plywood, boards, laminates, furniture, joinery and interior finishing products.',
            ],
            [
                'segment_code' => 'EQP',
                'segment_name' => 'Machinery & Equipment',
                'sort_order' => 140,
                'remarks' => 'Construction machinery, pumps, powered site equipment and related equipment.',
            ],
            [
                'segment_code' => 'TLS',
                'segment_name' => 'Tools & Accessories',
                'sort_order' => 150,
                'remarks' => 'Hand tools, tool systems, tool accessories and related products.',
            ],
            [
                'segment_code' => 'SAF',
                'segment_name' => 'Safety & PPE',
                'sort_order' => 160,
                'remarks' => 'Personal protective equipment, barricading, fall protection and site safety products.',
            ],
            [
                'segment_code' => 'SCF',
                'segment_name' => 'Scaffolding & Temporary Works',
                'sort_order' => 170,
                'remarks' => 'Scaffolding systems, access systems and temporary works products.',
            ],
            [
                'segment_code' => 'ENG',
                'segment_name' => 'Engineering & Survey Equipment',
                'sort_order' => 180,
                'remarks' => 'Surveying, measurement, testing, inspection and engineering equipment.',
            ],
            [
                'segment_code' => 'FUE',
                'segment_name' => 'Fuel, Lubricants & Energy',
                'sort_order' => 190,
                'remarks' => 'Fuel, lubricants, energy products and related supplies.',
            ],
            [
                'segment_code' => 'CON',
                'segment_name' => 'Site Consumables & General Supplies',
                'sort_order' => 200,
                'remarks' => 'General site consumables, miscellaneous supplies and supporting site materials.',
            ],
            [
                'segment_code' => 'GEN',
                'segment_name' => 'General / Multi-category',
                'sort_order' => 210,
                'remarks' => 'For genuine multi-category brand identities that do not reasonably belong to one primary commercial segment.',
            ],
        ];

        foreach ($segments as $segment) {
            BrandSegment::updateOrCreate(
                [
                    'segment_code' => $segment['segment_code'],
                ],
                [
                    'segment_name' => $segment['segment_name'],
                    'sort_order' => $segment['sort_order'],
                    'is_active' => true,
                    'remarks' => $segment['remarks'],
                ]
            );
        }
    }
}