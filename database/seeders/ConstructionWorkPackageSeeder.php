<?php

namespace Database\Seeders;

use App\Models\ActivityDivision;
use App\Models\ConstructionWorkPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConstructionWorkPackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $activityDivisions = ActivityDivision::query()
                ->pluck('id', 'name');

            $groups = [
                [
                    'code' => 'PRE',
                    'name' => 'Pre-Construction & Site Setup',
                    'activity_division' => 'PRE-CONSTRUCTION & APPROVALS',
                    'sort_order' => 10,
                    'children' => [
                        ['code' => 'PRE-SURVEY', 'name' => 'Surveying & Setting Out'],
                        ['code' => 'PRE-BARRICADE', 'name' => 'Barricading & Site Protection'],
                        ['code' => 'PRE-TEMP-ELEC', 'name' => 'Temporary Electrical Works'],
                        ['code' => 'PRE-TEMP-PLB', 'name' => 'Temporary Water & Plumbing'],
                        ['code' => 'PRE-SITE-OFFICE', 'name' => 'Site Office & Temporary Facilities'],
                    ],
                ],

                [
                    'code' => 'EARTH',
                    'name' => 'Earthwork & Ground Preparation',
                    'activity_division' => 'EARTHWORK',
                    'sort_order' => 20,
                    'children' => [
                        ['code' => 'EARTH-EXC', 'name' => 'Excavation'],
                        ['code' => 'EARTH-FILL', 'name' => 'Earth Filling & Backfilling'],
                        ['code' => 'EARTH-COMP', 'name' => 'Compaction'],
                        ['code' => 'EARTH-SUBBASE', 'name' => 'Sub-Base & Ground Preparation'],
                        ['code' => 'EARTH-DEWATER', 'name' => 'Dewatering'],
                    ],
                ],

                [
                    'code' => 'FOUND',
                    'name' => 'Foundation & Substructure',
                    'activity_division' => 'FOUNDATION WORKS',
                    'sort_order' => 30,
                    'children' => [
                        ['code' => 'FOUND-PCC', 'name' => 'PCC & Levelling Course'],
                        ['code' => 'FOUND-FOOT', 'name' => 'Footings'],
                        ['code' => 'FOUND-PILE', 'name' => 'Piling & Pile Caps'],
                        ['code' => 'FOUND-RAFT', 'name' => 'Raft Foundation'],
                        ['code' => 'FOUND-PED', 'name' => 'Pedestals & Column Starters'],
                        ['code' => 'FOUND-PLINTH', 'name' => 'Plinth Beams & Plinth Works'],
                        ['code' => 'FOUND-UGT', 'name' => 'Underground Tanks & Sumps'],
                    ],
                ],

                [
                    'code' => 'RCC',
                    'name' => 'RCC Structural Works',
                    'activity_division' => 'RCC STRUCTURE',
                    'sort_order' => 40,
                    'children' => [
                        ['code' => 'RCC-REBAR', 'name' => 'Reinforcement Steel Works'],
                        ['code' => 'RCC-FORM', 'name' => 'Formwork & Shuttering'],
                        ['code' => 'RCC-COLUMN', 'name' => 'Columns'],
                        ['code' => 'RCC-BEAM', 'name' => 'Beams'],
                        ['code' => 'RCC-SLAB', 'name' => 'Slabs'],
                        ['code' => 'RCC-STAIR', 'name' => 'Staircases'],
                        ['code' => 'RCC-WALL', 'name' => 'RCC Walls'],
                        ['code' => 'RCC-CONCRETE', 'name' => 'Concrete Placement'],
                        ['code' => 'RCC-CURING', 'name' => 'Concrete Curing'],
                        ['code' => 'RCC-REPAIR', 'name' => 'Concrete Repair & Grouting'],
                    ],
                ],

                [
                    'code' => 'STEEL',
                    'name' => 'Structural Steel & Fabrication',
                    'activity_division' => null,
                    'sort_order' => 50,
                    'children' => [
                        ['code' => 'STEEL-STRUCT', 'name' => 'Structural Steel'],
                        ['code' => 'STEEL-FAB', 'name' => 'Metal Fabrication'],
                        ['code' => 'STEEL-WELD', 'name' => 'Welding Works'],
                        ['code' => 'STEEL-RAIL', 'name' => 'Railings & Handrails'],
                        ['code' => 'STEEL-GRATE', 'name' => 'Gratings & Metal Accessories'],
                    ],
                ],

                [
                    'code' => 'MASON',
                    'name' => 'Masonry & Blockwork',
                    'activity_division' => 'MASONRY',
                    'sort_order' => 60,
                    'children' => [
                        ['code' => 'MASON-BRICK', 'name' => 'Brick Masonry'],
                        ['code' => 'MASON-AAC', 'name' => 'AAC Blockwork'],
                        ['code' => 'MASON-CONCBLOCK', 'name' => 'Concrete Blockwork'],
                        ['code' => 'MASON-PART', 'name' => 'Internal Partitions'],
                        ['code' => 'MASON-MORTAR', 'name' => 'Masonry Mortar'],
                        ['code' => 'MASON-REINF', 'name' => 'Masonry Reinforcement & Mesh'],
                    ],
                ],

                [
                    'code' => 'PLASTER',
                    'name' => 'Plastering & Wall Preparation',
                    'activity_division' => 'PLASTERING',
                    'sort_order' => 70,
                    'children' => [
                        ['code' => 'PLASTER-INT', 'name' => 'Internal Plastering'],
                        ['code' => 'PLASTER-EXT', 'name' => 'External Plastering'],
                        ['code' => 'PLASTER-CEIL', 'name' => 'Ceiling Plastering'],
                        ['code' => 'PLASTER-REPAIR', 'name' => 'Plaster Repair & Patching'],
                        ['code' => 'PLASTER-SCREED', 'name' => 'Wall Levelling & Screeding'],
                    ],
                ],

                [
                    'code' => 'WATER',
                    'name' => 'Waterproofing',
                    'activity_division' => 'WATERPROOFING',
                    'sort_order' => 80,
                    'children' => [
                        ['code' => 'WATER-BASE', 'name' => 'Basement Waterproofing'],
                        ['code' => 'WATER-TOILET', 'name' => 'Toilet & Wet Area Waterproofing'],
                        ['code' => 'WATER-TERRACE', 'name' => 'Terrace Waterproofing'],
                        ['code' => 'WATER-BALCONY', 'name' => 'Balcony Waterproofing'],
                        ['code' => 'WATER-TANK', 'name' => 'Water Tank Waterproofing'],
                        ['code' => 'WATER-JOINT', 'name' => 'Construction Joint Treatment'],
                        ['code' => 'WATER-SEAL', 'name' => 'Sealants & Joint Sealing'],
                    ],
                ],

                [
                    'code' => 'PLB',
                    'name' => 'Plumbing Works',
                    'activity_division' => 'PLUMBING SYSTEM',
                    'sort_order' => 90,
                    'children' => [
                        ['code' => 'PLB-WATER', 'name' => 'Water Supply Piping'],
                        ['code' => 'PLB-DRAIN', 'name' => 'Soil, Waste & Drainage Piping'],
                        ['code' => 'PLB-RAIN', 'name' => 'Rainwater Piping'],
                        ['code' => 'PLB-VALVE', 'name' => 'Valves & Plumbing Accessories'],
                        ['code' => 'PLB-PUMP', 'name' => 'Pumps & Pumping Systems'],
                        ['code' => 'PLB-TANK', 'name' => 'Water Storage Tanks'],
                        ['code' => 'PLB-INSUL', 'name' => 'Pipe Insulation'],
                        ['code' => 'PLB-CHAMBER', 'name' => 'Drainage Chambers & Manholes'],
                    ],
                ],

                [
                    'code' => 'SAN',
                    'name' => 'Sanitary Works',
                    'activity_division' => 'PLUMBING SYSTEM',
                    'sort_order' => 100,
                    'children' => [
                        ['code' => 'SAN-WC', 'name' => 'Water Closets & Cisterns'],
                        ['code' => 'SAN-BASIN', 'name' => 'Wash Basins'],
                        ['code' => 'SAN-FAUCET', 'name' => 'Faucets & Mixers'],
                        ['code' => 'SAN-SHOWER', 'name' => 'Showers & Bath Fittings'],
                        ['code' => 'SAN-SINK', 'name' => 'Sinks'],
                        ['code' => 'SAN-TRAP', 'name' => 'Floor Traps & Drain Accessories'],
                        ['code' => 'SAN-ACCESS', 'name' => 'Sanitary Accessories'],
                    ],
                ],

                [
                    'code' => 'ELE',
                    'name' => 'Electrical Works',
                    'activity_division' => 'ELECTRICAL SYSTEM',
                    'sort_order' => 110,
                    'children' => [
                        ['code' => 'ELE-CONDUIT', 'name' => 'Conduiting & Boxes'],
                        ['code' => 'ELE-WIRING', 'name' => 'Internal Wiring'],
                        ['code' => 'ELE-CABLE', 'name' => 'Power & Control Cables'],
                        ['code' => 'ELE-TRAY', 'name' => 'Cable Trays & Supports'],
                        ['code' => 'ELE-DB', 'name' => 'Distribution Boards & Panels'],
                        ['code' => 'ELE-PROTECT', 'name' => 'MCB, MCCB & Protection Devices'],
                        ['code' => 'ELE-SWITCH', 'name' => 'Switches & Sockets'],
                        ['code' => 'ELE-LIGHT', 'name' => 'Lighting Fixtures'],
                        ['code' => 'ELE-FAN', 'name' => 'Fans & Ventilation Fixtures'],
                        ['code' => 'ELE-EARTH', 'name' => 'Earthing & Lightning Protection'],
                    ],
                ],

                [
                    'code' => 'FIRE',
                    'name' => 'Fire Fighting & Life Safety',
                    'activity_division' => null,
                    'sort_order' => 120,
                    'children' => [
                        ['code' => 'FIRE-PIPE', 'name' => 'Fire Fighting Piping'],
                        ['code' => 'FIRE-HYDRANT', 'name' => 'Hydrants & Hose Reels'],
                        ['code' => 'FIRE-SPRINK', 'name' => 'Sprinkler System'],
                        ['code' => 'FIRE-VALVE', 'name' => 'Fire Valves & Accessories'],
                        ['code' => 'FIRE-PUMP', 'name' => 'Fire Pumps'],
                        ['code' => 'FIRE-EXT', 'name' => 'Fire Extinguishers'],
                        ['code' => 'FIRE-ALARM', 'name' => 'Fire Alarm System'],
                        ['code' => 'FIRE-DETECT', 'name' => 'Detection & Life Safety Devices'],
                    ],
                ],

                [
                    'code' => 'HVAC',
                    'name' => 'HVAC Works',
                    'activity_division' => 'HVAC SYSTEM',
                    'sort_order' => 130,
                    'children' => [
                        ['code' => 'HVAC-DUCT', 'name' => 'Ducting'],
                        ['code' => 'HVAC-INSUL', 'name' => 'HVAC Insulation'],
                        ['code' => 'HVAC-COPPER', 'name' => 'Refrigerant Copper Piping'],
                        ['code' => 'HVAC-DRAIN', 'name' => 'Condensate Drain Piping'],
                        ['code' => 'HVAC-GRILLE', 'name' => 'Grilles, Diffusers & Dampers'],
                        ['code' => 'HVAC-EQUIP', 'name' => 'HVAC Equipment'],
                        ['code' => 'HVAC-SUPPORT', 'name' => 'HVAC Supports & Accessories'],
                    ],
                ],

                [
                    'code' => 'FLOOR',
                    'name' => 'Flooring & Stone Works',
                    'activity_division' => 'FLOORING',
                    'sort_order' => 140,
                    'children' => [
                        ['code' => 'FLOOR-SCREED', 'name' => 'Floor Screed & Levelling'],
                        ['code' => 'FLOOR-TILE', 'name' => 'Floor Tiles'],
                        ['code' => 'FLOOR-WALLTILE', 'name' => 'Wall Tiles'],
                        ['code' => 'FLOOR-MARBLE', 'name' => 'Marble Works'],
                        ['code' => 'FLOOR-GRANITE', 'name' => 'Granite Works'],
                        ['code' => 'FLOOR-STONE', 'name' => 'Natural Stone Works'],
                        ['code' => 'FLOOR-ADH', 'name' => 'Tile Adhesive & Grouting'],
                        ['code' => 'FLOOR-SKIRT', 'name' => 'Skirting & Dado'],
                    ],
                ],

                [
                    'code' => 'DOOR',
                    'name' => 'Doors, Windows & Glazing',
                    'activity_division' => 'DOORS & WINDOWS',
                    'sort_order' => 150,
                    'children' => [
                        ['code' => 'DOOR-FRAME', 'name' => 'Door Frames'],
                        ['code' => 'DOOR-SHUTTER', 'name' => 'Door Shutters'],
                        ['code' => 'DOOR-FIRE', 'name' => 'Fire Rated Doors'],
                        ['code' => 'DOOR-WOOD', 'name' => 'Wooden Doors'],
                        ['code' => 'DOOR-ALU', 'name' => 'Aluminium Windows & Doors'],
                        ['code' => 'DOOR-UPVC', 'name' => 'UPVC Windows & Doors'],
                        ['code' => 'DOOR-GLASS', 'name' => 'Glass & Glazing'],
                        ['code' => 'DOOR-HARD', 'name' => 'Door & Window Hardware'],
                    ],
                ],

                [
                    'code' => 'CEIL',
                    'name' => 'False Ceiling & Drywall',
                    'activity_division' => null,
                    'sort_order' => 160,
                    'children' => [
                        ['code' => 'CEIL-GYPSUM', 'name' => 'Gypsum Board Ceiling'],
                        ['code' => 'CEIL-GRID', 'name' => 'Grid Ceiling'],
                        ['code' => 'CEIL-DRYWALL', 'name' => 'Drywall Partitions'],
                        ['code' => 'CEIL-FRAME', 'name' => 'Ceiling & Partition Framing'],
                        ['code' => 'CEIL-JOINT', 'name' => 'Jointing & Finishing'],
                        ['code' => 'CEIL-ACCESS', 'name' => 'Ceiling Accessories'],
                    ],
                ],

                [
                    'code' => 'PAINT',
                    'name' => 'Painting & Finishing',
                    'activity_division' => null,
                    'sort_order' => 170,
                    'children' => [
                        ['code' => 'PAINT-PUTTY', 'name' => 'Wall Putty'],
                        ['code' => 'PAINT-PRIMER', 'name' => 'Primer'],
                        ['code' => 'PAINT-INT', 'name' => 'Internal Painting'],
                        ['code' => 'PAINT-EXT', 'name' => 'External Painting'],
                        ['code' => 'PAINT-TEXTURE', 'name' => 'Texture & Decorative Coatings'],
                        ['code' => 'PAINT-METAL', 'name' => 'Metal Painting'],
                        ['code' => 'PAINT-WOOD', 'name' => 'Wood Polish & Coatings'],
                        ['code' => 'PAINT-TOUCH', 'name' => 'Touch-Up & Final Finishing'],
                    ],
                ],

                [
                    'code' => 'ROOF',
                    'name' => 'Roofing & External Envelope',
                    'activity_division' => null,
                    'sort_order' => 180,
                    'children' => [
                        ['code' => 'ROOF-SHEET', 'name' => 'Roofing Sheets'],
                        ['code' => 'ROOF-INSUL', 'name' => 'Roof Insulation'],
                        ['code' => 'ROOF-FLASH', 'name' => 'Flashing & Accessories'],
                        ['code' => 'ROOF-GUTTER', 'name' => 'Gutters & Rainwater Accessories'],
                        ['code' => 'ROOF-SEAL', 'name' => 'Roof Sealants & Fasteners'],
                    ],
                ],

                [
                    'code' => 'EXT',
                    'name' => 'External Development',
                    'activity_division' => null,
                    'sort_order' => 190,
                    'children' => [
                        ['code' => 'EXT-ROAD', 'name' => 'Internal Roads'],
                        ['code' => 'EXT-PAVER', 'name' => 'Pavers & Walkways'],
                        ['code' => 'EXT-KERB', 'name' => 'Kerbs & Edging'],
                        ['code' => 'EXT-DRAIN', 'name' => 'External Drainage'],
                        ['code' => 'EXT-COMPOUND', 'name' => 'Compound Wall'],
                        ['code' => 'EXT-GATE', 'name' => 'Gates & Fencing'],
                        ['code' => 'EXT-LAND', 'name' => 'Landscaping'],
                        ['code' => 'EXT-IRRIG', 'name' => 'Landscape Irrigation'],
                    ],
                ],

                [
                    'code' => 'CONS',
                    'name' => 'Site Consumables & Hardware',
                    'activity_division' => 'SITE CONSUMABLES',
                    'sort_order' => 200,
                    'children' => [
                        ['code' => 'CONS-FAST', 'name' => 'Fasteners & Anchors'],
                        ['code' => 'CONS-CUT', 'name' => 'Cutting & Grinding Consumables'],
                        ['code' => 'CONS-DRILL', 'name' => 'Drilling Consumables'],
                        ['code' => 'CONS-TAPE', 'name' => 'Tapes & Adhesives'],
                        ['code' => 'CONS-SEAL', 'name' => 'Sealants'],
                        ['code' => 'CONS-WELD', 'name' => 'Welding Consumables'],
                        ['code' => 'CONS-GENERAL', 'name' => 'General Site Consumables'],
                    ],
                ],

                [
                    'code' => 'TEMP',
                    'name' => 'Formwork, Scaffolding & Temporary Works',
                    'activity_division' => 'SCAFFOLDING & TEMPORARY WORKS',
                    'sort_order' => 210,
                    'children' => [
                        ['code' => 'TEMP-PLY', 'name' => 'Shuttering Plywood'],
                        ['code' => 'TEMP-PLATE', 'name' => 'Shuttering Plates'],
                        ['code' => 'TEMP-PROP', 'name' => 'Props & Supports'],
                        ['code' => 'TEMP-SCAF', 'name' => 'Scaffolding'],
                        ['code' => 'TEMP-ACCESS', 'name' => 'Formwork & Scaffolding Accessories'],
                    ],
                ],

                [
                    'code' => 'SAFE',
                    'name' => 'Safety & Site Protection',
                    'activity_division' => 'SAFETY EQUIPMENT',
                    'sort_order' => 220,
                    'children' => [
                        ['code' => 'SAFE-PPE', 'name' => 'Personal Protective Equipment'],
                        ['code' => 'SAFE-BARR', 'name' => 'Barricading & Warning Systems'],
                        ['code' => 'SAFE-NET', 'name' => 'Safety Nets'],
                        ['code' => 'SAFE-FALL', 'name' => 'Fall Protection'],
                        ['code' => 'SAFE-SIGN', 'name' => 'Safety Signage'],
                    ],
                ],

                [
                    'code' => 'TEST',
                    'name' => 'Testing, Commissioning & Handover',
                    'activity_division' => 'TESTING & QUALITY',
                    'sort_order' => 230,
                    'children' => [
                        ['code' => 'TEST-CIVIL', 'name' => 'Civil Testing & Quality Control'],
                        ['code' => 'TEST-PLB', 'name' => 'Plumbing Testing'],
                        ['code' => 'TEST-ELE', 'name' => 'Electrical Testing'],
                        ['code' => 'TEST-FIRE', 'name' => 'Fire System Testing'],
                        ['code' => 'TEST-HVAC', 'name' => 'HVAC Testing'],
                        ['code' => 'TEST-SNAG', 'name' => 'Snag Rectification'],
                        ['code' => 'TEST-HAND', 'name' => 'Final Handover & Closeout'],
                    ],
                ],
            ];

            foreach ($groups as $group) {
                $activityDivisionId = null;

                if (!empty($group['activity_division'])) {
                    $activityDivisionId =
                        $activityDivisions[$group['activity_division']] ?? null;
                }

                $parent = ConstructionWorkPackage::updateOrCreate(
                    [
                        'code' => $group['code'],
                    ],
                    [
                        'parent_id' => null,
                        'name' => $group['name'],
                        'activity_division_id' => $activityDivisionId,
                        'sort_order' => $group['sort_order'],
                        'is_active' => true,
                        'remarks' => 'System seeded construction work group.',
                    ]
                );

                foreach ($group['children'] as $index => $child) {
                    ConstructionWorkPackage::updateOrCreate(
                        [
                            'code' => $child['code'],
                        ],
                        [
                            'parent_id' => $parent->id,
                            'name' => $child['name'],
                            'activity_division_id' => $activityDivisionId,
                            'sort_order' => ($index + 1) * 10,
                            'is_active' => true,
                            'remarks' => 'System seeded construction work package.',
                        ]
                    );
                }
            }
        });
    }
}