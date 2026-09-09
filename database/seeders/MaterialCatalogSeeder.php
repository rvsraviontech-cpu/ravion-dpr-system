<?php

namespace Database\Seeders;

use App\Models\MaterialCatalogCategory;
use App\Models\MaterialCatalogSubcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $catalog = [
                [
                    'code' => 'CAT-000',
                    'name' => 'Site Mobilization & Temporary Works',
                    'sort_order' => 0,
                    'subcategories' => [
                        [
                            'code' => 'C000-S01',
                            'name' => 'Site barricading & boundary control',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C000-S02',
                            'name' => 'Site office / labour shed / temporary facilities',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C000-S03',
                            'name' => 'Temporary electrical / water / utilities',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C000-S04',
                            'name' => 'Temporary protection & storage',
                            'sort_order' => 40,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-001',
                    'name' => 'Survey & Setting Out',
                    'sort_order' => 10,
                    'subcategories' => [
                        [
                            'code' => 'C001-S01',
                            'name' => 'Marking materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C001-S02',
                            'name' => 'Survey consumables / accessories',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-002',
                    'name' => 'Earthwork, Filling & Soil Works',
                    'sort_order' => 20,
                    'subcategories' => [
                        [
                            'code' => 'C002-S01',
                            'name' => 'Excavation / filling materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C002-S02',
                            'name' => 'Ground protection / stabilization',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-003',
                    'name' => 'Anti-Termite & Soil Treatment',
                    'sort_order' => 30,
                    'subcategories' => [
                        [
                            'code' => 'C003-S01',
                            'name' => 'Anti-termite chemicals',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C003-S02',
                            'name' => 'Application consumables',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-004',
                    'name' => 'Cement & Binding Materials',
                    'sort_order' => 40,
                    'subcategories' => [
                        [
                            'code' => 'C004-S01',
                            'name' => 'Cement',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C004-S02',
                            'name' => 'Grouts / mortars / repair materials',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C004-S03',
                            'name' => 'Chemicals / additives',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-005',
                    'name' => 'Sand, Aggregates & Filling Materials',
                    'sort_order' => 50,
                    'subcategories' => [
                        [
                            'code' => 'C005-S01',
                            'name' => 'Sand',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C005-S02',
                            'name' => 'Aggregates',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C005-S03',
                            'name' => 'Filling / base materials',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-006',
                    'name' => 'RCC Concrete Materials',
                    'sort_order' => 60,
                    'subcategories' => [
                        [
                            'code' => 'C006-S01',
                            'name' => 'RMC / concrete grades',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C006-S02',
                            'name' => 'Concrete chemicals / joint materials',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C006-S03',
                            'name' => 'Concrete repair / anchoring',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-007',
                    'name' => 'Reinforcement Steel',
                    'sort_order' => 70,
                    'subcategories' => [
                        [
                            'code' => 'C007-S01',
                            'name' => 'TMT steel',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C007-S02',
                            'name' => 'Reinforcement accessories',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C007-S03',
                            'name' => 'Mesh / misc steel',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-008',
                    'name' => 'PT / Post-Tensioning Materials',
                    'sort_order' => 80,
                    'subcategories' => [
                        [
                            'code' => 'C008-S01',
                            'name' => 'PT strand / duct / anchorage',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C008-S02',
                            'name' => 'PT grouting / accessories',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C008-S03',
                            'name' => 'PT support / forming / safety',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-009',
                    'name' => 'Structural Steel / Composite / PEB Materials',
                    'sort_order' => 90,
                    'subcategories' => [
                        [
                            'code' => 'C009-S01',
                            'name' => 'Main steel members',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C009-S02',
                            'name' => 'Bolts / fasteners / composite items',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C009-S03',
                            'name' => 'PEB / roofing / cladding',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C009-S04',
                            'name' => 'Steel protection / consumables',
                            'sort_order' => 40,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-010',
                    'name' => 'Shuttering, Formwork & Scaffolding',
                    'sort_order' => 100,
                    'subcategories' => [
                        [
                            'code' => 'C010-S01',
                            'name' => 'Formwork boards / panels',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C010-S02',
                            'name' => 'Formwork supports',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C010-S03',
                            'name' => 'Tie / clamp accessories',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C010-S04',
                            'name' => 'Scaffolding',
                            'sort_order' => 40,
                        ],
                        [
                            'code' => 'C010-S05',
                            'name' => 'Formwork consumables',
                            'sort_order' => 50,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-011',
                    'name' => 'Masonry Materials',
                    'sort_order' => 110,
                    'subcategories' => [
                        [
                            'code' => 'C011-S01',
                            'name' => 'Bricks / blocks',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C011-S02',
                            'name' => 'Masonry mortar / adhesives',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C011-S03',
                            'name' => 'Masonry accessories',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-012',
                    'name' => 'Plastering Materials',
                    'sort_order' => 120,
                    'subcategories' => [
                        [
                            'code' => 'C012-S01',
                            'name' => 'Plaster base materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C012-S02',
                            'name' => 'Mesh / beads / profiles',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C012-S03',
                            'name' => 'Plastering consumables',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-013',
                    'name' => 'Waterproofing Materials',
                    'sort_order' => 130,
                    'subcategories' => [
                        [
                            'code' => 'C013-S01',
                            'name' => 'Coatings / membranes',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C013-S02',
                            'name' => 'Waterproofing accessories',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C013-S03',
                            'name' => 'Grouting / repair / testing',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-014',
                    'name' => 'Roofing & Roof Tile Materials',
                    'sort_order' => 140,
                    'subcategories' => [
                        [
                            'code' => 'C014-S01',
                            'name' => 'Roof tiles',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C014-S02',
                            'name' => 'Roofing fixing / support',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C014-S03',
                            'name' => 'Roof drainage / sheets / protection',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-015',
                    'name' => 'Plumbing Pipes',
                    'sort_order' => 150,
                    'subcategories' => [
                        [
                            'code' => 'C015-S01',
                            'name' => 'CPVC pipes',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C015-S02',
                            'name' => 'UPVC / PVC / SWR / PPR / GI / HDPE / RCC pipes',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-016',
                    'name' => 'Plumbing Fittings',
                    'sort_order' => 160,
                    'subcategories' => [
                        [
                            'code' => 'C016-S01',
                            'name' => 'CPVC fittings',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C016-S02',
                            'name' => 'UPVC / PVC / SWR fittings',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C016-S03',
                            'name' => 'PPR fittings',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-017',
                    'name' => 'Plumbing Traps, Drains & Accessories',
                    'sort_order' => 170,
                    'subcategories' => [
                        [
                            'code' => 'C017-S01',
                            'name' => 'Traps',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C017-S02',
                            'name' => 'Drains / covers / chambers',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C017-S03',
                            'name' => 'Drainage accessories',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-018',
                    'name' => 'Plumbing Valves & Control Items',
                    'sort_order' => 180,
                    'subcategories' => [
                        [
                            'code' => 'C018-S01',
                            'name' => 'Valves',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C018-S02',
                            'name' => 'Control / measurement items',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-019',
                    'name' => 'Sanitary Fixtures',
                    'sort_order' => 190,
                    'subcategories' => [
                        [
                            'code' => 'C019-S01',
                            'name' => 'WC / cistern / urinal',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C019-S02',
                            'name' => 'Basins / sinks / bath fixtures',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-020',
                    'name' => 'CP Fittings & Bathroom Accessories',
                    'sort_order' => 200,
                    'subcategories' => [
                        [
                            'code' => 'C020-S01',
                            'name' => 'Angle cocks / taps / mixers',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C020-S02',
                            'name' => 'Brass nipples / extensions / adaptors',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C020-S03',
                            'name' => 'Shower / diverter / mixer accessories',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C020-S04',
                            'name' => 'Health faucet / hose / connection items',
                            'sort_order' => 40,
                        ],
                        [
                            'code' => 'C020-S05',
                            'name' => 'Basin / sink / waste / accessories',
                            'sort_order' => 50,
                        ],
                        [
                            'code' => 'C020-S06',
                            'name' => 'Bathroom accessories',
                            'sort_order' => 60,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-021',
                    'name' => 'Electrical Conduits, Boxes & Accessories',
                    'sort_order' => 210,
                    'subcategories' => [
                        [
                            'code' => 'C021-S01',
                            'name' => 'Conduits',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C021-S02',
                            'name' => 'Boxes',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-022',
                    'name' => 'Electrical Wires & Cables',
                    'sort_order' => 220,
                    'subcategories' => [
                        [
                            'code' => 'C022-S01',
                            'name' => 'Power wires',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C022-S02',
                            'name' => 'Cables',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C022-S03',
                            'name' => 'Low voltage / data wires',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C022-S04',
                            'name' => 'Cable accessories',
                            'sort_order' => 40,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-023',
                    'name' => 'Electrical DB, Switchgear & Earthing',
                    'sort_order' => 230,
                    'subcategories' => [
                        [
                            'code' => 'C023-S01',
                            'name' => 'DB / switchgear',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C023-S02',
                            'name' => 'Earthing / lightning',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C023-S03',
                            'name' => 'Cable tray',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-024',
                    'name' => 'Electrical Fixtures',
                    'sort_order' => 240,
                    'subcategories' => [
                        [
                            'code' => 'C024-S01',
                            'name' => 'Switches / sockets / controls',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C024-S02',
                            'name' => 'Lights / fans',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-025',
                    'name' => 'ELV / CCTV / Automation / Data',
                    'sort_order' => 250,
                    'subcategories' => [
                        [
                            'code' => 'C025-S01',
                            'name' => 'CCTV / security',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C025-S02',
                            'name' => 'Data / network',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C025-S03',
                            'name' => 'Automation / access',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-026',
                    'name' => 'Fire Fighting Materials',
                    'sort_order' => 260,
                    'subcategories' => [
                        [
                            'code' => 'C026-S01',
                            'name' => 'Fire pipes / valves / hydrant',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C026-S02',
                            'name' => 'Fire alarm / safety',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-027',
                    'name' => 'HVAC Materials',
                    'sort_order' => 270,
                    'subcategories' => [
                        [
                            'code' => 'C027-S01',
                            'name' => 'AC / VRF / refrigerant',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C027-S02',
                            'name' => 'Ducting / accessories',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-028',
                    'name' => 'Flooring Materials',
                    'sort_order' => 280,
                    'subcategories' => [
                        [
                            'code' => 'C028-S01',
                            'name' => 'Standard tiles',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C028-S02',
                            'name' => 'Large format / luxury flooring',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C028-S03',
                            'name' => 'Flooring accessories',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-029',
                    'name' => 'Wall Tiling Materials',
                    'sort_order' => 290,
                    'subcategories' => [
                        [
                            'code' => 'C029-S01',
                            'name' => 'Wall tiles',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C029-S02',
                            'name' => 'Wall tile accessories',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-030',
                    'name' => 'Granite, Marble, Quartz & Stone',
                    'sort_order' => 300,
                    'subcategories' => [
                        [
                            'code' => 'C030-S01',
                            'name' => 'Italian / imported / Indian marble',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C030-S02',
                            'name' => 'Granite / quartz / stone',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C030-S03',
                            'name' => 'Stone accessories',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-031',
                    'name' => 'Doors, Frames & Hardware',
                    'sort_order' => 310,
                    'subcategories' => [
                        [
                            'code' => 'C031-S01',
                            'name' => 'Doors / frames',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C031-S02',
                            'name' => 'Door hardware',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-032',
                    'name' => 'UPVC, Aluminium & Glass',
                    'sort_order' => 320,
                    'subcategories' => [
                        [
                            'code' => 'C032-S01',
                            'name' => 'UPVC / aluminium',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C032-S02',
                            'name' => 'Glass / accessories',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-033',
                    'name' => 'Railings, Grills, Gates & Fabrication',
                    'sort_order' => 330,
                    'subcategories' => [
                        [
                            'code' => 'C033-S01',
                            'name' => 'Grills / railings',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C033-S02',
                            'name' => 'Gates / fabrication',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C033-S03',
                            'name' => 'Fabrication consumables',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-034',
                    'name' => 'Painting & Surface Finishing',
                    'sort_order' => 340,
                    'subcategories' => [
                        [
                            'code' => 'C034-S01',
                            'name' => 'Standard interior paints',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C034-S02',
                            'name' => 'Standard exterior paints',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C034-S03',
                            'name' => 'Primers / sealers / base coats',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C034-S04',
                            'name' => 'Putty / surface preparation',
                            'sort_order' => 40,
                        ],
                        [
                            'code' => 'C034-S05',
                            'name' => 'Premium decorative texture paints',
                            'sort_order' => 50,
                        ],
                        [
                            'code' => 'C034-S06',
                            'name' => 'Italian / imported decorative wall finishes',
                            'sort_order' => 60,
                        ],
                        [
                            'code' => 'C034-S07',
                            'name' => 'Metallic / pearl / luxury effect paints',
                            'sort_order' => 70,
                        ],
                        [
                            'code' => 'C034-S08',
                            'name' => 'Concrete / cement / microcement finishes',
                            'sort_order' => 80,
                        ],
                        [
                            'code' => 'C034-S09',
                            'name' => 'Stone / marble / natural effect wall finishes',
                            'sort_order' => 90,
                        ],
                        [
                            'code' => 'C034-S10',
                            'name' => 'Façade / exterior premium coatings',
                            'sort_order' => 100,
                        ],
                        [
                            'code' => 'C034-S11',
                            'name' => 'Wood / metal / special surface paints',
                            'sort_order' => 110,
                        ],
                        [
                            'code' => 'C034-S12',
                            'name' => 'Commercial / special performance coatings',
                            'sort_order' => 120,
                        ],
                        [
                            'code' => 'C034-S13',
                            'name' => 'Decorative tools / application consumables',
                            'sort_order' => 130,
                        ],
                        [
                            'code' => 'C034-S14',
                            'name' => 'Solvents / thinners / additives / sealers',
                            'sort_order' => 140,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-035',
                    'name' => 'False Ceiling Materials',
                    'sort_order' => 350,
                    'subcategories' => [
                        [
                            'code' => 'C035-S01',
                            'name' => 'Boards / panels',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C035-S02',
                            'name' => 'Channels / frame / accessories',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-036',
                    'name' => 'Carpentry & Interior Base Materials',
                    'sort_order' => 360,
                    'subcategories' => [
                        [
                            'code' => 'C036-S01',
                            'name' => 'Boards',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C036-S02',
                            'name' => 'Finishes / adhesives / hardware',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-037',
                    'name' => 'Modular Kitchen / Wardrobe / Interior Hardware',
                    'sort_order' => 370,
                    'subcategories' => [
                        [
                            'code' => 'C037-S01',
                            'name' => 'Kitchen hardware',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C037-S02',
                            'name' => 'Wardrobe / cabinet hardware',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-038',
                    'name' => 'External Development Materials',
                    'sort_order' => 380,
                    'subcategories' => [
                        [
                            'code' => 'C038-S01',
                            'name' => 'Driveway / road / drainage',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C038-S02',
                            'name' => 'Compound / external finishes',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-039',
                    'name' => 'Landscape Materials',
                    'sort_order' => 390,
                    'subcategories' => [
                        [
                            'code' => 'C039-S01',
                            'name' => 'Soil / plants',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C039-S02',
                            'name' => 'Irrigation / decorative',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-040',
                    'name' => 'Terrace, Roof Drainage & Rainwater Materials',
                    'sort_order' => 400,
                    'subcategories' => [
                        [
                            'code' => 'C040-S01',
                            'name' => 'Terrace / roof',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C040-S02',
                            'name' => 'Rainwater / harvesting',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-041',
                    'name' => 'Lift / Elevator Civil Interface Materials',
                    'sort_order' => 410,
                    'subcategories' => [
                        [
                            'code' => 'C041-S01',
                            'name' => 'Lift pit / shaft',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C041-S02',
                            'name' => 'Machine room / interface',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-042',
                    'name' => 'Safety Materials',
                    'sort_order' => 420,
                    'subcategories' => [
                        [
                            'code' => 'C042-S01',
                            'name' => 'PPE',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C042-S02',
                            'name' => 'Site safety',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-043',
                    'name' => 'Testing & Quality Materials',
                    'sort_order' => 430,
                    'subcategories' => [
                        [
                            'code' => 'C043-S01',
                            'name' => 'Civil testing',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C043-S02',
                            'name' => 'MEP / finishing testing',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-044',
                    'name' => 'Consumables',
                    'sort_order' => 440,
                    'subcategories' => [
                        [
                            'code' => 'C044-S01',
                            'name' => 'General consumables',
                            'sort_order' => 10,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-045',
                    'name' => 'Tools & Small Equipment',
                    'sort_order' => 450,
                    'subcategories' => [
                        [
                            'code' => 'C045-S01',
                            'name' => 'Hand tools',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C045-S02',
                            'name' => 'Small equipment',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-046',
                    'name' => 'Machinery / Rental Equipment',
                    'sort_order' => 460,
                    'subcategories' => [
                        [
                            'code' => 'C046-S01',
                            'name' => 'Earthwork / concrete / lifting',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C046-S02',
                            'name' => 'Construction equipment',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-047',
                    'name' => 'Cleaning & Housekeeping',
                    'sort_order' => 470,
                    'subcategories' => [
                        [
                            'code' => 'C047-S01',
                            'name' => 'Cleaning materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C047-S02',
                            'name' => 'Protection / disposal',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-048',
                    'name' => 'Handover & Snag Rectification Materials',
                    'sort_order' => 480,
                    'subcategories' => [
                        [
                            'code' => 'C048-S01',
                            'name' => 'Handover protection / identification',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C048-S02',
                            'name' => 'Touch-up / rectification',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-049',
                    'name' => 'Miscellaneous Controlled Items',
                    'sort_order' => 490,
                    'subcategories' => [
                        [
                            'code' => 'C049-S01',
                            'name' => 'Client / designer / imported items',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C049-S02',
                            'name' => 'Custom items',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-050',
                    'name' => 'Elevation Cladding & Façade Materials',
                    'sort_order' => 500,
                    'subcategories' => [
                        [
                            'code' => 'C050-S01',
                            'name' => 'ACP / aluminium composite panel',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C050-S02',
                            'name' => 'HPL / exterior laminate cladding',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C050-S03',
                            'name' => 'Stone / ceramic / porcelain cladding',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C050-S04',
                            'name' => 'Terracotta / GRC / GFRC / UHPC',
                            'sort_order' => 40,
                        ],
                        [
                            'code' => 'C050-S05',
                            'name' => 'Metal / WPC / boards / glass façade',
                            'sort_order' => 50,
                        ],
                        [
                            'code' => 'C050-S06',
                            'name' => 'Elevation decorative accessories',
                            'sort_order' => 60,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-051',
                    'name' => 'Façade Sub-Frame, Fixing & Sealant Materials',
                    'sort_order' => 510,
                    'subcategories' => [
                        [
                            'code' => 'C051-S01',
                            'name' => 'Sub-frame materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C051-S02',
                            'name' => 'Fasteners / anchors',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C051-S03',
                            'name' => 'Sealants / tapes / joint materials',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-052',
                    'name' => 'Premium Interior Wall Panelling',
                    'sort_order' => 520,
                    'subcategories' => [
                        [
                            'code' => 'C052-S01',
                            'name' => 'Wall panelling base materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C052-S02',
                            'name' => 'Decorative wall panels',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C052-S03',
                            'name' => 'Premium wall finish surfaces',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-053',
                    'name' => 'Interior Mouldings, Profiles & Decorative Trims',
                    'sort_order' => 530,
                    'subcategories' => [
                        [
                            'code' => 'C053-S01',
                            'name' => 'Wall mouldings',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C053-S02',
                            'name' => 'Ceiling mouldings',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C053-S03',
                            'name' => 'Floor / door / furniture profiles',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-054',
                    'name' => 'Laminates, Acrylics, Veneers & Surface Finishes',
                    'sort_order' => 540,
                    'subcategories' => [
                        [
                            'code' => 'C054-S01',
                            'name' => 'Laminates',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C054-S02',
                            'name' => 'Acrylic laminates / sheets',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C054-S03',
                            'name' => 'Veneers',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C054-S04',
                            'name' => 'PU / Duco / lacquer finishes',
                            'sort_order' => 40,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-055',
                    'name' => 'Decorative Boards & Designer Panels',
                    'sort_order' => 550,
                    'subcategories' => [
                        [
                            'code' => 'C055-S01',
                            'name' => 'Boards',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C055-S02',
                            'name' => 'Designer decorative panels',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-056',
                    'name' => 'Modular Kitchen Materials',
                    'sort_order' => 560,
                    'subcategories' => [
                        [
                            'code' => 'C056-S01',
                            'name' => 'Kitchen carcass / shutter materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C056-S02',
                            'name' => 'Kitchen countertop / backsplash',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C056-S03',
                            'name' => 'Kitchen hardware',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C056-S04',
                            'name' => 'Appliance coordination items',
                            'sort_order' => 40,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-057',
                    'name' => 'Wardrobe & Storage Materials',
                    'sort_order' => 570,
                    'subcategories' => [
                        [
                            'code' => 'C057-S01',
                            'name' => 'Wardrobe base materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C057-S02',
                            'name' => 'Wardrobe hardware',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-058',
                    'name' => 'Interior Hardware, Handles, Locks & Accessories',
                    'sort_order' => 580,
                    'subcategories' => [
                        [
                            'code' => 'C058-S01',
                            'name' => 'Handles',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C058-S02',
                            'name' => 'Locks',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C058-S03',
                            'name' => 'Hinges / channels / stays',
                            'sort_order' => 30,
                        ],
                        [
                            'code' => 'C058-S04',
                            'name' => 'Fasteners / fixing accessories',
                            'sort_order' => 40,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-059',
                    'name' => 'Premium Lighting, Chandeliers & Decorative Electrical',
                    'sort_order' => 590,
                    'subcategories' => [
                        [
                            'code' => 'C059-S01',
                            'name' => 'Chandeliers / decorative lights',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C059-S02',
                            'name' => 'Architectural lighting',
                            'sort_order' => 20,
                        ],
                        [
                            'code' => 'C059-S03',
                            'name' => 'Lighting accessories',
                            'sort_order' => 30,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-060',
                    'name' => 'Ceiling Feature Materials',
                    'sort_order' => 600,
                    'subcategories' => [
                        [
                            'code' => 'C060-S01',
                            'name' => 'Designer ceiling materials',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C060-S02',
                            'name' => 'Ceiling accessories',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-061',
                    'name' => 'Glass, Mirror & Metal Interior Features',
                    'sort_order' => 610,
                    'subcategories' => [
                        [
                            'code' => 'C061-S01',
                            'name' => 'Mirrors / glass',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C061-S02',
                            'name' => 'Metal decorative items',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-062',
                    'name' => 'Soft Furnishing & Furnishing Coordination Materials',
                    'sort_order' => 620,
                    'subcategories' => [
                        [
                            'code' => 'C062-S01',
                            'name' => 'Curtains / blinds',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C062-S02',
                            'name' => 'Upholstery / soft furnishing',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-063',
                    'name' => 'Commercial Fit-Out Materials',
                    'sort_order' => 630,
                    'subcategories' => [
                        [
                            'code' => 'C063-S01',
                            'name' => 'Partition systems',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C063-S02',
                            'name' => 'Office / commercial interior items',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-064',
                    'name' => 'HNI / NRI Premium Imported Materials',
                    'sort_order' => 640,
                    'subcategories' => [
                        [
                            'code' => 'C064-S01',
                            'name' => 'High-value imported / designer items',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C064-S02',
                            'name' => 'Mandatory data flags',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-065',
                    'name' => 'Loose Furniture Coordination Items',
                    'sort_order' => 650,
                    'subcategories' => [
                        [
                            'code' => 'C065-S01',
                            'name' => 'Loose furniture',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C065-S02',
                            'name' => 'Decorative loose items',
                            'sort_order' => 20,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-066',
                    'name' => 'Smart Home / Automation Interior Items',
                    'sort_order' => 660,
                    'subcategories' => [
                        [
                            'code' => 'C066-S01',
                            'name' => 'Smart home / automation',
                            'sort_order' => 10,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-067',
                    'name' => 'Decorative Accessories & Final Styling Items',
                    'sort_order' => 670,
                    'subcategories' => [
                        [
                            'code' => 'C067-S01',
                            'name' => 'Styling / decor',
                            'sort_order' => 10,
                        ],
                    ],
                ],
                [
                    'code' => 'CAT-068',
                    'name' => 'Interior Protection, Handover & Snag Materials',
                    'sort_order' => 680,
                    'subcategories' => [
                        [
                            'code' => 'C068-S01',
                            'name' => 'Interior protection',
                            'sort_order' => 10,
                        ],
                        [
                            'code' => 'C068-S02',
                            'name' => 'Snag / touch-up / final cleaning',
                            'sort_order' => 20,
                        ],
                    ],
                ],
            ];

            foreach ($catalog as $categoryData) {
                $category = MaterialCatalogCategory::query()->updateOrCreate(
                    ['code' => $categoryData['code']],
                    [
                        'name' => $categoryData['name'],
                        'sort_order' => $categoryData['sort_order'],
                        'is_active' => true,
                        'remarks' => 'Ravion comprehensive construction material catalogue.',
                    ]
                );

                foreach ($categoryData['subcategories'] as $subcategoryData) {
                    MaterialCatalogSubcategory::query()->updateOrCreate(
                        [
                            'material_catalog_category_id' => $category->id,
                            'code' => $subcategoryData['code'],
                        ],
                        [
                            'name' => $subcategoryData['name'],
                            'sort_order' => $subcategoryData['sort_order'],
                            'is_active' => true,
                            'remarks' => null,
                        ]
                    );
                }
            }
        });
    }
}
