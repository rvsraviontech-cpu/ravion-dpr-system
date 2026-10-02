<?php

namespace Database\Seeders;

use App\Models\WorkActivity;
use App\Models\WorkActivityAlias;
use App\Models\WorkPackage;
use App\Models\WorkSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkExecutionCatalogue0107Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $catalogue = $this->catalogue();

            foreach ($catalogue as $packageCode => $packageData) {
                $package = WorkPackage::query()
                    ->where('code', $packageCode)
                    ->first();

                if (! $package) {
                    throw new RuntimeException(
                        "Work Package [{$packageCode}] was not found. Run WorkExecutionMasterSeeder first."
                    );
                }

                $package->update([
                    'description' => $packageData['description'],
                ]);

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
                            $normalized = $this->normalizeAlias($alias);

                            WorkActivityAlias::updateOrCreate(
                                [
                                    'work_activity_id' => $activity->id,
                                    'normalized_alias' => $normalized,
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

            'PRE_CONSTRUCTION' => [
                'description' => 'Pre-construction coordination, statutory, investigation and planning activities that may be reported from site.',
                'sections' => [
                    [
                        'code' => '01-01',
                        'name' => 'Approvals & Statutory',
                        'activities' => [
                            [
                                'code' => 'PRE_APPROVAL_COORDINATION',
                                'name' => 'Authority Approval Coordination',
                                'unit' => 'Job',
                                'aliases' => ['approval work', 'authority approval', 'permit coordination'],
                            ],
                            [
                                'code' => 'PRE_SITE_HANDOVER',
                                'name' => 'Site Handover & Possession',
                                'unit' => 'Job',
                                'aliases' => ['site handover', 'site possession', 'possession handover'],
                            ],
                            [
                                'code' => 'PRE_UTILITY_CLEARANCE',
                                'name' => 'Existing Utility Clearance',
                                'unit' => 'Job',
                                'aliases' => ['utility clearance', 'existing services clearance', 'service clearance'],
                            ],
                            [
                                'code' => 'PRE_NOC_COORDINATION',
                                'name' => 'NOC Coordination',
                                'unit' => 'Job',
                                'aliases' => ['noc work', 'noc coordination', 'clearance coordination'],
                            ],
                        ],
                    ],
                    [
                        'code' => '01-02',
                        'name' => 'Investigations & Studies',
                        'activities' => [
                            [
                                'code' => 'PRE_SOIL_INVESTIGATION',
                                'name' => 'Soil Investigation',
                                'unit' => 'Nos',
                                'aliases' => ['soil test', 'soil investigation', 'borehole investigation', 'geotechnical investigation'],
                            ],
                            [
                                'code' => 'PRE_BOREHOLE_DRILLING',
                                'name' => 'Borehole Drilling',
                                'unit' => 'Rm',
                                'aliases' => ['bore drilling', 'soil bore', 'geotechnical borehole'],
                            ],
                            [
                                'code' => 'PRE_TRIAL_PIT',
                                'name' => 'Trial Pit Excavation',
                                'unit' => 'Nos',
                                'aliases' => ['trial pit', 'test pit', 'soil trial pit'],
                            ],
                            [
                                'code' => 'PRE_EXISTING_CONDITION',
                                'name' => 'Existing Condition Survey',
                                'unit' => 'Job',
                                'aliases' => ['existing survey', 'condition survey', 'pre construction survey'],
                            ],
                        ],
                    ],
                    [
                        'code' => '01-03',
                        'name' => 'Planning & Coordination',
                        'activities' => [
                            [
                                'code' => 'PRE_CONSTRUCTABILITY_REVIEW',
                                'name' => 'Constructability Review',
                                'unit' => 'Job',
                                'aliases' => ['constructability', 'drawing constructability review'],
                            ],
                            [
                                'code' => 'PRE_DRAWING_COORDINATION',
                                'name' => 'Construction Drawing Coordination',
                                'unit' => 'Job',
                                'aliases' => ['drawing coordination', 'site drawing coordination'],
                            ],
                            [
                                'code' => 'PRE_METHOD_STATEMENT',
                                'name' => 'Method Statement Preparation / Review',
                                'unit' => 'Job',
                                'aliases' => ['method statement', 'work methodology'],
                            ],
                            [
                                'code' => 'PRE_MOCKUP_PLANNING',
                                'name' => 'Mock-up Planning & Approval',
                                'unit' => 'Nos',
                                'aliases' => ['mockup planning', 'sample approval', 'mock up approval'],
                            ],
                        ],
                    ],
                ],
            ],
            'SURVEY_ENGINEERING' => [
                'description' => 'Survey control, setting out, level checks and engineering measurement activities.',
                'sections' => [
                    [
                        'code' => '02-01',
                        'name' => 'Control & Benchmarks',
                        'activities' => [
                            [
                                'code' => 'SURVEY_BENCHMARK',
                                'name' => 'Establish Site Benchmark',
                                'unit' => 'Nos',
                                'aliases' => ['benchmark fixing', 'site benchmark', 'tbm fixing', 'temporary benchmark'],
                            ],
                            [
                                'code' => 'SURVEY_CONTROL_POINT',
                                'name' => 'Establish Survey Control Points',
                                'unit' => 'Nos',
                                'aliases' => ['control point', 'survey control', 'grid control point'],
                            ],
                            [
                                'code' => 'SURVEY_GRID_TRANSFER',
                                'name' => 'Grid Line Establishment / Transfer',
                                'unit' => 'Rm',
                                'aliases' => ['grid marking', 'grid transfer', 'grid line marking'],
                            ],
                            [
                                'code' => 'SURVEY_LEVEL_TRANSFER',
                                'name' => 'Level Transfer',
                                'unit' => 'Nos',
                                'aliases' => ['level marking', 'level transfer', 'datum transfer'],
                            ],
                        ],
                    ],
                    [
                        'code' => '02-02',
                        'name' => 'Setting Out',
                        'activities' => [
                            [
                                'code' => 'SURVEY_BUILDING_SET_OUT',
                                'name' => 'Building Setting Out',
                                'unit' => 'Job',
                                'aliases' => ['building marking', 'building setout', 'building layout'],
                            ],
                            [
                                'code' => 'SURVEY_FOUNDATION_SET_OUT',
                                'name' => 'Foundation Setting Out',
                                'unit' => 'Nos',
                                'aliases' => ['footing marking', 'foundation marking', 'footing setout'],
                            ],
                            [
                                'code' => 'SURVEY_COLUMN_SET_OUT',
                                'name' => 'Column Setting Out',
                                'unit' => 'Nos',
                                'aliases' => ['column marking', 'column layout', 'column setout'],
                            ],
                            [
                                'code' => 'SURVEY_WALL_SET_OUT',
                                'name' => 'Wall / Partition Setting Out',
                                'unit' => 'Rm',
                                'aliases' => ['wall marking', 'partition marking', 'wall layout'],
                            ],
                            [
                                'code' => 'SURVEY_MEP_SET_OUT',
                                'name' => 'MEP Opening / Sleeve Setting Out',
                                'unit' => 'Nos',
                                'aliases' => ['mep marking', 'sleeve marking', 'opening marking'],
                            ],
                        ],
                    ],
                    [
                        'code' => '02-03',
                        'name' => 'Survey Checks',
                        'activities' => [
                            [
                                'code' => 'SURVEY_TOPOGRAPHIC',
                                'name' => 'Topographic Survey',
                                'unit' => 'Sq.m',
                                'aliases' => ['topo survey', 'topographical survey', 'land survey'],
                            ],
                            [
                                'code' => 'SURVEY_LEVEL_CHECK',
                                'name' => 'Level Survey / Level Checking',
                                'unit' => 'Sq.m',
                                'aliases' => ['level check', 'level survey', 'rl checking'],
                            ],
                            [
                                'code' => 'SURVEY_VERTICALITY',
                                'name' => 'Verticality Check',
                                'unit' => 'Nos',
                                'aliases' => ['plumb check', 'verticality survey', 'column verticality'],
                            ],
                            [
                                'code' => 'SURVEY_AS_BUILT',
                                'name' => 'As-Built Survey',
                                'unit' => 'Job',
                                'aliases' => ['as built survey', 'asbuilt measurement', 'final survey'],
                            ],
                            [
                                'code' => 'SURVEY_AREA_MEASUREMENT',
                                'name' => 'Area / Quantity Measurement Survey',
                                'unit' => 'Job',
                                'aliases' => ['quantity survey measurement', 'area measurement', 'site measurement'],
                            ],
                        ],
                    ],
                ],
            ],
            'SITE_ESTABLISHMENT' => [
                'description' => 'Mobilization, temporary facilities, utilities, access, safety and site establishment works.',
                'sections' => [
                    [
                        'code' => '03-01',
                        'name' => 'Site Access & Security',
                        'activities' => [
                            [
                                'code' => 'SITE_TEMP_GATE',
                                'name' => 'Temporary Site Gate Installation',
                                'unit' => 'Nos',
                                'aliases' => ['temporary gate', 'site gate', 'construction gate'],
                            ],
                            [
                                'code' => 'SITE_HOARDING',
                                'name' => 'Site Hoarding / Barricading',
                                'unit' => 'Rm',
                                'aliases' => ['site barricading', 'hoarding work', 'temporary fencing', 'site fencing'],
                            ],
                            [
                                'code' => 'SITE_SECURITY_CABIN',
                                'name' => 'Security Cabin Installation',
                                'unit' => 'Nos',
                                'aliases' => ['security cabin', 'guard cabin', 'watchman cabin'],
                            ],
                            [
                                'code' => 'SITE_ACCESS_ROAD',
                                'name' => 'Temporary Access Road',
                                'unit' => 'Sq.m',
                                'aliases' => ['temporary road', 'site access road', 'construction access'],
                            ],
                        ],
                    ],
                    [
                        'code' => '03-02',
                        'name' => 'Temporary Facilities',
                        'activities' => [
                            [
                                'code' => 'SITE_OFFICE',
                                'name' => 'Site Office Setup',
                                'unit' => 'Nos',
                                'aliases' => ['site office', 'office cabin', 'temporary office'],
                            ],
                            [
                                'code' => 'SITE_STORE',
                                'name' => 'Site Store / Storage Setup',
                                'unit' => 'Nos',
                                'aliases' => ['site store', 'material store', 'storage shed'],
                            ],
                            [
                                'code' => 'SITE_LABOUR_FACILITY',
                                'name' => 'Labour Welfare / Rest Area Setup',
                                'unit' => 'Nos',
                                'aliases' => ['labour shed', 'labour rest area', 'worker facility'],
                            ],
                            [
                                'code' => 'SITE_TEMP_TOILET',
                                'name' => 'Temporary Toilet Setup',
                                'unit' => 'Nos',
                                'aliases' => ['site toilet', 'temporary toilet', 'worker toilet'],
                            ],
                            [
                                'code' => 'SITE_DRINKING_WATER',
                                'name' => 'Temporary Drinking Water Arrangement',
                                'unit' => 'Job',
                                'aliases' => ['drinking water setup', 'site drinking water'],
                            ],
                        ],
                    ],
                    [
                        'code' => '03-03',
                        'name' => 'Temporary Utilities',
                        'activities' => [
                            [
                                'code' => 'SITE_TEMP_POWER',
                                'name' => 'Temporary Electrical Supply Setup',
                                'unit' => 'Job',
                                'aliases' => ['temporary power', 'site power', 'temporary electrical connection'],
                            ],
                            [
                                'code' => 'SITE_TEMP_LIGHTING',
                                'name' => 'Temporary Site Lighting',
                                'unit' => 'Nos',
                                'aliases' => ['site lighting', 'temporary lights', 'construction lighting'],
                            ],
                            [
                                'code' => 'SITE_TEMP_WATER',
                                'name' => 'Temporary Water Supply Setup',
                                'unit' => 'Job',
                                'aliases' => ['temporary water', 'site water connection', 'construction water'],
                            ],
                            [
                                'code' => 'SITE_TEMP_DRAINAGE',
                                'name' => 'Temporary Drainage Arrangement',
                                'unit' => 'Rm',
                                'aliases' => ['temporary drainage', 'site drain', 'construction drainage'],
                            ],
                        ],
                    ],
                    [
                        'code' => '03-04',
                        'name' => 'Safety & Information',
                        'activities' => [
                            [
                                'code' => 'SITE_SIGNAGE',
                                'name' => 'Site Signage Installation',
                                'unit' => 'Nos',
                                'aliases' => ['site signage', 'construction sign board', 'notice board'],
                            ],
                            [
                                'code' => 'SITE_SAFETY_SIGNAGE',
                                'name' => 'Safety Signage Installation',
                                'unit' => 'Nos',
                                'aliases' => ['safety board', 'safety signage', 'warning signs'],
                            ],
                            [
                                'code' => 'SITE_FIRE_POINT',
                                'name' => 'Temporary Fire Point Setup',
                                'unit' => 'Nos',
                                'aliases' => ['fire point', 'temporary fire station', 'fire extinguisher point'],
                            ],
                            [
                                'code' => 'SITE_FIRST_AID',
                                'name' => 'First Aid Station Setup',
                                'unit' => 'Nos',
                                'aliases' => ['first aid point', 'first aid station'],
                            ],
                        ],
                    ],
                ],
            ],
            'SITE_PREPARATION' => [
                'description' => 'Clearing, demolition, debris handling and preparation of the site before permanent construction.',
                'sections' => [
                    [
                        'code' => '04-01',
                        'name' => 'Clearing & Grubbing',
                        'activities' => [
                            [
                                'code' => 'PREP_VEGETATION_CLEAR',
                                'name' => 'Vegetation / Bush Clearing',
                                'unit' => 'Sq.m',
                                'aliases' => ['bush clearing', 'vegetation clearing', 'jungle clearing', 'site clearing'],
                            ],
                            [
                                'code' => 'PREP_TREE_CUTTING',
                                'name' => 'Tree Cutting',
                                'unit' => 'Nos',
                                'aliases' => ['tree cutting', 'tree felling', 'cut tree'],
                            ],
                            [
                                'code' => 'PREP_TREE_UPROOTING',
                                'name' => 'Tree Uprooting / Stump Removal',
                                'unit' => 'Nos',
                                'aliases' => ['tree uprooting', 'stump removal', 'root removal'],
                            ],
                            [
                                'code' => 'PREP_TOPSOIL_STRIP',
                                'name' => 'Topsoil Stripping',
                                'unit' => 'Cum',
                                'aliases' => ['top soil removal', 'topsoil stripping', 'remove top soil'],
                            ],
                        ],
                    ],
                    [
                        'code' => '04-02',
                        'name' => 'Demolition',
                        'activities' => [
                            [
                                'code' => 'PREP_BUILDING_DEMO',
                                'name' => 'Existing Building Demolition',
                                'unit' => 'Cum',
                                'aliases' => ['building demolition', 'structure demolition', 'demolition work'],
                            ],
                            [
                                'code' => 'PREP_WALL_DEMO',
                                'name' => 'Wall / Partition Demolition',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall demolition', 'partition removal', 'break wall'],
                            ],
                            [
                                'code' => 'PREP_FLOOR_DEMO',
                                'name' => 'Existing Flooring Demolition',
                                'unit' => 'Sq.m',
                                'aliases' => ['floor demolition', 'tile removal', 'flooring removal'],
                            ],
                            [
                                'code' => 'PREP_RCC_DEMO',
                                'name' => 'RCC Demolition / Breaking',
                                'unit' => 'Cum',
                                'aliases' => ['rcc breaking', 'concrete demolition', 'rcc demolition'],
                            ],
                            [
                                'code' => 'PREP_PAVEMENT_DEMO',
                                'name' => 'Pavement / Hardscape Removal',
                                'unit' => 'Sq.m',
                                'aliases' => ['pavement removal', 'paver removal', 'hardscape demolition'],
                            ],
                            [
                                'code' => 'PREP_SERVICE_DISMANTLE',
                                'name' => 'Existing Service Dismantling',
                                'unit' => 'Job',
                                'aliases' => ['service dismantling', 'utility dismantling', 'old services removal'],
                            ],
                        ],
                    ],
                    [
                        'code' => '04-03',
                        'name' => 'Debris & Cleaning',
                        'activities' => [
                            [
                                'code' => 'PREP_DEBRIS_COLLECTION',
                                'name' => 'Demolition Debris Collection',
                                'unit' => 'Cum',
                                'aliases' => ['debris collection', 'malba collection', 'waste collection'],
                            ],
                            [
                                'code' => 'PREP_DEBRIS_SHIFT',
                                'name' => 'Debris Shifting / Loading',
                                'unit' => 'Cum',
                                'aliases' => ['debris shifting', 'debris loading', 'malba shifting'],
                            ],
                            [
                                'code' => 'PREP_DEBRIS_DISPOSAL',
                                'name' => 'Debris Disposal',
                                'unit' => 'Cum',
                                'aliases' => ['debris disposal', 'debris removal', 'malba disposal'],
                            ],
                            [
                                'code' => 'PREP_SITE_CLEAN',
                                'name' => 'Initial Site Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['site cleaning', 'initial cleaning', 'plot cleaning'],
                            ],
                        ],
                    ],
                    [
                        'code' => '04-04',
                        'name' => 'Site Preparation',
                        'activities' => [
                            [
                                'code' => 'PREP_ROUGH_GRADING',
                                'name' => 'Rough Site Grading',
                                'unit' => 'Sq.m',
                                'aliases' => ['rough grading', 'site grading', 'ground grading'],
                            ],
                            [
                                'code' => 'PREP_SURFACE_LEVEL',
                                'name' => 'Existing Ground Levelling',
                                'unit' => 'Sq.m',
                                'aliases' => ['land leveling', 'land levelling', 'ground leveling', 'site leveling'],
                            ],
                            [
                                'code' => 'PREP_TEMP_PROTECTION',
                                'name' => 'Temporary Protection of Existing Works / Services',
                                'unit' => 'Job',
                                'aliases' => ['temporary protection', 'existing work protection', 'service protection'],
                            ],
                        ],
                    ],
                ],
            ],
            'EARTHWORK' => [
                'description' => 'Excavation, dewatering, filling, compaction, earth shifting and disposal operations.',
                'sections' => [
                    [
                        'code' => '05-01',
                        'name' => 'Excavation',
                        'activities' => [
                            [
                                'code' => 'EARTH_MANUAL_EXCAVATION',
                                'name' => 'Manual Excavation',
                                'unit' => 'Cum',
                                'aliases' => ['manual excavation', 'hand excavation', 'excavation by labour'],
                            ],
                            [
                                'code' => 'EARTH_MECH_EXCAVATION',
                                'name' => 'Mechanical Excavation',
                                'unit' => 'Cum',
                                'aliases' => ['mechanical excavation', 'machine excavation', 'jcb excavation', 'excavator work', 'earth excavation jcb'],
                            ],
                            [
                                'code' => 'EARTH_TRENCH_EXCAVATION',
                                'name' => 'Trench Excavation',
                                'unit' => 'Cum',
                                'aliases' => ['trench excavation', 'service trench', 'pipe trench excavation'],
                            ],
                            [
                                'code' => 'EARTH_ROCK_EXCAVATION',
                                'name' => 'Rock Excavation / Cutting',
                                'unit' => 'Cum',
                                'aliases' => ['rock cutting', 'rock excavation', 'hard rock excavation'],
                            ],
                            [
                                'code' => 'EARTH_EXTRA_OVERDEPTH',
                                'name' => 'Extra / Over-Depth Excavation',
                                'unit' => 'Cum',
                                'aliases' => ['overdepth excavation', 'extra depth excavation', 'deep excavation'],
                            ],
                        ],
                    ],
                    [
                        'code' => '05-02',
                        'name' => 'Excavation Support & Dewatering',
                        'activities' => [
                            [
                                'code' => 'EARTH_DEWATERING',
                                'name' => 'Excavation Dewatering',
                                'unit' => 'Hour',
                                'aliases' => ['dewatering', 'water pumping', 'excavation pumping', 'sump pumping'],
                            ],
                            [
                                'code' => 'EARTH_SHORING_SUPPORT',
                                'name' => 'Excavation Shoring / Temporary Support',
                                'unit' => 'Sq.m',
                                'aliases' => ['shoring', 'excavation support', 'trench support'],
                            ],
                            [
                                'code' => 'EARTH_SLOPE_TRIM',
                                'name' => 'Excavation Slope Trimming',
                                'unit' => 'Sq.m',
                                'aliases' => ['slope trimming', 'excavation trimming', 'side trimming'],
                            ],
                            [
                                'code' => 'EARTH_BOTTOM_DRESS',
                                'name' => 'Excavation Bottom Dressing & Levelling',
                                'unit' => 'Sq.m',
                                'aliases' => ['bottom dressing', 'excavation bed leveling', 'foundation bed dressing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '05-03',
                        'name' => 'Filling & Backfilling',
                        'activities' => [
                            [
                                'code' => 'EARTH_BACKFILL',
                                'name' => 'Backfilling with Excavated Soil',
                                'unit' => 'Cum',
                                'aliases' => ['backfilling', 'back filling', 'soil filling', 'earth filling'],
                            ],
                            [
                                'code' => 'EARTH_IMPORTED_FILL',
                                'name' => 'Filling with Imported Soil',
                                'unit' => 'Cum',
                                'aliases' => ['imported soil filling', 'borrow earth filling', 'external soil filling'],
                            ],
                            [
                                'code' => 'EARTH_MURRAM_FILL',
                                'name' => 'Murram Filling',
                                'unit' => 'Cum',
                                'aliases' => ['murram filling', 'muram filling', 'morum filling', 'plinth murram'],
                            ],
                            [
                                'code' => 'EARTH_SAND_FILL',
                                'name' => 'Sand Filling',
                                'unit' => 'Cum',
                                'aliases' => ['sand filling', 'plinth sand filling', 'sand backfill'],
                            ],
                            [
                                'code' => 'EARTH_PLINTH_FILL',
                                'name' => 'Plinth Backfilling & Compaction',
                                'unit' => 'Cum',
                                'aliases' => ['plinth filling', 'plinth backfilling', 'plinth back filling', 'murram filling', 'muram filling', 'plinth compaction'],
                            ],
                            [
                                'code' => 'EARTH_LAYER_COMPACT',
                                'name' => 'Layer-wise Filling & Compaction',
                                'unit' => 'Cum',
                                'aliases' => ['layer compaction', 'layer filling', 'soil compaction', 'earth compaction'],
                            ],
                        ],
                    ],
                    [
                        'code' => '05-04',
                        'name' => 'Disposal & Transportation',
                        'activities' => [
                            [
                                'code' => 'EARTH_LOADING',
                                'name' => 'Excavated Soil Loading',
                                'unit' => 'Cum',
                                'aliases' => ['soil loading', 'earth loading', 'excavated earth loading'],
                            ],
                            [
                                'code' => 'EARTH_SHIFTING',
                                'name' => 'Internal Earth Shifting',
                                'unit' => 'Cum',
                                'aliases' => ['soil shifting', 'earth shifting', 'internal soil transport'],
                            ],
                            [
                                'code' => 'EARTH_DISPOSAL',
                                'name' => 'Surplus Soil Disposal',
                                'unit' => 'Cum',
                                'aliases' => ['soil disposal', 'earth disposal', 'surplus earth removal'],
                            ],
                        ],
                    ],
                ],
            ],
            'FOUNDATION' => [
                'description' => 'Foundation preparation, footings, pedestals, grade beams, raft, piles and below-ground protection works.',
                'sections' => [
                    [
                        'code' => '06-01',
                        'name' => 'Foundation Preparation',
                        'activities' => [
                            [
                                'code' => 'FOUND_ANTI_TERMITE',
                                'name' => 'Anti-Termite Treatment Below Foundation',
                                'unit' => 'Sq.m',
                                'aliases' => ['anti termite', 'antitermite treatment', 'foundation termite treatment'],
                            ],
                            [
                                'code' => 'FOUND_PCC',
                                'name' => 'Foundation PCC',
                                'unit' => 'Cum',
                                'aliases' => ['pcc', 'plain cement concrete', 'foundation pcc', 'bed concrete'],
                            ],
                            [
                                'code' => 'FOUND_PCC_CURING',
                                'name' => 'Foundation PCC Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['pcc curing', 'foundation curing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '06-02',
                        'name' => 'Footings',
                        'activities' => [
                            [
                                'code' => 'FOUND_FOOTING_REBAR',
                                'name' => 'Footing Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['footing steel', 'footing rebar', 'footing reinforcement'],
                            ],
                            [
                                'code' => 'FOUND_FOOTING_FORM',
                                'name' => 'Footing Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['footing shuttering', 'footing formwork', 'footing centering'],
                            ],
                            [
                                'code' => 'FOUND_FOOTING_CONCRETE',
                                'name' => 'Footing Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['footing concrete', 'footing casting', 'footing rcc'],
                            ],
                            [
                                'code' => 'FOUND_FOOTING_DESHUTTER',
                                'name' => 'Footing De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['footing deshuttering', 'footing de shuttering', 'footing shutter removal'],
                            ],
                            [
                                'code' => 'FOUND_FOOTING_CURING',
                                'name' => 'Footing Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['footing curing', 'cure footing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '06-03',
                        'name' => 'Pedestals & Foundation Columns',
                        'activities' => [
                            [
                                'code' => 'FOUND_PEDESTAL_REBAR',
                                'name' => 'Pedestal Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['pedestal steel', 'pedestal rebar'],
                            ],
                            [
                                'code' => 'FOUND_PEDESTAL_FORM',
                                'name' => 'Pedestal Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['pedestal shuttering', 'pedestal formwork'],
                            ],
                            [
                                'code' => 'FOUND_PEDESTAL_CONCRETE',
                                'name' => 'Pedestal Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['pedestal concrete', 'pedestal casting'],
                            ],
                            [
                                'code' => 'FOUND_PEDESTAL_DESHUTTER',
                                'name' => 'Pedestal De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['pedestal deshuttering', 'pedestal shutter removal'],
                            ],
                            [
                                'code' => 'FOUND_PEDESTAL_CURING',
                                'name' => 'Pedestal Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['pedestal curing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '06-04',
                        'name' => 'Plinth Beams & Grade Beams',
                        'activities' => [
                            [
                                'code' => 'FOUND_PLINTH_REBAR',
                                'name' => 'Plinth / Grade Beam Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['plinth beam steel', 'grade beam steel', 'plinth beam reinforcement'],
                            ],
                            [
                                'code' => 'FOUND_PLINTH_FORM',
                                'name' => 'Plinth / Grade Beam Shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['plinth beam shuttering', 'grade beam formwork'],
                            ],
                            [
                                'code' => 'FOUND_PLINTH_CONCRETE',
                                'name' => 'Plinth / Grade Beam Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['plinth beam concrete', 'grade beam concrete', 'plinth beam casting'],
                            ],
                            [
                                'code' => 'FOUND_PLINTH_DESHUTTER',
                                'name' => 'Plinth / Grade Beam De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['plinth beam deshuttering', 'grade beam shutter removal'],
                            ],
                            [
                                'code' => 'FOUND_PLINTH_CURING',
                                'name' => 'Plinth / Grade Beam Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['plinth beam curing', 'grade beam curing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '06-05',
                        'name' => 'Raft & Mat Foundation',
                        'activities' => [
                            [
                                'code' => 'FOUND_RAFT_PCC',
                                'name' => 'Raft PCC / Blinding Concrete',
                                'unit' => 'Cum',
                                'aliases' => ['raft pcc', 'raft blinding', 'mat foundation pcc'],
                            ],
                            [
                                'code' => 'FOUND_RAFT_REBAR',
                                'name' => 'Raft Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['raft steel', 'raft rebar', 'mat reinforcement'],
                            ],
                            [
                                'code' => 'FOUND_RAFT_FORM',
                                'name' => 'Raft Edge Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['raft shuttering', 'raft formwork', 'raft edge shutter'],
                            ],
                            [
                                'code' => 'FOUND_RAFT_CONCRETE',
                                'name' => 'Raft Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['raft concrete', 'raft casting', 'mat foundation concrete'],
                            ],
                            [
                                'code' => 'FOUND_RAFT_CURING',
                                'name' => 'Raft Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['raft curing', 'mat curing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '06-06',
                        'name' => 'Pile Foundation',
                        'activities' => [
                            [
                                'code' => 'FOUND_PILE_BORING',
                                'name' => 'Pile Boring / Drilling',
                                'unit' => 'Rm',
                                'aliases' => ['pile boring', 'pile drilling', 'bored pile'],
                            ],
                            [
                                'code' => 'FOUND_PILE_REBAR',
                                'name' => 'Pile Reinforcement Cage',
                                'unit' => 'Kg',
                                'aliases' => ['pile cage', 'pile reinforcement', 'pile steel cage'],
                            ],
                            [
                                'code' => 'FOUND_PILE_CONCRETE',
                                'name' => 'Pile Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['pile concrete', 'pile casting'],
                            ],
                            [
                                'code' => 'FOUND_PILE_HEAD_BREAK',
                                'name' => 'Pile Head Breaking / Chipping',
                                'unit' => 'Nos',
                                'aliases' => ['pile head breaking', 'pile chipping', 'pile head trimming'],
                            ],
                            [
                                'code' => 'FOUND_PILE_CAP_REBAR',
                                'name' => 'Pile Cap Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['pile cap steel', 'pile cap rebar'],
                            ],
                            [
                                'code' => 'FOUND_PILE_CAP_FORM',
                                'name' => 'Pile Cap Shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['pile cap shuttering', 'pile cap formwork'],
                            ],
                            [
                                'code' => 'FOUND_PILE_CAP_CONCRETE',
                                'name' => 'Pile Cap Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['pile cap concrete', 'pile cap casting'],
                            ],
                        ],
                    ],
                    [
                        'code' => '06-07',
                        'name' => 'Foundation Waterproofing & Protection',
                        'activities' => [
                            [
                                'code' => 'FOUND_WATERPROOF',
                                'name' => 'Foundation Waterproofing',
                                'unit' => 'Sq.m',
                                'aliases' => ['foundation waterproofing', 'below ground waterproofing'],
                            ],
                            [
                                'code' => 'FOUND_PROTECTION',
                                'name' => 'Foundation Waterproofing Protection',
                                'unit' => 'Sq.m',
                                'aliases' => ['waterproof protection', 'foundation protection board'],
                            ],
                        ],
                    ],
                ],
            ],
            'RCC' => [
                'description' => 'Reinforced cement concrete structural execution including reinforcement, formwork, concreting, curing and rectification.',
                'sections' => [
                    [
                        'code' => '07-01',
                        'name' => 'Columns',
                        'activities' => [
                            [
                                'code' => 'RCC_COLUMN_REBAR',
                                'name' => 'Column Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['column steel', 'column rebar', 'column reinforcement'],
                            ],
                            [
                                'code' => 'RCC_COLUMN_FORM',
                                'name' => 'Column Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['column shuttering', 'column formwork', 'column centering'],
                            ],
                            [
                                'code' => 'RCC_COLUMN_CONCRETE',
                                'name' => 'Column Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['column concrete', 'column casting', 'rcc column'],
                            ],
                            [
                                'code' => 'RCC_COLUMN_DESHUTTER',
                                'name' => 'Column De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['column deshuttering', 'column de shuttering', 'column shutter removal'],
                            ],
                            [
                                'code' => 'RCC_COLUMN_CURING',
                                'name' => 'Column Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['column curing', 'cure column'],
                            ],
                            [
                                'code' => 'RCC_COLUMN_REPAIR',
                                'name' => 'Column Concrete Repair',
                                'unit' => 'Sq.m',
                                'aliases' => ['column repair', 'column honeycomb repair', 'column patch repair'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-02',
                        'name' => 'Beams',
                        'activities' => [
                            [
                                'code' => 'RCC_BEAM_REBAR',
                                'name' => 'Beam Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['beam steel', 'beam rebar', 'beam reinforcement'],
                            ],
                            [
                                'code' => 'RCC_BEAM_FORM',
                                'name' => 'Beam Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['beam shuttering', 'beam formwork', 'beam centering'],
                            ],
                            [
                                'code' => 'RCC_BEAM_CONCRETE',
                                'name' => 'Beam Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['beam concrete', 'beam casting', 'rcc beam'],
                            ],
                            [
                                'code' => 'RCC_BEAM_DESHUTTER',
                                'name' => 'Beam De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['beam deshuttering', 'beam de shuttering', 'beam shutter removal', 'beam centering removal'],
                            ],
                            [
                                'code' => 'RCC_BEAM_CURING',
                                'name' => 'Beam Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['beam curing', 'cure beam'],
                            ],
                            [
                                'code' => 'RCC_BEAM_REPAIR',
                                'name' => 'Beam Concrete Repair',
                                'unit' => 'Sq.m',
                                'aliases' => ['beam repair', 'beam honeycomb repair'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-03',
                        'name' => 'Slabs',
                        'activities' => [
                            [
                                'code' => 'RCC_SLAB_FORM',
                                'name' => 'Slab Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['slab shuttering', 'slab formwork', 'slab centering', 'centering work'],
                            ],
                            [
                                'code' => 'RCC_SLAB_REBAR',
                                'name' => 'Slab Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['slab steel', 'slab rebar', 'slab reinforcement'],
                            ],
                            [
                                'code' => 'RCC_SLAB_MEP_EMBED',
                                'name' => 'Slab MEP Sleeve / Conduit Embed Coordination',
                                'unit' => 'Sq.m',
                                'aliases' => ['slab conduit work', 'slab sleeve work', 'mep embed', 'electrical conduit in slab'],
                            ],
                            [
                                'code' => 'RCC_SLAB_CONCRETE',
                                'name' => 'Slab Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['slab concrete', 'slab casting', 'roof slab concrete', 'rcc slab'],
                            ],
                            [
                                'code' => 'RCC_SLAB_FINISH',
                                'name' => 'Slab Concrete Surface Finishing',
                                'unit' => 'Sq.m',
                                'aliases' => ['slab finishing', 'concrete surface finish', 'power trowel slab'],
                            ],
                            [
                                'code' => 'RCC_SLAB_CURING',
                                'name' => 'Slab Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['slab curing', 'roof slab curing', 'pond curing'],
                            ],
                            [
                                'code' => 'RCC_SLAB_DESHUTTER',
                                'name' => 'Slab De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['slab deshuttering', 'slab de shuttering', 'slab shutter removal', 'shuttering removal', 'centering removal', 'slab centering removal', 'remove slab shutter'],
                            ],
                            [
                                'code' => 'RCC_SLAB_RESHORE',
                                'name' => 'Slab Re-shoring / Prop Support',
                                'unit' => 'Sq.m',
                                'aliases' => ['reshoring', 're shoring', 'slab props', 'slab support'],
                            ],
                            [
                                'code' => 'RCC_SLAB_REPAIR',
                                'name' => 'Slab Concrete Repair',
                                'unit' => 'Sq.m',
                                'aliases' => ['slab repair', 'slab honeycomb repair', 'concrete patch repair'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-04',
                        'name' => 'Staircases',
                        'activities' => [
                            [
                                'code' => 'RCC_STAIR_FORM',
                                'name' => 'Staircase Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['stair shuttering', 'staircase formwork', 'stair centering'],
                            ],
                            [
                                'code' => 'RCC_STAIR_REBAR',
                                'name' => 'Staircase Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['stair steel', 'staircase rebar', 'stair reinforcement'],
                            ],
                            [
                                'code' => 'RCC_STAIR_CONCRETE',
                                'name' => 'Staircase Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['stair concrete', 'staircase casting'],
                            ],
                            [
                                'code' => 'RCC_STAIR_DESHUTTER',
                                'name' => 'Staircase De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['stair deshuttering', 'staircase shutter removal'],
                            ],
                            [
                                'code' => 'RCC_STAIR_CURING',
                                'name' => 'Staircase Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['stair curing', 'staircase curing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-05',
                        'name' => 'Shear Walls',
                        'activities' => [
                            [
                                'code' => 'RCC_SHEAR_REBAR',
                                'name' => 'Shear Wall Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['shear wall steel', 'shear wall rebar'],
                            ],
                            [
                                'code' => 'RCC_SHEAR_FORM',
                                'name' => 'Shear Wall Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['shear wall shuttering', 'shear wall formwork'],
                            ],
                            [
                                'code' => 'RCC_SHEAR_CONCRETE',
                                'name' => 'Shear Wall Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['shear wall concrete', 'shear wall casting'],
                            ],
                            [
                                'code' => 'RCC_SHEAR_DESHUTTER',
                                'name' => 'Shear Wall De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['shear wall deshuttering', 'shear wall shutter removal'],
                            ],
                            [
                                'code' => 'RCC_SHEAR_CURING',
                                'name' => 'Shear Wall Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['shear wall curing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-06',
                        'name' => 'Lift Shafts',
                        'activities' => [
                            [
                                'code' => 'RCC_LIFT_REBAR',
                                'name' => 'Lift Shaft Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['lift shaft steel', 'elevator shaft rebar'],
                            ],
                            [
                                'code' => 'RCC_LIFT_FORM',
                                'name' => 'Lift Shaft Shuttering / Formwork',
                                'unit' => 'Sq.m',
                                'aliases' => ['lift shaft shuttering', 'elevator shaft formwork'],
                            ],
                            [
                                'code' => 'RCC_LIFT_CONCRETE',
                                'name' => 'Lift Shaft Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['lift shaft concrete', 'elevator shaft casting'],
                            ],
                            [
                                'code' => 'RCC_LIFT_DESHUTTER',
                                'name' => 'Lift Shaft De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['lift shaft deshuttering', 'lift shutter removal'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-07',
                        'name' => 'Retaining Walls',
                        'activities' => [
                            [
                                'code' => 'RCC_RET_WALL_REBAR',
                                'name' => 'RCC Retaining Wall Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['retaining wall steel', 'retaining wall rebar'],
                            ],
                            [
                                'code' => 'RCC_RET_WALL_FORM',
                                'name' => 'RCC Retaining Wall Shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['retaining wall shuttering', 'retaining wall formwork'],
                            ],
                            [
                                'code' => 'RCC_RET_WALL_CONCRETE',
                                'name' => 'RCC Retaining Wall Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['retaining wall concrete', 'retaining wall casting'],
                            ],
                            [
                                'code' => 'RCC_RET_WALL_DESHUTTER',
                                'name' => 'RCC Retaining Wall De-shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['retaining wall deshuttering', 'retaining wall shutter removal'],
                            ],
                            [
                                'code' => 'RCC_RET_WALL_CURING',
                                'name' => 'RCC Retaining Wall Curing',
                                'unit' => 'Sq.m',
                                'aliases' => ['retaining wall curing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-08',
                        'name' => 'Secondary RCC',
                        'activities' => [
                            [
                                'code' => 'RCC_CHHAJJA_FORM',
                                'name' => 'Chajja / Sunshade Shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['chajja shuttering', 'sunshade shuttering'],
                            ],
                            [
                                'code' => 'RCC_CHHAJJA_REBAR',
                                'name' => 'Chajja / Sunshade Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['chajja steel', 'sunshade reinforcement'],
                            ],
                            [
                                'code' => 'RCC_CHHAJJA_CONCRETE',
                                'name' => 'Chajja / Sunshade Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['chajja concrete', 'sunshade concrete'],
                            ],
                            [
                                'code' => 'RCC_LINTEL_REBAR',
                                'name' => 'RCC Lintel Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['lintel steel', 'lintel reinforcement'],
                            ],
                            [
                                'code' => 'RCC_LINTEL_FORM',
                                'name' => 'RCC Lintel Shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['lintel shuttering', 'lintel formwork'],
                            ],
                            [
                                'code' => 'RCC_LINTEL_CONCRETE',
                                'name' => 'RCC Lintel Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['lintel concrete', 'lintel casting'],
                            ],
                            [
                                'code' => 'RCC_PARAPET_REBAR',
                                'name' => 'RCC Parapet Reinforcement',
                                'unit' => 'Kg',
                                'aliases' => ['parapet steel', 'parapet reinforcement'],
                            ],
                            [
                                'code' => 'RCC_PARAPET_FORM',
                                'name' => 'RCC Parapet Shuttering',
                                'unit' => 'Sq.m',
                                'aliases' => ['parapet shuttering', 'parapet formwork'],
                            ],
                            [
                                'code' => 'RCC_PARAPET_CONCRETE',
                                'name' => 'RCC Parapet Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['parapet concrete', 'parapet casting'],
                            ],
                            [
                                'code' => 'RCC_UPSTAND_CONCRETE',
                                'name' => 'RCC Upstand / Kerb Concreting',
                                'unit' => 'Cum',
                                'aliases' => ['rcc upstand', 'rcc kerb', 'concrete kerb'],
                            ],
                        ],
                    ],
                    [
                        'code' => '07-09',
                        'name' => 'Concrete Support & Rectification',
                        'activities' => [
                            [
                                'code' => 'RCC_CONSTRUCTION_JOINT',
                                'name' => 'Construction Joint Preparation',
                                'unit' => 'Rm',
                                'aliases' => ['construction joint', 'joint preparation', 'old new concrete joint'],
                            ],
                            [
                                'code' => 'RCC_SURFACE_CHIPPING',
                                'name' => 'Concrete Surface Chipping / Roughening',
                                'unit' => 'Sq.m',
                                'aliases' => ['concrete chipping', 'surface hacking', 'rcc hacking', 'roughening concrete'],
                            ],
                            [
                                'code' => 'RCC_HONEYCOMB_REPAIR',
                                'name' => 'Honeycomb Repair',
                                'unit' => 'Sq.m',
                                'aliases' => ['honeycomb repair', 'concrete honeycomb', 'rcc repair'],
                            ],
                            [
                                'code' => 'RCC_CRACK_REPAIR',
                                'name' => 'RCC Crack Repair',
                                'unit' => 'Rm',
                                'aliases' => ['concrete crack repair', 'rcc crack repair', 'crack injection'],
                            ],
                            [
                                'code' => 'RCC_CORE_CUTTING',
                                'name' => 'RCC Core Cutting',
                                'unit' => 'Nos',
                                'aliases' => ['core cutting', 'core hole', 'concrete coring'],
                            ],
                            [
                                'code' => 'RCC_CUTTING_BREAKING',
                                'name' => 'RCC Cutting / Local Breaking',
                                'unit' => 'Cum',
                                'aliases' => ['rcc cutting', 'concrete breaking', 'local demolition'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
