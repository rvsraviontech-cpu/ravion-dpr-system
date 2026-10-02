<?php

namespace Database\Seeders;

use App\Models\WorkActivity;
use App\Models\WorkActivityAlias;
use App\Models\WorkPackage;
use App\Models\WorkSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkExecutionCatalogue0818Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->catalogue() as $packageCode => $packageData) {
                $package = WorkPackage::query()->where('code', $packageCode)->first();

                if (! $package) {
                    throw new RuntimeException(
                        "Work Package [{$packageCode}] was not found. Run WorkExecutionMasterSeeder first."
                    );
                }

                $package->update(['description' => $packageData['description']]);

                foreach ($packageData['sections'] as $sectionIndex => $sectionData) {
                    $section = WorkSection::updateOrCreate(
                        ['code' => $sectionData['code']],
                        [
                            'work_package_id' => $package->id,
                            'name' => $sectionData['name'],
                            'description' => $sectionData['description'] ?? null,
                            'sort_order' => ($sectionIndex + 1) * 10,
                            'is_system' => true,
                            'is_active' => true,
                            'remarks' => null,
                        ]
                    );

                    foreach ($sectionData['activities'] as $activityIndex => $activityData) {
                        $activity = WorkActivity::updateOrCreate(
                            ['code' => $activityData['code']],
                            [
                                'work_package_id' => $package->id,
                                'work_section_id' => $section->id,
                                'legacy_activity_id' => null,
                                'name' => $activityData['name'],
                                'description' => $activityData['description'] ?? null,
                                'default_unit' => $activityData['unit'],
                                'allow_materials' => $activityData['allow_materials'] ?? true,
                                'allow_labour' => $activityData['allow_labour'] ?? true,
                                'allow_equipment' => $activityData['allow_equipment'] ?? true,
                                'allow_photos' => $activityData['allow_photos'] ?? true,
                                'material_calculation_enabled' => false,
                                'is_selectable' => true,
                                'is_system' => true,
                                'is_active' => true,
                                'sort_order' => ($activityIndex + 1) * 10,
                                'remarks' => null,
                            ]
                        );

                        $aliases = array_values(array_unique(array_merge(
                            [$activityData['name']],
                            $activityData['aliases'] ?? []
                        )));

                        foreach ($aliases as $aliasIndex => $alias) {
                            WorkActivityAlias::updateOrCreate(
                                [
                                    'work_activity_id' => $activity->id,
                                    'normalized_alias' => $this->normalizeAlias($alias),
                                ],
                                [
                                    'alias' => $alias,
                                    'sort_order' => ($aliasIndex + 1) * 10,
                                    'is_active' => true,
                                ]
                            );
                        }
                    }
                }
            }
        });
    }

    private function normalizeAlias(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['-', '_', '/', '\\'], ' ', $value);
        $value = preg_replace('/[^\pL\pN\s]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function catalogue(): array
    {
        return [

            'STRUCTURAL_STEEL' => [
                'description' => 'Structural steel fabrication, erection, metal framing and miscellaneous structural metal works.',
                'sections' => [
                    [
                        'code' => '08-01',
                        'name' => 'Structural Steel Fabrication',
                        'activities' => [
                            [
                                'code' => 'STEEL_MEMBER_FAB',
                                'name' => 'Structural Steel Member Fabrication',
                                'unit' => 'Kg',
                                'aliases' => ['steel fabrication', 'structural fabrication', 'beam fabrication', 'column fabrication'],
                            ],
                            [
                                'code' => 'STEEL_PLATE_FAB',
                                'name' => 'Steel Plate / Base Plate Fabrication',
                                'unit' => 'Kg',
                                'aliases' => ['base plate fabrication', 'steel plate fabrication'],
                            ],
                            [
                                'code' => 'STEEL_DRILL_CUT',
                                'name' => 'Steel Cutting / Drilling',
                                'unit' => 'Kg',
                                'aliases' => ['steel cutting', 'steel drilling', 'member cutting'],
                            ],
                            [
                                'code' => 'STEEL_WELD',
                                'name' => 'Structural Steel Welding',
                                'unit' => 'Rm',
                                'aliases' => ['steel welding', 'structural welding', 'site welding'],
                            ],
                            [
                                'code' => 'STEEL_BOLT',
                                'name' => 'Structural Bolting',
                                'unit' => 'Nos',
                                'aliases' => ['steel bolting', 'bolt fixing', 'structural bolts'],
                            ],
                        ],
                    ],
                    [
                        'code' => '08-02',
                        'name' => 'Structural Steel Erection',
                        'activities' => [
                            [
                                'code' => 'STEEL_COLUMN_ERECT',
                                'name' => 'Steel Column Erection',
                                'unit' => 'Kg',
                                'aliases' => ['steel column erection', 'column erection'],
                            ],
                            [
                                'code' => 'STEEL_BEAM_ERECT',
                                'name' => 'Steel Beam / Girder Erection',
                                'unit' => 'Kg',
                                'aliases' => ['steel beam erection', 'girder erection'],
                            ],
                            [
                                'code' => 'STEEL_TRUSS_ERECT',
                                'name' => 'Steel Truss Erection',
                                'unit' => 'Kg',
                                'aliases' => ['truss erection', 'roof truss erection'],
                            ],
                            [
                                'code' => 'STEEL_BRACING_ERECT',
                                'name' => 'Steel Bracing Erection',
                                'unit' => 'Kg',
                                'aliases' => ['bracing erection', 'steel bracing'],
                            ],
                            [
                                'code' => 'STEEL_DECK_FIX',
                                'name' => 'Metal Deck / Decking Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['metal deck', 'deck sheet fixing', 'steel decking'],
                            ],
                        ],
                    ],
                    [
                        'code' => '08-03',
                        'name' => 'Metal Works',
                        'activities' => [
                            [
                                'code' => 'STEEL_STAIR',
                                'name' => 'MS Staircase Fabrication & Installation',
                                'unit' => 'Kg',
                                'aliases' => ['ms staircase', 'steel stair', 'metal staircase'],
                            ],
                            [
                                'code' => 'STEEL_RAILING',
                                'name' => 'MS / Steel Railing Installation',
                                'unit' => 'Rm',
                                'aliases' => ['ms railing', 'steel railing', 'handrail fixing'],
                            ],
                            [
                                'code' => 'STEEL_GRATING',
                                'name' => 'Steel Grating Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['steel grating', 'grating fixing'],
                            ],
                            [
                                'code' => 'STEEL_LADDER',
                                'name' => 'Steel Ladder Installation',
                                'unit' => 'Nos',
                                'aliases' => ['steel ladder', 'ms ladder'],
                            ],
                            [
                                'code' => 'STEEL_MISC',
                                'name' => 'Miscellaneous Steel Supports / Frames',
                                'unit' => 'Kg',
                                'aliases' => ['steel supports', 'ms supports', 'metal frame fabrication'],
                            ],
                        ],
                    ],
                    [
                        'code' => '08-04',
                        'name' => 'Protection & Finishing',
                        'activities' => [
                            [
                                'code' => 'STEEL_SURFACE_PREP',
                                'name' => 'Structural Steel Surface Preparation',
                                'unit' => 'Sq.m',
                                'aliases' => ['steel surface preparation', 'steel cleaning', 'rust cleaning'],
                            ],
                            [
                                'code' => 'STEEL_PRIMER',
                                'name' => 'Structural Steel Primer Coating',
                                'unit' => 'Sq.m',
                                'aliases' => ['steel primer', 'red oxide primer', 'metal primer'],
                            ],
                            [
                                'code' => 'STEEL_FIREPROOF',
                                'name' => 'Structural Steel Fireproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['steel fireproofing', 'fire protection coating', 'intumescent coating'],
                            ],
                        ],
                    ],
                ],
            ],
            'MASONRY' => [
                'description' => 'Brick, block, partition and associated masonry execution works.',
                'sections' => [
                    [
                        'code' => '09-01',
                        'name' => 'Brick Masonry',
                        'activities' => [
                            [
                                'code' => 'MAS_CLAY_BRICK',
                                'name' => 'Clay Brick Masonry',
                                'unit' => 'Cum',
                                'aliases' => ['brick work', 'brickwork', 'red brick masonry', 'clay brick work'],
                            ],
                            [
                                'code' => 'MAS_FLYASH_BRICK',
                                'name' => 'Fly Ash Brick Masonry',
                                'unit' => 'Cum',
                                'aliases' => ['flyash brickwork', 'fly ash brick work'],
                            ],
                            [
                                'code' => 'MAS_BRICK_PARTITION',
                                'name' => 'Brick Partition Wall',
                                'unit' => 'Sq.m',
                                'aliases' => ['brick partition', 'partition brickwork'],
                            ],
                        ],
                    ],
                    [
                        'code' => '09-02',
                        'name' => 'Block Masonry',
                        'activities' => [
                            [
                                'code' => 'MAS_AAC_BLOCK',
                                'name' => 'AAC Block Masonry',
                                'unit' => 'Cum',
                                'aliases' => ['aac block work', 'aac masonry', 'aerated block masonry'],
                            ],
                            [
                                'code' => 'MAS_CONCRETE_BLOCK',
                                'name' => 'Concrete Block Masonry',
                                'unit' => 'Cum',
                                'aliases' => ['concrete block work', 'cement block masonry'],
                            ],
                            [
                                'code' => 'MAS_HOLLOW_BLOCK',
                                'name' => 'Hollow Block Masonry',
                                'unit' => 'Cum',
                                'aliases' => ['hollow block work', 'hollow block masonry'],
                            ],
                            [
                                'code' => 'MAS_SOLID_BLOCK',
                                'name' => 'Solid Block Masonry',
                                'unit' => 'Cum',
                                'aliases' => ['solid block work', 'solid block masonry'],
                            ],
                        ],
                    ],
                    [
                        'code' => '09-03',
                        'name' => 'Special Masonry',
                        'activities' => [
                            [
                                'code' => 'MAS_SHAFT_WALL',
                                'name' => 'Masonry Shaft Wall',
                                'unit' => 'Sq.m',
                                'aliases' => ['shaft wall', 'duct wall masonry'],
                            ],
                            [
                                'code' => 'MAS_PARAPET',
                                'name' => 'Masonry Parapet Wall',
                                'unit' => 'Cum',
                                'aliases' => ['parapet brickwork', 'parapet blockwork', 'parapet masonry'],
                            ],
                            [
                                'code' => 'MAS_CURVED',
                                'name' => 'Curved / Special Shape Masonry',
                                'unit' => 'Sq.m',
                                'aliases' => ['curved wall masonry', 'special masonry'],
                            ],
                            [
                                'code' => 'MAS_INFIL',
                                'name' => 'Masonry Infill / Closing Openings',
                                'unit' => 'Sq.m',
                                'aliases' => ['opening closing', 'masonry infill', 'block opening'],
                            ],
                        ],
                    ],
                    [
                        'code' => '09-04',
                        'name' => 'Masonry Support Works',
                        'activities' => [
                            [
                                'code' => 'MAS_DPC',
                                'name' => 'Damp Proof Course Below Masonry',
                                'unit' => 'Sq.m',
                                'aliases' => ['dpc', 'damp proof course'],
                            ],
                            [
                                'code' => 'MAS_MESH',
                                'name' => 'Chicken Mesh / Joint Mesh Fixing',
                                'unit' => 'Sq.m',
                                'aliases' => ['chicken mesh', 'joint mesh', 'block rcc mesh'],
                            ],
                            [
                                'code' => 'MAS_CHASE',
                                'name' => 'Masonry Chasing',
                                'unit' => 'Rm',
                                'aliases' => ['wall chasing', 'brick chasing', 'block chasing'],
                            ],
                            [
                                'code' => 'MAS_OPENING',
                                'name' => 'Masonry Opening / Cutting',
                                'unit' => 'Nos',
                                'aliases' => ['wall opening', 'brick wall cutting', 'block opening cutting'],
                            ],
                            [
                                'code' => 'MAS_REPAIR',
                                'name' => 'Masonry Repair / Rework',
                                'unit' => 'Sq.m',
                                'aliases' => ['brickwork repair', 'blockwork repair', 'masonry rework'],
                            ],
                        ],
                    ],
                ],
            ],
            'PLASTERING' => [
                'description' => 'Internal and external plaster, rendering, screed and surface preparation works.',
                'sections' => [
                    [
                        'code' => '10-01',
                        'name' => 'Internal Plaster',
                        'activities' => [
                            [
                                'code' => 'PLAST_INTERNAL',
                                'name' => 'Internal Wall Plastering',
                                'unit' => 'Sq.m',
                                'aliases' => ['internal plaster', 'inside plaster', 'wall plaster'],
                            ],
                            [
                                'code' => 'PLAST_CEILING',
                                'name' => 'Ceiling Plastering',
                                'unit' => 'Sq.m',
                                'aliases' => ['ceiling plaster', 'soffit plaster'],
                            ],
                            [
                                'code' => 'PLAST_SHAFT',
                                'name' => 'Shaft / Duct Plastering',
                                'unit' => 'Sq.m',
                                'aliases' => ['shaft plaster', 'duct plaster'],
                            ],
                        ],
                    ],
                    [
                        'code' => '10-02',
                        'name' => 'External Plaster & Render',
                        'activities' => [
                            [
                                'code' => 'PLAST_EXTERNAL',
                                'name' => 'External Wall Plastering',
                                'unit' => 'Sq.m',
                                'aliases' => ['external plaster', 'outside plaster', 'facade plaster'],
                            ],
                            [
                                'code' => 'PLAST_RENDER',
                                'name' => 'Cement Rendering',
                                'unit' => 'Sq.m',
                                'aliases' => ['cement render', 'rendering work'],
                            ],
                            [
                                'code' => 'PLAST_TEXTURE_BASE',
                                'name' => 'Texture Base Plaster / Render',
                                'unit' => 'Sq.m',
                                'aliases' => ['texture base coat', 'texture plaster base'],
                            ],
                        ],
                    ],
                    [
                        'code' => '10-03',
                        'name' => 'Surface Preparation',
                        'activities' => [
                            [
                                'code' => 'PLAST_HACKING',
                                'name' => 'RCC Surface Hacking for Plaster',
                                'unit' => 'Sq.m',
                                'aliases' => ['rcc hacking', 'surface hacking', 'plaster hacking'],
                            ],
                            [
                                'code' => 'PLAST_LEVEL_DOT',
                                'name' => 'Plaster Level Dot / Screed Patti',
                                'unit' => 'Sq.m',
                                'aliases' => ['plaster dots', 'level dots', 'screed patti'],
                            ],
                            [
                                'code' => 'PLAST_MESH',
                                'name' => 'Plaster Mesh Fixing at Joints',
                                'unit' => 'Sq.m',
                                'aliases' => ['plaster mesh', 'chicken mesh plaster', 'joint mesh'],
                            ],
                        ],
                    ],
                    [
                        'code' => '10-04',
                        'name' => 'Screed & Levelling',
                        'activities' => [
                            [
                                'code' => 'PLAST_FLOOR_SCREED',
                                'name' => 'Floor Screed',
                                'unit' => 'Sq.m',
                                'aliases' => ['floor screed', 'cement screed', 'floor leveling screed'],
                            ],
                            [
                                'code' => 'PLAST_SLOPE_SCREED',
                                'name' => 'Slope Screed',
                                'unit' => 'Sq.m',
                                'aliases' => ['slope screed', 'bathroom slope', 'terrace slope screed'],
                            ],
                            [
                                'code' => 'PLAST_REPAIR',
                                'name' => 'Plaster Repair / Patch Work',
                                'unit' => 'Sq.m',
                                'aliases' => ['plaster patch', 'plaster repair', 'plaster rework'],
                            ],
                        ],
                    ],
                ],
            ],
            'WATERPROOFING' => [
                'description' => 'Waterproofing, damp-proofing, joint treatment, protective layers and water-retaining structure treatments.',
                'sections' => [
                    [
                        'code' => '11-01',
                        'name' => 'Wet Area Waterproofing',
                        'activities' => [
                            [
                                'code' => 'WP_TOILET',
                                'name' => 'Toilet / Bathroom Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['toilet waterproofing', 'bathroom waterproofing', 'washroom waterproofing'],
                            ],
                            [
                                'code' => 'WP_KITCHEN',
                                'name' => 'Kitchen / Utility Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['kitchen waterproofing', 'utility waterproofing'],
                            ],
                            [
                                'code' => 'WP_SUNKEN',
                                'name' => 'Sunken Slab Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['sunken waterproofing', 'sunken slab treatment'],
                            ],
                        ],
                    ],
                    [
                        'code' => '11-02',
                        'name' => 'Terrace & Roof Waterproofing',
                        'activities' => [
                            [
                                'code' => 'WP_TERRACE',
                                'name' => 'Terrace Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['terrace waterproofing', 'roof waterproofing'],
                            ],
                            [
                                'code' => 'WP_MEMBRANE',
                                'name' => 'Waterproofing Membrane Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['membrane waterproofing', 'waterproof membrane', 'app membrane'],
                            ],
                            [
                                'code' => 'WP_PROTECTIVE_SCREED',
                                'name' => 'Waterproofing Protective Screed',
                                'unit' => 'Sq.m',
                                'aliases' => ['protective screed', 'waterproof screed'],
                            ],
                        ],
                    ],
                    [
                        'code' => '11-03',
                        'name' => 'Below Ground Waterproofing',
                        'activities' => [
                            [
                                'code' => 'WP_BASEMENT',
                                'name' => 'Basement Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['basement waterproofing', 'below ground waterproofing'],
                            ],
                            [
                                'code' => 'WP_RETAINING',
                                'name' => 'Retaining Wall Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['retaining wall waterproofing'],
                            ],
                            [
                                'code' => 'WP_DPC',
                                'name' => 'Damp Proofing / DPC Treatment',
                                'unit' => 'Sq.m',
                                'aliases' => ['damp proofing', 'dpc treatment'],
                            ],
                        ],
                    ],
                    [
                        'code' => '11-04',
                        'name' => 'Water Retaining Structures',
                        'activities' => [
                            [
                                'code' => 'WP_TANK',
                                'name' => 'Water Tank Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['tank waterproofing', 'water tank treatment'],
                            ],
                            [
                                'code' => 'WP_POOL',
                                'name' => 'Swimming Pool Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['pool waterproofing', 'swimming pool treatment'],
                            ],
                            [
                                'code' => 'WP_STP',
                                'name' => 'STP / Sump Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['stp waterproofing', 'sump waterproofing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '11-05',
                        'name' => 'Joints & Repairs',
                        'activities' => [
                            [
                                'code' => 'WP_JOINT',
                                'name' => 'Construction / Expansion Joint Waterproofing',
                                'unit' => 'Rm',
                                'aliases' => ['joint waterproofing', 'expansion joint treatment', 'construction joint treatment'],
                            ],
                            [
                                'code' => 'WP_CRACK',
                                'name' => 'Waterproofing Crack Treatment',
                                'unit' => 'Rm',
                                'aliases' => ['crack waterproofing', 'leak crack repair'],
                            ],
                            [
                                'code' => 'WP_INJECTION',
                                'name' => 'PU / Chemical Injection Grouting',
                                'unit' => 'Nos',
                                'aliases' => ['pu injection', 'chemical grouting', 'leak injection'],
                            ],
                            [
                                'code' => 'WP_POND_TEST',
                                'name' => 'Waterproofing Pond Test',
                                'unit' => 'Sq.m',
                                'aliases' => ['pond test', 'water ponding test', 'waterproof test'],
                            ],
                        ],
                    ],
                ],
            ],
            'ROOFING' => [
                'description' => 'Roof covering, insulation, drainage interfaces and associated roof works.',
                'sections' => [
                    [
                        'code' => '12-01',
                        'name' => 'Roof Preparation',
                        'activities' => [
                            [
                                'code' => 'ROOF_SURFACE_PREP',
                                'name' => 'Roof Surface Preparation',
                                'unit' => 'Sq.m',
                                'aliases' => ['roof preparation', 'terrace surface preparation'],
                            ],
                            [
                                'code' => 'ROOF_SLOPE',
                                'name' => 'Roof Slope Formation',
                                'unit' => 'Sq.m',
                                'aliases' => ['roof slope', 'terrace slope', 'slope concrete'],
                            ],
                            [
                                'code' => 'ROOF_INSULATION',
                                'name' => 'Roof Thermal Insulation',
                                'unit' => 'Sq.m',
                                'aliases' => ['roof insulation', 'terrace insulation', 'thermal insulation'],
                            ],
                        ],
                    ],
                    [
                        'code' => '12-02',
                        'name' => 'Sheet & Metal Roofing',
                        'activities' => [
                            [
                                'code' => 'ROOF_SHEET',
                                'name' => 'Roofing Sheet Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['roof sheet', 'sheet roofing', 'metal roofing'],
                            ],
                            [
                                'code' => 'ROOF_PURLIN',
                                'name' => 'Roof Purlin Installation',
                                'unit' => 'Kg',
                                'aliases' => ['purlin fixing', 'roof purlin'],
                            ],
                            [
                                'code' => 'ROOF_FLASHING',
                                'name' => 'Roof Flashing Installation',
                                'unit' => 'Rm',
                                'aliases' => ['roof flashing', 'flashing fixing'],
                            ],
                            [
                                'code' => 'ROOF_RIDGE',
                                'name' => 'Ridge / Hip Cover Installation',
                                'unit' => 'Rm',
                                'aliases' => ['ridge cover', 'ridge flashing', 'hip cover'],
                            ],
                        ],
                    ],
                    [
                        'code' => '12-03',
                        'name' => 'Roof Drainage Interfaces',
                        'activities' => [
                            [
                                'code' => 'ROOF_GUTTER',
                                'name' => 'Roof Gutter Installation',
                                'unit' => 'Rm',
                                'aliases' => ['roof gutter', 'gutter fixing'],
                            ],
                            [
                                'code' => 'ROOF_DRAIN_OUTLET',
                                'name' => 'Roof Drain / Rainwater Outlet Installation',
                                'unit' => 'Nos',
                                'aliases' => ['roof drain', 'rainwater outlet', 'khurra outlet'],
                            ],
                        ],
                    ],
                    [
                        'code' => '12-04',
                        'name' => 'Roof Finishes',
                        'activities' => [
                            [
                                'code' => 'ROOF_TILE',
                                'name' => 'Roof Tile Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['roof tiles', 'tile roofing'],
                            ],
                            [
                                'code' => 'ROOF_PAVER',
                                'name' => 'Terrace Paver / Protective Tile Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['terrace paver', 'roof paver', 'terrace tiles'],
                            ],
                        ],
                    ],
                ],
            ],
            'FLOORING' => [
                'description' => 'Floor, wall tile, stone, skirting, dado and specialist flooring execution.',
                'sections' => [
                    [
                        'code' => '13-01',
                        'name' => 'Substrate Preparation',
                        'activities' => [
                            [
                                'code' => 'FLOOR_BASE_PREP',
                                'name' => 'Floor Base Surface Preparation',
                                'unit' => 'Sq.m',
                                'aliases' => ['floor preparation', 'floor base cleaning', 'tile base preparation'],
                            ],
                            [
                                'code' => 'FLOOR_LEVEL',
                                'name' => 'Floor Levelling / Screed',
                                'unit' => 'Sq.m',
                                'aliases' => ['floor leveling', 'floor levelling', 'floor screed'],
                            ],
                            [
                                'code' => 'FLOOR_MARKING',
                                'name' => 'Flooring Layout / Grid Marking',
                                'unit' => 'Sq.m',
                                'aliases' => ['tile marking', 'floor layout', 'tile grid'],
                            ],
                        ],
                    ],
                    [
                        'code' => '13-02',
                        'name' => 'Tile Flooring',
                        'activities' => [
                            [
                                'code' => 'FLOOR_VITRIFIED',
                                'name' => 'Vitrified Tile Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['vitrified tiles', 'vitrified flooring', 'tile flooring'],
                            ],
                            [
                                'code' => 'FLOOR_CERAMIC',
                                'name' => 'Ceramic Tile Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['ceramic flooring', 'ceramic tiles'],
                            ],
                            [
                                'code' => 'FLOOR_ANTISKID',
                                'name' => 'Anti-Skid Tile Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['anti skid tiles', 'bathroom floor tiles'],
                            ],
                            [
                                'code' => 'FLOOR_PORCELAIN',
                                'name' => 'Porcelain Tile Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['porcelain tiles', 'porcelain flooring'],
                            ],
                            [
                                'code' => 'FLOOR_TILE_GROUT',
                                'name' => 'Floor Tile Grouting',
                                'unit' => 'Sq.m',
                                'aliases' => ['tile grouting', 'floor grout'],
                            ],
                        ],
                    ],
                    [
                        'code' => '13-03',
                        'name' => 'Wall Tiles & Dado',
                        'activities' => [
                            [
                                'code' => 'FLOOR_WALL_TILE',
                                'name' => 'Wall Tile / Dado Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall tiles', 'dado tiles', 'toilet wall tiles', 'kitchen dado'],
                            ],
                            [
                                'code' => 'FLOOR_WALL_GROUT',
                                'name' => 'Wall Tile Grouting',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall tile grout', 'dado grouting'],
                            ],
                        ],
                    ],
                    [
                        'code' => '13-04',
                        'name' => 'Natural Stone',
                        'activities' => [
                            [
                                'code' => 'FLOOR_MARBLE',
                                'name' => 'Marble Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['marble flooring', 'marble laying'],
                            ],
                            [
                                'code' => 'FLOOR_GRANITE',
                                'name' => 'Granite Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['granite flooring', 'granite laying'],
                            ],
                            [
                                'code' => 'FLOOR_KOTA',
                                'name' => 'Kota Stone Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['kota flooring', 'kota stone'],
                            ],
                            [
                                'code' => 'FLOOR_STONE_CLAD',
                                'name' => 'Stone Wall Cladding',
                                'unit' => 'Sq.m',
                                'aliases' => ['stone cladding', 'granite wall cladding', 'marble wall cladding'],
                            ],
                            [
                                'code' => 'FLOOR_STONE_POLISH',
                                'name' => 'Stone Grinding / Polishing',
                                'unit' => 'Sq.m',
                                'aliases' => ['marble polishing', 'granite polishing', 'stone polishing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '13-05',
                        'name' => 'Skirting & Stair Finishes',
                        'activities' => [
                            [
                                'code' => 'FLOOR_SKIRT_TILE',
                                'name' => 'Tile Skirting',
                                'unit' => 'Rm',
                                'aliases' => ['tile skirting', 'skirting tiles'],
                            ],
                            [
                                'code' => 'FLOOR_SKIRT_STONE',
                                'name' => 'Stone Skirting',
                                'unit' => 'Rm',
                                'aliases' => ['granite skirting', 'marble skirting', 'stone skirting'],
                            ],
                            [
                                'code' => 'FLOOR_STAIR_STONE',
                                'name' => 'Staircase Granite / Marble Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['stair granite', 'stair marble', 'staircase stone'],
                            ],
                            [
                                'code' => 'FLOOR_STAIR_SKIRT',
                                'name' => 'Staircase Skirting',
                                'unit' => 'Rm',
                                'aliases' => ['stair skirting', 'staircase skirting'],
                            ],
                        ],
                    ],
                    [
                        'code' => '13-06',
                        'name' => 'Special Flooring',
                        'activities' => [
                            [
                                'code' => 'FLOOR_EPOXY',
                                'name' => 'Epoxy Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['epoxy floor', 'industrial epoxy flooring'],
                            ],
                            [
                                'code' => 'FLOOR_VINYL',
                                'name' => 'Vinyl Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['vinyl floor', 'pvc flooring'],
                            ],
                            [
                                'code' => 'FLOOR_WOOD',
                                'name' => 'Wooden / Laminate Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['wood flooring', 'laminate flooring'],
                            ],
                            [
                                'code' => 'FLOOR_CARPET',
                                'name' => 'Carpet Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['carpet flooring', 'carpet laying'],
                            ],
                            [
                                'code' => 'FLOOR_RAISED',
                                'name' => 'Raised Access Flooring',
                                'unit' => 'Sq.m',
                                'aliases' => ['raised floor', 'access floor'],
                            ],
                            [
                                'code' => 'FLOOR_INDUSTRIAL',
                                'name' => 'Industrial Floor / Floor Hardener',
                                'unit' => 'Sq.m',
                                'aliases' => ['industrial flooring', 'floor hardener', 'vdf flooring'],
                            ],
                        ],
                    ],
                    [
                        'code' => '13-07',
                        'name' => 'Floor Repairs',
                        'activities' => [
                            [
                                'code' => 'FLOOR_TILE_REPAIR',
                                'name' => 'Tile Replacement / Flooring Repair',
                                'unit' => 'Sq.m',
                                'aliases' => ['tile repair', 'tile replacement', 'floor repair'],
                            ],
                            [
                                'code' => 'FLOOR_JOINT_SEAL',
                                'name' => 'Floor Expansion Joint / Sealant Work',
                                'unit' => 'Rm',
                                'aliases' => ['floor joint', 'expansion joint floor', 'floor sealant'],
                            ],
                        ],
                    ],
                ],
            ],
            'CEILING' => [
                'description' => 'Conventional, gypsum, grid, acoustic and specialist ceiling execution.',
                'sections' => [
                    [
                        'code' => '14-01',
                        'name' => 'Ceiling Framework',
                        'activities' => [
                            [
                                'code' => 'CEIL_LEVEL_MARK',
                                'name' => 'Ceiling Level Marking',
                                'unit' => 'Sq.m',
                                'aliases' => ['ceiling marking', 'false ceiling level'],
                            ],
                            [
                                'code' => 'CEIL_GYPSUM_FRAME',
                                'name' => 'Gypsum Ceiling Framework',
                                'unit' => 'Sq.m',
                                'aliases' => ['gypsum frame', 'false ceiling frame'],
                            ],
                            [
                                'code' => 'CEIL_GRID_FRAME',
                                'name' => 'Grid Ceiling Framework',
                                'unit' => 'Sq.m',
                                'aliases' => ['ceiling grid', 't grid ceiling'],
                            ],
                        ],
                    ],
                    [
                        'code' => '14-02',
                        'name' => 'Gypsum & Board Ceilings',
                        'activities' => [
                            [
                                'code' => 'CEIL_GYPSUM_BOARD',
                                'name' => 'Gypsum Board False Ceiling',
                                'unit' => 'Sq.m',
                                'aliases' => ['gypsum ceiling', 'false ceiling', 'gypsum board ceiling'],
                            ],
                            [
                                'code' => 'CEIL_CEMENT_BOARD',
                                'name' => 'Cement Board Ceiling',
                                'unit' => 'Sq.m',
                                'aliases' => ['cement board ceiling', 'calcium silicate ceiling'],
                            ],
                            [
                                'code' => 'CEIL_BULKHEAD',
                                'name' => 'Ceiling Bulkhead / Cove Formation',
                                'unit' => 'Rm',
                                'aliases' => ['ceiling cove', 'bulkhead ceiling', 'false ceiling cove'],
                            ],
                        ],
                    ],
                    [
                        'code' => '14-03',
                        'name' => 'Grid & Acoustic Ceilings',
                        'activities' => [
                            [
                                'code' => 'CEIL_MINERAL',
                                'name' => 'Mineral Fibre Grid Ceiling',
                                'unit' => 'Sq.m',
                                'aliases' => ['mineral fiber ceiling', 'grid ceiling', 'armstrong ceiling'],
                            ],
                            [
                                'code' => 'CEIL_ACOUSTIC',
                                'name' => 'Acoustic Ceiling Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['acoustic ceiling', 'sound ceiling'],
                            ],
                            [
                                'code' => 'CEIL_METAL',
                                'name' => 'Metal Ceiling Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['metal ceiling', 'aluminium ceiling'],
                            ],
                        ],
                    ],
                    [
                        'code' => '14-04',
                        'name' => 'Ceiling Finishing',
                        'activities' => [
                            [
                                'code' => 'CEIL_JOINT',
                                'name' => 'Gypsum Ceiling Joint Treatment',
                                'unit' => 'Sq.m',
                                'aliases' => ['gypsum jointing', 'ceiling joint treatment'],
                            ],
                            [
                                'code' => 'CEIL_ACCESS',
                                'name' => 'Ceiling Access Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['access panel', 'ceiling trap door'],
                            ],
                            [
                                'code' => 'CEIL_REPAIR',
                                'name' => 'False Ceiling Repair / Rework',
                                'unit' => 'Sq.m',
                                'aliases' => ['ceiling repair', 'false ceiling rework'],
                            ],
                        ],
                    ],
                ],
            ],
            'DOORS_WINDOWS' => [
                'description' => 'Door, window, glazing, hardware, sealant and associated opening works.',
                'sections' => [
                    [
                        'code' => '15-01',
                        'name' => 'Door Frames',
                        'activities' => [
                            [
                                'code' => 'DW_WOOD_FRAME',
                                'name' => 'Wooden Door Frame Installation',
                                'unit' => 'Nos',
                                'aliases' => ['wood door frame', 'wooden frame fixing'],
                            ],
                            [
                                'code' => 'DW_METAL_FRAME',
                                'name' => 'Metal Door Frame Installation',
                                'unit' => 'Nos',
                                'aliases' => ['metal door frame', 'ms door frame'],
                            ],
                            [
                                'code' => 'DW_FIRE_FRAME',
                                'name' => 'Fire Rated Door Frame Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire door frame', 'fire rated frame'],
                            ],
                        ],
                    ],
                    [
                        'code' => '15-02',
                        'name' => 'Door Shutters',
                        'activities' => [
                            [
                                'code' => 'DW_WOOD_SHUTTER',
                                'name' => 'Wooden / Flush Door Shutter Installation',
                                'unit' => 'Nos',
                                'aliases' => ['flush door', 'wood door shutter', 'door shutter fixing'],
                            ],
                            [
                                'code' => 'DW_FIRE_DOOR',
                                'name' => 'Fire Rated Door Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire door', 'fire rated door'],
                            ],
                            [
                                'code' => 'DW_METAL_DOOR',
                                'name' => 'Steel / Metal Door Installation',
                                'unit' => 'Nos',
                                'aliases' => ['steel door', 'metal door'],
                            ],
                            [
                                'code' => 'DW_GLASS_DOOR',
                                'name' => 'Glass Door Installation',
                                'unit' => 'Nos',
                                'aliases' => ['glass door', 'toughened glass door'],
                            ],
                        ],
                    ],
                    [
                        'code' => '15-03',
                        'name' => 'Windows',
                        'activities' => [
                            [
                                'code' => 'DW_AL_WINDOW',
                                'name' => 'Aluminium Window Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['aluminium window', 'aluminum window'],
                            ],
                            [
                                'code' => 'DW_UPVC_WINDOW',
                                'name' => 'uPVC Window Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['upvc window', 'pvc window'],
                            ],
                            [
                                'code' => 'DW_STEEL_WINDOW',
                                'name' => 'Steel Window Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['steel window', 'ms window'],
                            ],
                        ],
                    ],
                    [
                        'code' => '15-04',
                        'name' => 'Glazing',
                        'activities' => [
                            [
                                'code' => 'DW_GLASS_FIX',
                                'name' => 'Glass / Glazing Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['glass fixing', 'glazing work', 'window glass'],
                            ],
                            [
                                'code' => 'DW_TOUGHENED',
                                'name' => 'Toughened Glass Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['toughened glass', 'tempered glass'],
                            ],
                            [
                                'code' => 'DW_MIRROR',
                                'name' => 'Mirror Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['mirror fixing', 'mirror work'],
                            ],
                        ],
                    ],
                    [
                        'code' => '15-05',
                        'name' => 'Hardware & Sealants',
                        'activities' => [
                            [
                                'code' => 'DW_HARDWARE',
                                'name' => 'Door / Window Hardware Installation',
                                'unit' => 'Nos',
                                'aliases' => ['door hardware', 'window hardware', 'ironmongery'],
                            ],
                            [
                                'code' => 'DW_CLOSER',
                                'name' => 'Door Closer Installation',
                                'unit' => 'Nos',
                                'aliases' => ['door closer', 'hydraulic closer'],
                            ],
                            [
                                'code' => 'DW_LOCK',
                                'name' => 'Lock / Latch Installation',
                                'unit' => 'Nos',
                                'aliases' => ['door lock', 'lock fixing', 'latch fixing'],
                            ],
                            [
                                'code' => 'DW_SEALANT',
                                'name' => 'Perimeter Sealant Around Doors / Windows',
                                'unit' => 'Rm',
                                'aliases' => ['window sealant', 'door sealant', 'perimeter silicone'],
                            ],
                            [
                                'code' => 'DW_FIRE_SEAL',
                                'name' => 'Fire / Smoke Seal Installation',
                                'unit' => 'Rm',
                                'aliases' => ['fire seal', 'smoke seal', 'door fire seal'],
                            ],
                        ],
                    ],
                    [
                        'code' => '15-06',
                        'name' => 'Opening Finishes',
                        'activities' => [
                            [
                                'code' => 'DW_SILL',
                                'name' => 'Window Sill Installation',
                                'unit' => 'Rm',
                                'aliases' => ['window sill', 'granite sill'],
                            ],
                            [
                                'code' => 'DW_REVEAL',
                                'name' => 'Door / Window Reveal Finishing',
                                'unit' => 'Rm',
                                'aliases' => ['window reveal', 'door reveal', 'opening finishing'],
                            ],
                        ],
                    ],
                ],
            ],
            'JOINERY' => [
                'description' => 'Carpentry, cabinetry, fixed furniture, counters and architectural joinery.',
                'sections' => [
                    [
                        'code' => '16-01',
                        'name' => 'Cabinetry',
                        'activities' => [
                            [
                                'code' => 'JOIN_KITCHEN_BASE',
                                'name' => 'Kitchen Base Cabinet Installation',
                                'unit' => 'Rm',
                                'aliases' => ['base cabinet', 'kitchen cabinet', 'kitchen base unit'],
                            ],
                            [
                                'code' => 'JOIN_KITCHEN_WALL',
                                'name' => 'Kitchen Wall Cabinet Installation',
                                'unit' => 'Rm',
                                'aliases' => ['wall cabinet', 'overhead kitchen cabinet'],
                            ],
                            [
                                'code' => 'JOIN_WARDROBE',
                                'name' => 'Wardrobe Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['wardrobe work', 'cupboard installation'],
                            ],
                            [
                                'code' => 'JOIN_VANITY',
                                'name' => 'Vanity Cabinet Installation',
                                'unit' => 'Nos',
                                'aliases' => ['vanity cabinet', 'bathroom vanity'],
                            ],
                        ],
                    ],
                    [
                        'code' => '16-02',
                        'name' => 'Fixed Furniture',
                        'activities' => [
                            [
                                'code' => 'JOIN_COUNTER',
                                'name' => 'Counter / Reception Desk Installation',
                                'unit' => 'Rm',
                                'aliases' => ['reception counter', 'counter installation'],
                            ],
                            [
                                'code' => 'JOIN_STORAGE',
                                'name' => 'Fixed Storage Cabinet Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['storage cabinet', 'fixed cupboard'],
                            ],
                            [
                                'code' => 'JOIN_SHELVING',
                                'name' => 'Fixed Shelving Installation',
                                'unit' => 'Rm',
                                'aliases' => ['shelf fixing', 'fixed shelves'],
                            ],
                            [
                                'code' => 'JOIN_BENCH',
                                'name' => 'Fixed Bench / Seating Installation',
                                'unit' => 'Rm',
                                'aliases' => ['fixed seating', 'bench installation'],
                            ],
                        ],
                    ],
                    [
                        'code' => '16-03',
                        'name' => 'Architectural Joinery',
                        'activities' => [
                            [
                                'code' => 'JOIN_WALL_PANEL',
                                'name' => 'Wooden / Laminate Wall Panelling',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall paneling', 'wood panel', 'laminate wall panel'],
                            ],
                            [
                                'code' => 'JOIN_DECORATIVE',
                                'name' => 'Decorative Joinery Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['decorative woodwork', 'architectural joinery'],
                            ],
                            [
                                'code' => 'JOIN_SKIRT',
                                'name' => 'Wooden Skirting Installation',
                                'unit' => 'Rm',
                                'aliases' => ['wood skirting', 'wooden skirting'],
                            ],
                            [
                                'code' => 'JOIN_HANDRAIL',
                                'name' => 'Wooden Handrail Installation',
                                'unit' => 'Rm',
                                'aliases' => ['wood handrail', 'wooden railing top'],
                            ],
                        ],
                    ],
                    [
                        'code' => '16-04',
                        'name' => 'Joinery Finishing',
                        'activities' => [
                            [
                                'code' => 'JOIN_LAMINATE',
                                'name' => 'Laminate / Veneer Application',
                                'unit' => 'Sq.m',
                                'aliases' => ['laminate fixing', 'veneer work'],
                            ],
                            [
                                'code' => 'JOIN_POLISH',
                                'name' => 'Wood Polish / Joinery Finishing',
                                'unit' => 'Sq.m',
                                'aliases' => ['wood polish', 'joinery polish'],
                            ],
                            [
                                'code' => 'JOIN_REPAIR',
                                'name' => 'Joinery Repair / Adjustment',
                                'unit' => 'Nos',
                                'aliases' => ['carpentry repair', 'cabinet adjustment', 'joinery repair'],
                            ],
                        ],
                    ],
                ],
            ],
            'PAINTING' => [
                'description' => 'Surface preparation, putty, primer, paint, coatings and decorative finish works.',
                'sections' => [
                    [
                        'code' => '17-01',
                        'name' => 'Surface Preparation',
                        'activities' => [
                            [
                                'code' => 'PAINT_SCRAPE',
                                'name' => 'Surface Scraping / Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['paint scraping', 'surface cleaning', 'wall scraping'],
                            ],
                            [
                                'code' => 'PAINT_PUTTY',
                                'name' => 'Wall Putty Application',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall putty', 'putty work', 'putty application'],
                            ],
                            [
                                'code' => 'PAINT_SANDING',
                                'name' => 'Putty / Surface Sanding',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall sanding', 'putty sanding', 'surface rubbing'],
                            ],
                            [
                                'code' => 'PAINT_CRACK_FILL',
                                'name' => 'Surface Crack Filling Before Painting',
                                'unit' => 'Rm',
                                'aliases' => ['paint crack filling', 'wall crack filling'],
                            ],
                        ],
                    ],
                    [
                        'code' => '17-02',
                        'name' => 'Primer',
                        'activities' => [
                            [
                                'code' => 'PAINT_INT_PRIMER',
                                'name' => 'Internal Wall Primer',
                                'unit' => 'Sq.m',
                                'aliases' => ['interior primer', 'internal primer', 'wall primer'],
                            ],
                            [
                                'code' => 'PAINT_EXT_PRIMER',
                                'name' => 'External Wall Primer',
                                'unit' => 'Sq.m',
                                'aliases' => ['exterior primer', 'external primer'],
                            ],
                            [
                                'code' => 'PAINT_METAL_PRIMER',
                                'name' => 'Metal Primer Coating',
                                'unit' => 'Sq.m',
                                'aliases' => ['metal primer', 'red oxide', 'steel primer'],
                            ],
                            [
                                'code' => 'PAINT_WOOD_PRIMER',
                                'name' => 'Wood Primer / Sealer',
                                'unit' => 'Sq.m',
                                'aliases' => ['wood primer', 'wood sealer'],
                            ],
                        ],
                    ],
                    [
                        'code' => '17-03',
                        'name' => 'Internal Painting',
                        'activities' => [
                            [
                                'code' => 'PAINT_INT_EMULSION',
                                'name' => 'Internal Emulsion Painting',
                                'unit' => 'Sq.m',
                                'aliases' => ['interior paint', 'internal emulsion', 'wall painting'],
                            ],
                            [
                                'code' => 'PAINT_CEILING',
                                'name' => 'Ceiling Painting',
                                'unit' => 'Sq.m',
                                'aliases' => ['ceiling paint', 'ceiling emulsion'],
                            ],
                            [
                                'code' => 'PAINT_ENAMEL',
                                'name' => 'Enamel Painting',
                                'unit' => 'Sq.m',
                                'aliases' => ['enamel paint', 'oil paint'],
                            ],
                        ],
                    ],
                    [
                        'code' => '17-04',
                        'name' => 'External Painting',
                        'activities' => [
                            [
                                'code' => 'PAINT_EXT_EMULSION',
                                'name' => 'Exterior Emulsion Painting',
                                'unit' => 'Sq.m',
                                'aliases' => ['exterior paint', 'external emulsion', 'outside painting'],
                            ],
                            [
                                'code' => 'PAINT_TEXTURE',
                                'name' => 'Texture Paint / Decorative Coating',
                                'unit' => 'Sq.m',
                                'aliases' => ['texture paint', 'texture coating', 'decorative texture'],
                            ],
                            [
                                'code' => 'PAINT_WEATHER',
                                'name' => 'Weatherproof Exterior Coating',
                                'unit' => 'Sq.m',
                                'aliases' => ['weather coat', 'weatherproof paint', 'exterior coating'],
                            ],
                        ],
                    ],
                    [
                        'code' => '17-05',
                        'name' => 'Special Coatings',
                        'activities' => [
                            [
                                'code' => 'PAINT_EPOXY',
                                'name' => 'Epoxy Coating / Painting',
                                'unit' => 'Sq.m',
                                'aliases' => ['epoxy paint', 'epoxy coating'],
                            ],
                            [
                                'code' => 'PAINT_FIRE',
                                'name' => 'Fire Retardant / Intumescent Coating',
                                'unit' => 'Sq.m',
                                'aliases' => ['fire retardant paint', 'intumescent paint'],
                            ],
                            [
                                'code' => 'PAINT_ANTI_CORR',
                                'name' => 'Anti-Corrosive Coating',
                                'unit' => 'Sq.m',
                                'aliases' => ['anti corrosion paint', 'anticorrosive coating'],
                            ],
                            [
                                'code' => 'PAINT_FLOOR_MARK',
                                'name' => 'Floor / Parking Marking Paint',
                                'unit' => 'Rm',
                                'aliases' => ['parking line marking', 'floor marking', 'road marking paint'],
                            ],
                        ],
                    ],
                    [
                        'code' => '17-06',
                        'name' => 'Touch-up & Rectification',
                        'activities' => [
                            [
                                'code' => 'PAINT_TOUCHUP',
                                'name' => 'Paint Touch-up',
                                'unit' => 'Sq.m',
                                'aliases' => ['paint touch up', 'painting touchup'],
                            ],
                            [
                                'code' => 'PAINT_REPAIR',
                                'name' => 'Painting Rectification / Rework',
                                'unit' => 'Sq.m',
                                'aliases' => ['paint repair', 'painting rework'],
                            ],
                        ],
                    ],
                ],
            ],
            'FACADE' => [
                'description' => 'External façade, curtain wall, cladding, glazing, louvers and façade sealing works.',
                'sections' => [
                    [
                        'code' => '18-01',
                        'name' => 'Curtain Wall',
                        'activities' => [
                            [
                                'code' => 'FAC_BRACKET',
                                'name' => 'Façade Bracket / Anchor Installation',
                                'unit' => 'Nos',
                                'aliases' => ['facade bracket', 'curtain wall bracket', 'anchor fixing'],
                            ],
                            [
                                'code' => 'FAC_MULLION',
                                'name' => 'Curtain Wall Mullion Installation',
                                'unit' => 'Rm',
                                'aliases' => ['mullion fixing', 'curtain wall mullion'],
                            ],
                            [
                                'code' => 'FAC_TRANSOM',
                                'name' => 'Curtain Wall Transom Installation',
                                'unit' => 'Rm',
                                'aliases' => ['transom fixing', 'curtain wall transom'],
                            ],
                            [
                                'code' => 'FAC_GLASS',
                                'name' => 'Curtain Wall Glass Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['curtain wall glazing', 'facade glass', 'structural glazing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '18-02',
                        'name' => 'Cladding',
                        'activities' => [
                            [
                                'code' => 'FAC_ACP',
                                'name' => 'ACP Cladding Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['acp work', 'aluminium composite panel', 'acp cladding'],
                            ],
                            [
                                'code' => 'FAC_HPL',
                                'name' => 'HPL Façade Cladding',
                                'unit' => 'Sq.m',
                                'aliases' => ['hpl cladding', 'hpl facade'],
                            ],
                            [
                                'code' => 'FAC_STONE',
                                'name' => 'External Stone Cladding',
                                'unit' => 'Sq.m',
                                'aliases' => ['facade stone cladding', 'external granite cladding'],
                            ],
                            [
                                'code' => 'FAC_METAL',
                                'name' => 'Metal Façade Cladding',
                                'unit' => 'Sq.m',
                                'aliases' => ['metal cladding', 'aluminium facade panel'],
                            ],
                        ],
                    ],
                    [
                        'code' => '18-03',
                        'name' => 'Façade Features',
                        'activities' => [
                            [
                                'code' => 'FAC_LOUVER',
                                'name' => 'Façade Louver Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['facade louver', 'aluminium louver'],
                            ],
                            [
                                'code' => 'FAC_CANOPY',
                                'name' => 'Entrance Canopy Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['canopy work', 'glass canopy', 'entrance canopy'],
                            ],
                            [
                                'code' => 'FAC_FIN',
                                'name' => 'Façade Fin / Feature Installation',
                                'unit' => 'Rm',
                                'aliases' => ['facade fins', 'architectural fins'],
                            ],
                        ],
                    ],
                    [
                        'code' => '18-04',
                        'name' => 'Sealants & Firestopping',
                        'activities' => [
                            [
                                'code' => 'FAC_SEALANT',
                                'name' => 'Façade Sealant Application',
                                'unit' => 'Rm',
                                'aliases' => ['facade silicone', 'weather sealant', 'curtain wall sealant'],
                            ],
                            [
                                'code' => 'FAC_PERIM_FIRESTOP',
                                'name' => 'Façade Perimeter Firestop Installation',
                                'unit' => 'Rm',
                                'aliases' => ['perimeter firestop', 'curtain wall firestop', 'slab edge firestop'],
                            ],
                        ],
                    ],
                    [
                        'code' => '18-05',
                        'name' => 'Façade Testing & Rectification',
                        'activities' => [
                            [
                                'code' => 'FAC_WATER_TEST',
                                'name' => 'Façade Water Leakage Test',
                                'unit' => 'Sq.m',
                                'aliases' => ['facade water test', 'spray test', 'curtain wall leak test'],
                            ],
                            [
                                'code' => 'FAC_GLASS_REPLACE',
                                'name' => 'Façade Glass Replacement',
                                'unit' => 'Sq.m',
                                'aliases' => ['facade glass replacement', 'curtain wall glass repair'],
                            ],
                            [
                                'code' => 'FAC_RECTIFY',
                                'name' => 'Façade Rectification / Alignment',
                                'unit' => 'Sq.m',
                                'aliases' => ['facade repair', 'facade alignment', 'curtain wall rectification'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
