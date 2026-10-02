<?php

namespace Database\Seeders;

use App\Models\WorkActivity;
use App\Models\WorkActivityAlias;
use App\Models\WorkPackage;
use App\Models\WorkSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkExecutionCatalogue2940Seeder extends Seeder
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
                            'description' => null,
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
                                'description' => null,
                                'default_unit' => $activityData['unit'],
                                'allow_materials' => true,
                                'allow_labour' => true,
                                'allow_equipment' => true,
                                'allow_photos' => true,
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
                            $activityData['aliases']
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

            'INTERIOR_FITOUT' => [
                'description' => 'Interior partitions, wall finishes, architectural specialties and fit-out completion works.',
                'sections' => [
                    [
                        'code' => '29-01',
                        'name' => 'Partitions & Linings',
                        'activities' => [
                            [
                                'code' => 'INT_GYPSUM_PART',
                                'name' => 'Gypsum Board Partition Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['gypsum partition', 'drywall partition', 'gypsum wall'],
                            ],
                            [
                                'code' => 'INT_CEMENT_PART',
                                'name' => 'Cement Board Partition Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['cement board partition', 'calcium silicate partition'],
                            ],
                            [
                                'code' => 'INT_GLASS_PART',
                                'name' => 'Internal Glass Partition Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['glass partition', 'office glass partition'],
                            ],
                            [
                                'code' => 'INT_PART_INSUL',
                                'name' => 'Partition Acoustic / Thermal Insulation',
                                'unit' => 'Sq.m',
                                'aliases' => ['partition insulation', 'rockwool in partition'],
                            ],
                        ],
                    ],
                    [
                        'code' => '29-02',
                        'name' => 'Wall Finishes & Features',
                        'activities' => [
                            [
                                'code' => 'INT_WALLPAPER',
                                'name' => 'Wallpaper Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall paper', 'wallpaper fixing'],
                            ],
                            [
                                'code' => 'INT_WALLCOVER',
                                'name' => 'Decorative Wall Covering Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['wall covering', 'decorative wall finish'],
                            ],
                            [
                                'code' => 'INT_FEATURE_WALL',
                                'name' => 'Feature Wall Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['feature wall', 'decorative wall'],
                            ],
                            [
                                'code' => 'INT_CORNER_GUARD',
                                'name' => 'Wall / Corner Guard Installation',
                                'unit' => 'Rm',
                                'aliases' => ['corner guard', 'wall guard', 'hospital wall guard'],
                            ],
                        ],
                    ],
                    [
                        'code' => '29-03',
                        'name' => 'Architectural Specialties',
                        'activities' => [
                            [
                                'code' => 'INT_TOILET_PART',
                                'name' => 'Toilet Cubicle / Partition Installation',
                                'unit' => 'Nos',
                                'aliases' => ['toilet cubicle', 'washroom partition', 'hpl toilet partition'],
                            ],
                            [
                                'code' => 'INT_LOCKER',
                                'name' => 'Locker Installation',
                                'unit' => 'Nos',
                                'aliases' => ['locker fixing', 'staff locker'],
                            ],
                            [
                                'code' => 'INT_SIGNAGE',
                                'name' => 'Internal Signage Installation',
                                'unit' => 'Nos',
                                'aliases' => ['room signage', 'internal signs', 'wayfinding sign'],
                            ],
                            [
                                'code' => 'INT_BLIND',
                                'name' => 'Curtain / Blind Track Installation',
                                'unit' => 'Rm',
                                'aliases' => ['curtain track', 'blind track'],
                            ],
                            [
                                'code' => 'INT_CURTAIN',
                                'name' => 'Curtain / Blind Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['curtain fixing', 'roller blind', 'window blind'],
                            ],
                        ],
                    ],
                    [
                        'code' => '29-04',
                        'name' => 'Raised Platforms & Access',
                        'activities' => [
                            [
                                'code' => 'INT_STAGE_PLATFORM',
                                'name' => 'Internal Raised Platform / Stage Construction',
                                'unit' => 'Sq.m',
                                'aliases' => ['raised platform', 'indoor stage'],
                            ],
                            [
                                'code' => 'INT_ACCESS_FLOOR',
                                'name' => 'Raised Access Floor Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['raised access floor', 'server room raised floor'],
                            ],
                        ],
                    ],
                    [
                        'code' => '29-05',
                        'name' => 'Interior Fit-Out Completion',
                        'activities' => [
                            [
                                'code' => 'INT_SILICONE',
                                'name' => 'Interior Sealant / Silicone Finishing',
                                'unit' => 'Rm',
                                'aliases' => ['interior silicone', 'internal sealant'],
                            ],
                            [
                                'code' => 'INT_PROTECTION',
                                'name' => 'Protection of Finished Interior Works',
                                'unit' => 'Sq.m',
                                'aliases' => ['finish protection', 'floor protection', 'interior protection'],
                            ],
                            [
                                'code' => 'INT_FITOUT_REPAIR',
                                'name' => 'Interior Fit-Out Repair / Adjustment',
                                'unit' => 'Job',
                                'aliases' => ['fitout repair', 'interior rectification'],
                            ],
                        ],
                    ],
                ],
            ],
            'ACOUSTIC_AV' => [
                'description' => 'Acoustic treatments, auditorium, stage, audio-visual and related specialist works.',
                'sections' => [
                    [
                        'code' => '30-01',
                        'name' => 'Acoustic Treatments',
                        'activities' => [
                            [
                                'code' => 'AV_ACOUSTIC_WALL',
                                'name' => 'Acoustic Wall Panel Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['acoustic wall panel', 'sound panel'],
                            ],
                            [
                                'code' => 'AV_ACOUSTIC_CEIL',
                                'name' => 'Acoustic Ceiling Treatment',
                                'unit' => 'Sq.m',
                                'aliases' => ['acoustic ceiling', 'sound ceiling treatment'],
                            ],
                            [
                                'code' => 'AV_ACOUSTIC_DOOR',
                                'name' => 'Acoustic Door Installation',
                                'unit' => 'Nos',
                                'aliases' => ['soundproof door', 'acoustic door'],
                            ],
                            [
                                'code' => 'AV_SOUND_INSUL',
                                'name' => 'Sound Insulation Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['sound insulation', 'acoustic insulation'],
                            ],
                        ],
                    ],
                    [
                        'code' => '30-02',
                        'name' => 'Auditorium & Stage',
                        'activities' => [
                            [
                                'code' => 'AV_STAGE',
                                'name' => 'Auditorium / Stage Platform Construction',
                                'unit' => 'Sq.m',
                                'aliases' => ['auditorium stage', 'stage platform'],
                            ],
                            [
                                'code' => 'AV_STAGE_FLOOR',
                                'name' => 'Stage Flooring Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['stage floor', 'wooden stage flooring'],
                            ],
                            [
                                'code' => 'AV_STAGE_CURTAIN',
                                'name' => 'Stage Curtain / Track Installation',
                                'unit' => 'Set',
                                'aliases' => ['stage curtain', 'auditorium curtain'],
                            ],
                            [
                                'code' => 'AV_AUD_SEATING',
                                'name' => 'Auditorium Fixed Seating Installation',
                                'unit' => 'Nos',
                                'aliases' => ['auditorium chairs', 'theatre seating'],
                            ],
                        ],
                    ],
                    [
                        'code' => '30-03',
                        'name' => 'Audio Visual Systems',
                        'activities' => [
                            [
                                'code' => 'AV_CABLE',
                                'name' => 'AV Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['audio video cable', 'av cabling'],
                            ],
                            [
                                'code' => 'AV_SPEAKER',
                                'name' => 'AV Speaker Installation',
                                'unit' => 'Nos',
                                'aliases' => ['auditorium speaker', 'sound system speaker'],
                            ],
                            [
                                'code' => 'AV_PROJECTOR',
                                'name' => 'Projector Installation',
                                'unit' => 'Nos',
                                'aliases' => ['projector fixing', 'ceiling projector'],
                            ],
                            [
                                'code' => 'AV_SCREEN',
                                'name' => 'Projection / LED Screen Installation',
                                'unit' => 'Nos',
                                'aliases' => ['projector screen', 'led screen'],
                            ],
                            [
                                'code' => 'AV_DISPLAY',
                                'name' => 'Digital Display Installation',
                                'unit' => 'Nos',
                                'aliases' => ['display screen', 'digital signage display'],
                            ],
                            [
                                'code' => 'AV_RACK',
                                'name' => 'AV Equipment Rack Installation',
                                'unit' => 'Nos',
                                'aliases' => ['av rack', 'audio rack'],
                            ],
                        ],
                    ],
                    [
                        'code' => '30-04',
                        'name' => 'Stage Systems',
                        'activities' => [
                            [
                                'code' => 'AV_STAGE_LIGHT',
                                'name' => 'Stage Lighting Installation',
                                'unit' => 'Point',
                                'aliases' => ['stage lights', 'theatre lighting'],
                            ],
                            [
                                'code' => 'AV_RIGGING',
                                'name' => 'Stage Rigging / Support Installation',
                                'unit' => 'Job',
                                'aliases' => ['stage rigging', 'theatre rigging'],
                            ],
                        ],
                    ],
                    [
                        'code' => '30-05',
                        'name' => 'AV Testing',
                        'activities' => [
                            [
                                'code' => 'AV_ACOUSTIC_TEST',
                                'name' => 'Acoustic Performance Testing',
                                'unit' => 'Job',
                                'aliases' => ['acoustic test', 'sound test'],
                            ],
                            [
                                'code' => 'AV_SYSTEM_TEST',
                                'name' => 'AV System Testing & Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['av commissioning', 'sound system testing'],
                            ],
                        ],
                    ],
                ],
            ],
            'EXTERNAL_DEVELOPMENT' => [
                'description' => 'External site infrastructure, boundaries, drainage, structures and amenities.',
                'sections' => [
                    [
                        'code' => '31-01',
                        'name' => 'Site Boundary',
                        'activities' => [
                            [
                                'code' => 'EXT_BOUNDARY_WALL',
                                'name' => 'Boundary Wall Construction',
                                'unit' => 'Rm',
                                'aliases' => ['compound wall', 'boundary wall'],
                            ],
                            [
                                'code' => 'EXT_GATE',
                                'name' => 'Main Gate Installation',
                                'unit' => 'Nos',
                                'aliases' => ['main gate', 'entrance gate'],
                            ],
                            [
                                'code' => 'EXT_FENCE',
                                'name' => 'External Fencing Installation',
                                'unit' => 'Rm',
                                'aliases' => ['site fencing', 'permanent fencing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '31-02',
                        'name' => 'External Drainage',
                        'activities' => [
                            [
                                'code' => 'EXT_STORM_DRAIN',
                                'name' => 'Storm Water Drain Construction',
                                'unit' => 'Rm',
                                'aliases' => ['storm drain', 'rain drain'],
                            ],
                            [
                                'code' => 'EXT_OPEN_DRAIN',
                                'name' => 'Open Drain Construction',
                                'unit' => 'Rm',
                                'aliases' => ['open drain', 'surface drain'],
                            ],
                            [
                                'code' => 'EXT_CATCH_PIT',
                                'name' => 'Catch Pit / Gully Construction',
                                'unit' => 'Nos',
                                'aliases' => ['catch pit', 'storm water chamber'],
                            ],
                            [
                                'code' => 'EXT_CULVERT',
                                'name' => 'Culvert Construction',
                                'unit' => 'Nos',
                                'aliases' => ['culvert work', 'road culvert'],
                            ],
                        ],
                    ],
                    [
                        'code' => '31-03',
                        'name' => 'External Structures',
                        'activities' => [
                            [
                                'code' => 'EXT_GUARD_ROOM',
                                'name' => 'Security / Guard Room Construction',
                                'unit' => 'Sq.m',
                                'aliases' => ['guard room', 'security room'],
                            ],
                            [
                                'code' => 'EXT_COMPOUND_FEATURE',
                                'name' => 'Entrance Feature / Gate Structure Construction',
                                'unit' => 'Job',
                                'aliases' => ['entrance feature', 'gate structure'],
                            ],
                            [
                                'code' => 'EXT_BIN_AREA',
                                'name' => 'Garbage / Waste Collection Area Construction',
                                'unit' => 'Sq.m',
                                'aliases' => ['garbage room', 'waste collection area'],
                            ],
                        ],
                    ],
                    [
                        'code' => '31-04',
                        'name' => 'Site Furniture & Amenities',
                        'activities' => [
                            [
                                'code' => 'EXT_BOLLARD',
                                'name' => 'Bollard Installation',
                                'unit' => 'Nos',
                                'aliases' => ['bollard fixing', 'traffic bollard'],
                            ],
                            [
                                'code' => 'EXT_BENCH',
                                'name' => 'External Bench Installation',
                                'unit' => 'Nos',
                                'aliases' => ['outdoor bench', 'site bench'],
                            ],
                            [
                                'code' => 'EXT_FLAGPOLE',
                                'name' => 'Flag Pole Installation',
                                'unit' => 'Nos',
                                'aliases' => ['flagpole', 'flag mast'],
                            ],
                            [
                                'code' => 'EXT_EXTERNAL_SIGN',
                                'name' => 'External Signage Installation',
                                'unit' => 'Nos',
                                'aliases' => ['external signage', 'site signboard'],
                            ],
                        ],
                    ],
                ],
            ],
            'ROADS_PARKING' => [
                'description' => 'Road formation, paving, hardscape, parking and traffic furniture works.',
                'sections' => [
                    [
                        'code' => '32-01',
                        'name' => 'Road Formation',
                        'activities' => [
                            [
                                'code' => 'ROAD_SUBGRADE',
                                'name' => 'Road Subgrade Preparation',
                                'unit' => 'Sq.m',
                                'aliases' => ['subgrade preparation', 'road formation'],
                            ],
                            [
                                'code' => 'ROAD_GSB',
                                'name' => 'Granular Sub-Base Laying',
                                'unit' => 'Cum',
                                'aliases' => ['gsb', 'granular sub base'],
                            ],
                            [
                                'code' => 'ROAD_WMM',
                                'name' => 'Wet Mix Macadam Laying',
                                'unit' => 'Cum',
                                'aliases' => ['wmm', 'wet mix macadam'],
                            ],
                            [
                                'code' => 'ROAD_BITUMEN',
                                'name' => 'Bituminous Road Laying',
                                'unit' => 'Sq.m',
                                'aliases' => ['asphalt road', 'bitumen road', 'blacktop'],
                            ],
                            [
                                'code' => 'ROAD_CONCRETE',
                                'name' => 'Concrete Road / Pavement Construction',
                                'unit' => 'Sq.m',
                                'aliases' => ['concrete road', 'rcc road', 'rigid pavement'],
                            ],
                        ],
                    ],
                    [
                        'code' => '32-02',
                        'name' => 'Paving & Hardscape',
                        'activities' => [
                            [
                                'code' => 'ROAD_PAVER',
                                'name' => 'Interlocking Paver Block Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['paver blocks', 'interlock paving', 'parking pavers'],
                            ],
                            [
                                'code' => 'ROAD_KERB',
                                'name' => 'Kerb Stone Installation',
                                'unit' => 'Rm',
                                'aliases' => ['kerb stone', 'curb stone'],
                            ],
                            [
                                'code' => 'ROAD_FOOTPATH',
                                'name' => 'Footpath / Walkway Construction',
                                'unit' => 'Sq.m',
                                'aliases' => ['footpath', 'walkway'],
                            ],
                            [
                                'code' => 'ROAD_TACTILE',
                                'name' => 'Tactile Paving Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['tactile tiles', 'blind path'],
                            ],
                        ],
                    ],
                    [
                        'code' => '32-03',
                        'name' => 'Parking Works',
                        'activities' => [
                            [
                                'code' => 'ROAD_PARK_MARK',
                                'name' => 'Parking Bay Marking',
                                'unit' => 'Rm',
                                'aliases' => ['parking marking', 'parking lines'],
                            ],
                            [
                                'code' => 'ROAD_WHEEL_STOP',
                                'name' => 'Wheel Stopper Installation',
                                'unit' => 'Nos',
                                'aliases' => ['wheel stopper', 'parking stopper'],
                            ],
                            [
                                'code' => 'ROAD_SPEED_BREAK',
                                'name' => 'Speed Breaker Installation',
                                'unit' => 'Rm',
                                'aliases' => ['speed breaker', 'speed hump'],
                            ],
                            [
                                'code' => 'ROAD_PARK_SIGN',
                                'name' => 'Parking Signage Installation',
                                'unit' => 'Nos',
                                'aliases' => ['parking sign', 'parking signage'],
                            ],
                        ],
                    ],
                    [
                        'code' => '32-04',
                        'name' => 'Road Furniture',
                        'activities' => [
                            [
                                'code' => 'ROAD_SIGN',
                                'name' => 'Road / Traffic Sign Installation',
                                'unit' => 'Nos',
                                'aliases' => ['traffic sign', 'road sign'],
                            ],
                            [
                                'code' => 'ROAD_MIRROR',
                                'name' => 'Convex Traffic Mirror Installation',
                                'unit' => 'Nos',
                                'aliases' => ['convex mirror', 'traffic mirror'],
                            ],
                            [
                                'code' => 'ROAD_GUARDRAIL',
                                'name' => 'Guardrail / Crash Barrier Installation',
                                'unit' => 'Rm',
                                'aliases' => ['crash barrier', 'road guardrail'],
                            ],
                        ],
                    ],
                ],
            ],
            'LANDSCAPING' => [
                'description' => 'Softscape, landscape preparation, irrigation, features and establishment works.',
                'sections' => [
                    [
                        'code' => '33-01',
                        'name' => 'Landscape Preparation',
                        'activities' => [
                            [
                                'code' => 'LAND_SOIL',
                                'name' => 'Landscape Soil Preparation',
                                'unit' => 'Sq.m',
                                'aliases' => ['garden soil preparation', 'landscape soil'],
                            ],
                            [
                                'code' => 'LAND_TOPSOIL',
                                'name' => 'Topsoil Spreading',
                                'unit' => 'Cum',
                                'aliases' => ['top soil spreading', 'garden soil filling'],
                            ],
                            [
                                'code' => 'LAND_GRADING',
                                'name' => 'Landscape Grading / Levelling',
                                'unit' => 'Sq.m',
                                'aliases' => ['garden leveling', 'landscape grading'],
                            ],
                        ],
                    ],
                    [
                        'code' => '33-02',
                        'name' => 'Softscape',
                        'activities' => [
                            [
                                'code' => 'LAND_GRASS',
                                'name' => 'Lawn / Grass Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['lawn work', 'grass laying', 'turf'],
                            ],
                            [
                                'code' => 'LAND_SHRUB',
                                'name' => 'Shrub Planting',
                                'unit' => 'Nos',
                                'aliases' => ['shrub planting', 'bush planting'],
                            ],
                            [
                                'code' => 'LAND_TREE',
                                'name' => 'Tree Planting',
                                'unit' => 'Nos',
                                'aliases' => ['tree plantation', 'plant tree'],
                            ],
                            [
                                'code' => 'LAND_GROUND_COVER',
                                'name' => 'Ground Cover Planting',
                                'unit' => 'Sq.m',
                                'aliases' => ['ground cover planting'],
                            ],
                            [
                                'code' => 'LAND_PLANTER',
                                'name' => 'Planter Bed Preparation',
                                'unit' => 'Sq.m',
                                'aliases' => ['planter preparation', 'flower bed'],
                            ],
                        ],
                    ],
                    [
                        'code' => '33-03',
                        'name' => 'Irrigation',
                        'activities' => [
                            [
                                'code' => 'LAND_IRR_PIPE',
                                'name' => 'Irrigation Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['irrigation pipe', 'garden water line'],
                            ],
                            [
                                'code' => 'LAND_SPRINKLER',
                                'name' => 'Landscape Sprinkler Installation',
                                'unit' => 'Nos',
                                'aliases' => ['garden sprinkler', 'irrigation sprinkler'],
                            ],
                            [
                                'code' => 'LAND_DRIP',
                                'name' => 'Drip Irrigation Installation',
                                'unit' => 'Rm',
                                'aliases' => ['drip line', 'drip irrigation'],
                            ],
                            [
                                'code' => 'LAND_IRR_VALVE',
                                'name' => 'Irrigation Valve Installation',
                                'unit' => 'Nos',
                                'aliases' => ['irrigation valve', 'garden valve'],
                            ],
                            [
                                'code' => 'LAND_IRR_CTRL',
                                'name' => 'Irrigation Controller Installation',
                                'unit' => 'Nos',
                                'aliases' => ['irrigation controller', 'garden timer'],
                            ],
                        ],
                    ],
                    [
                        'code' => '33-04',
                        'name' => 'Landscape Features',
                        'activities' => [
                            [
                                'code' => 'LAND_EDGING',
                                'name' => 'Landscape Edging Installation',
                                'unit' => 'Rm',
                                'aliases' => ['garden edging', 'lawn edging'],
                            ],
                            [
                                'code' => 'LAND_PERGOLA',
                                'name' => 'Pergola Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['pergola work', 'garden pergola'],
                            ],
                            [
                                'code' => 'LAND_WATER_FEATURE',
                                'name' => 'Water Feature / Fountain Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fountain', 'water feature'],
                            ],
                        ],
                    ],
                    [
                        'code' => '33-05',
                        'name' => 'Landscape Maintenance Establishment',
                        'activities' => [
                            [
                                'code' => 'LAND_WATERING',
                                'name' => 'Initial Landscape Watering / Establishment',
                                'unit' => 'Shift',
                                'aliases' => ['plant watering', 'lawn watering'],
                            ],
                            [
                                'code' => 'LAND_REPLACE',
                                'name' => 'Plant Replacement / Landscape Rectification',
                                'unit' => 'Nos',
                                'aliases' => ['plant replacement', 'landscape repair'],
                            ],
                        ],
                    ],
                ],
            ],
            'WATER_TREATMENT' => [
                'description' => 'STP, WTP, treatment equipment, rainwater systems and commissioning works.',
                'sections' => [
                    [
                        'code' => '34-01',
                        'name' => 'STP Works',
                        'activities' => [
                            [
                                'code' => 'WT_STP_TANK',
                                'name' => 'STP Tank / Civil Interface Work',
                                'unit' => 'Job',
                                'aliases' => ['stp tank', 'sewage treatment tank'],
                            ],
                            [
                                'code' => 'WT_STP_PIPE',
                                'name' => 'STP Process Piping Installation',
                                'unit' => 'Rm',
                                'aliases' => ['stp piping', 'sewage treatment piping'],
                            ],
                            [
                                'code' => 'WT_STP_EQUIP',
                                'name' => 'STP Equipment Installation',
                                'unit' => 'Nos',
                                'aliases' => ['stp equipment', 'sewage treatment equipment'],
                            ],
                            [
                                'code' => 'WT_STP_PANEL',
                                'name' => 'STP Control Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['stp panel', 'sewage treatment panel'],
                            ],
                        ],
                    ],
                    [
                        'code' => '34-02',
                        'name' => 'WTP Works',
                        'activities' => [
                            [
                                'code' => 'WT_WTP_PIPE',
                                'name' => 'WTP Process Piping Installation',
                                'unit' => 'Rm',
                                'aliases' => ['wtp piping', 'water treatment piping'],
                            ],
                            [
                                'code' => 'WT_WTP_EQUIP',
                                'name' => 'WTP Equipment Installation',
                                'unit' => 'Nos',
                                'aliases' => ['wtp equipment', 'water treatment equipment'],
                            ],
                            [
                                'code' => 'WT_WTP_PANEL',
                                'name' => 'WTP Control Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['wtp panel', 'water treatment panel'],
                            ],
                        ],
                    ],
                    [
                        'code' => '34-03',
                        'name' => 'Water Treatment Equipment',
                        'activities' => [
                            [
                                'code' => 'WT_SOFTENER',
                                'name' => 'Water Softener Installation',
                                'unit' => 'Nos',
                                'aliases' => ['water softener', 'softener plant'],
                            ],
                            [
                                'code' => 'WT_FILTER',
                                'name' => 'Water Filtration Unit Installation',
                                'unit' => 'Nos',
                                'aliases' => ['water filter plant', 'filtration unit'],
                            ],
                            [
                                'code' => 'WT_RO',
                                'name' => 'RO Plant Installation',
                                'unit' => 'Nos',
                                'aliases' => ['ro plant', 'reverse osmosis plant'],
                            ],
                            [
                                'code' => 'WT_DOSING',
                                'name' => 'Chemical Dosing System Installation',
                                'unit' => 'Set',
                                'aliases' => ['dosing system', 'chemical dosing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '34-04',
                        'name' => 'Rainwater Systems',
                        'activities' => [
                            [
                                'code' => 'WT_RWH_FILTER',
                                'name' => 'Rainwater Harvesting Filter Installation',
                                'unit' => 'Nos',
                                'aliases' => ['rwh filter', 'rainwater filter'],
                            ],
                            [
                                'code' => 'WT_RECHARGE',
                                'name' => 'Recharge Well / Pit Construction',
                                'unit' => 'Nos',
                                'aliases' => ['recharge well', 'recharge pit'],
                            ],
                            [
                                'code' => 'WT_RAIN_TANK',
                                'name' => 'Rainwater Storage Tank Interface',
                                'unit' => 'Job',
                                'aliases' => ['rainwater tank', 'rwh storage'],
                            ],
                        ],
                    ],
                    [
                        'code' => '34-05',
                        'name' => 'Treatment Testing & Commissioning',
                        'activities' => [
                            [
                                'code' => 'WT_STP_TEST',
                                'name' => 'STP Testing & Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['stp commissioning', 'sewage treatment testing'],
                            ],
                            [
                                'code' => 'WT_WTP_TEST',
                                'name' => 'WTP Testing & Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['wtp commissioning', 'water treatment testing'],
                            ],
                            [
                                'code' => 'WT_WATER_TEST',
                                'name' => 'Treated Water Quality Testing',
                                'unit' => 'Sample',
                                'aliases' => ['water quality test', 'treated water test'],
                            ],
                        ],
                    ],
                ],
            ],
            'UTILITY_CONNECTIONS' => [
                'description' => 'External electrical, water, sewer, gas and telecom utility connection works.',
                'sections' => [
                    [
                        'code' => '35-01',
                        'name' => 'Electrical Utility',
                        'activities' => [
                            [
                                'code' => 'UTIL_ELEC_DUCT',
                                'name' => 'External Electrical Duct / Trench Work',
                                'unit' => 'Rm',
                                'aliases' => ['electrical duct bank', 'cable trench'],
                            ],
                            [
                                'code' => 'UTIL_ELEC_CABLE',
                                'name' => 'External Incoming Power Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['incoming cable', 'utility power cable'],
                            ],
                            [
                                'code' => 'UTIL_TRANSFORMER',
                                'name' => 'Transformer Installation / Utility Interface',
                                'unit' => 'Nos',
                                'aliases' => ['transformer installation', 'electrical transformer'],
                            ],
                            [
                                'code' => 'UTIL_METER',
                                'name' => 'Utility Electrical Metering Installation',
                                'unit' => 'Nos',
                                'aliases' => ['electric meter', 'utility meter'],
                            ],
                        ],
                    ],
                    [
                        'code' => '35-02',
                        'name' => 'Water Utility',
                        'activities' => [
                            [
                                'code' => 'UTIL_WATER_MAIN',
                                'name' => 'External Water Main Connection',
                                'unit' => 'Job',
                                'aliases' => ['water connection', 'municipal water connection'],
                            ],
                            [
                                'code' => 'UTIL_WATER_PIPE',
                                'name' => 'External Water Supply Pipe Laying',
                                'unit' => 'Rm',
                                'aliases' => ['external water pipe', 'utility water line'],
                            ],
                        ],
                    ],
                    [
                        'code' => '35-03',
                        'name' => 'Sewer Utility',
                        'activities' => [
                            [
                                'code' => 'UTIL_SEWER_MAIN',
                                'name' => 'External Sewer Connection',
                                'unit' => 'Job',
                                'aliases' => ['sewer connection', 'municipal sewer connection'],
                            ],
                            [
                                'code' => 'UTIL_SEWER_PIPE',
                                'name' => 'External Sewer Pipe Laying',
                                'unit' => 'Rm',
                                'aliases' => ['external sewer pipe', 'utility sewer'],
                            ],
                        ],
                    ],
                    [
                        'code' => '35-04',
                        'name' => 'Gas Utility',
                        'activities' => [
                            [
                                'code' => 'UTIL_GAS_PIPE',
                                'name' => 'External Gas Pipeline Installation',
                                'unit' => 'Rm',
                                'aliases' => ['gas line', 'external gas pipe'],
                            ],
                            [
                                'code' => 'UTIL_GAS_METER',
                                'name' => 'Gas Meter / Regulator Installation',
                                'unit' => 'Nos',
                                'aliases' => ['gas meter', 'gas regulator'],
                            ],
                        ],
                    ],
                    [
                        'code' => '35-05',
                        'name' => 'Telecom Utility',
                        'activities' => [
                            [
                                'code' => 'UTIL_TELECOM_DUCT',
                                'name' => 'External Telecom Duct Installation',
                                'unit' => 'Rm',
                                'aliases' => ['telecom duct', 'fiber duct'],
                            ],
                            [
                                'code' => 'UTIL_TELECOM_CABLE',
                                'name' => 'External Telecom / Fiber Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['external fiber', 'telecom cable'],
                            ],
                        ],
                    ],
                    [
                        'code' => '35-06',
                        'name' => 'Utility Testing & Coordination',
                        'activities' => [
                            [
                                'code' => 'UTIL_CHAMBER',
                                'name' => 'Utility Chamber Construction',
                                'unit' => 'Nos',
                                'aliases' => ['service chamber', 'utility chamber'],
                            ],
                            [
                                'code' => 'UTIL_TEST',
                                'name' => 'External Utility Testing / Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['utility testing', 'service commissioning'],
                            ],
                        ],
                    ],
                ],
            ],
            'TEMPORARY_WORKS' => [
                'description' => 'Scaffolding, temporary access, edge protection, supports and temporary protection works.',
                'sections' => [
                    [
                        'code' => '36-01',
                        'name' => 'Scaffolding',
                        'activities' => [
                            [
                                'code' => 'TEMP_SCAFF_ERECT',
                                'name' => 'Scaffolding Erection',
                                'unit' => 'Sq.m',
                                'aliases' => ['scaffolding erection', 'scaffold fixing'],
                            ],
                            [
                                'code' => 'TEMP_SCAFF_MODIFY',
                                'name' => 'Scaffolding Modification / Extension',
                                'unit' => 'Sq.m',
                                'aliases' => ['scaffold modification', 'scaffold extension'],
                            ],
                            [
                                'code' => 'TEMP_SCAFF_DISMANTLE',
                                'name' => 'Scaffolding Dismantling',
                                'unit' => 'Sq.m',
                                'aliases' => ['scaffolding removal', 'scaffold dismantling'],
                            ],
                            [
                                'code' => 'TEMP_MOBILE_SCAFF',
                                'name' => 'Mobile Scaffold Setup',
                                'unit' => 'Nos',
                                'aliases' => ['mobile scaffold', 'tower scaffold'],
                            ],
                        ],
                    ],
                    [
                        'code' => '36-02',
                        'name' => 'Working Platforms & Access',
                        'activities' => [
                            [
                                'code' => 'TEMP_PLATFORM',
                                'name' => 'Temporary Working Platform Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['working platform', 'temporary platform'],
                            ],
                            [
                                'code' => 'TEMP_STAIR',
                                'name' => 'Temporary Stair / Access Installation',
                                'unit' => 'Nos',
                                'aliases' => ['temporary staircase', 'site access stair'],
                            ],
                            [
                                'code' => 'TEMP_LADDER',
                                'name' => 'Temporary Ladder / Access Installation',
                                'unit' => 'Nos',
                                'aliases' => ['temporary ladder', 'access ladder'],
                            ],
                            [
                                'code' => 'TEMP_WALKWAY',
                                'name' => 'Temporary Walkway Installation',
                                'unit' => 'Rm',
                                'aliases' => ['temporary walkway', 'site walkway'],
                            ],
                        ],
                    ],
                    [
                        'code' => '36-03',
                        'name' => 'Edge & Opening Protection',
                        'activities' => [
                            [
                                'code' => 'TEMP_EDGE',
                                'name' => 'Temporary Edge Protection Installation',
                                'unit' => 'Rm',
                                'aliases' => ['edge protection', 'temporary railing'],
                            ],
                            [
                                'code' => 'TEMP_OPENING',
                                'name' => 'Floor / Shaft Opening Protection',
                                'unit' => 'Nos',
                                'aliases' => ['opening cover', 'shaft protection', 'floor opening protection'],
                            ],
                            [
                                'code' => 'TEMP_SAFETY_NET',
                                'name' => 'Safety Net Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['safety net', 'construction safety net'],
                            ],
                        ],
                    ],
                    [
                        'code' => '36-04',
                        'name' => 'Temporary Supports',
                        'activities' => [
                            [
                                'code' => 'TEMP_PROPPING',
                                'name' => 'Temporary Propping / Shoring',
                                'unit' => 'Nos',
                                'aliases' => ['propping', 'temporary props', 'shoring support'],
                            ],
                            [
                                'code' => 'TEMP_BRACING',
                                'name' => 'Temporary Bracing Installation',
                                'unit' => 'Job',
                                'aliases' => ['temporary bracing', 'support bracing'],
                            ],
                            [
                                'code' => 'TEMP_FORM_SUPPORT',
                                'name' => 'Formwork Support / Re-shoring',
                                'unit' => 'Sq.m',
                                'aliases' => ['reshoring', 're shoring', 'formwork support'],
                            ],
                        ],
                    ],
                    [
                        'code' => '36-05',
                        'name' => 'Temporary Protection',
                        'activities' => [
                            [
                                'code' => 'TEMP_WEATHER',
                                'name' => 'Temporary Weather Protection',
                                'unit' => 'Sq.m',
                                'aliases' => ['rain protection', 'temporary covering', 'tarpaulin cover'],
                            ],
                            [
                                'code' => 'TEMP_DUST',
                                'name' => 'Temporary Dust Protection / Screening',
                                'unit' => 'Sq.m',
                                'aliases' => ['dust screen', 'dust protection'],
                            ],
                            [
                                'code' => 'TEMP_FINISH_PROTECT',
                                'name' => 'Temporary Protection to Finished Work',
                                'unit' => 'Sq.m',
                                'aliases' => ['finished work protection', 'tile protection'],
                            ],
                        ],
                    ],
                ],
            ],
            'SITE_OPERATIONS' => [
                'description' => 'Daily site support including material handling, housekeeping, dewatering and workfront preparation.',
                'sections' => [
                    [
                        'code' => '37-01',
                        'name' => 'Material Handling',
                        'activities' => [
                            [
                                'code' => 'SITE_UNLOAD',
                                'name' => 'Material Unloading',
                                'unit' => 'Load',
                                'aliases' => ['material unloading', 'unloading material', 'lorry unloading'],
                            ],
                            [
                                'code' => 'SITE_LOAD',
                                'name' => 'Material Loading',
                                'unit' => 'Load',
                                'aliases' => ['material loading', 'loading material'],
                            ],
                            [
                                'code' => 'SITE_SHIFT',
                                'name' => 'Internal Material Shifting',
                                'unit' => 'Load',
                                'aliases' => ['material shifting', 'site material shifting', 'internal shifting'],
                            ],
                            [
                                'code' => 'SITE_FLOOR_SHIFT',
                                'name' => 'Floor-wise / Vertical Material Shifting',
                                'unit' => 'Load',
                                'aliases' => ['floor material shifting', 'vertical shifting', 'material lifting to floor'],
                            ],
                            [
                                'code' => 'SITE_STACK',
                                'name' => 'Material Stacking / Arrangement',
                                'unit' => 'Load',
                                'aliases' => ['material stacking', 'stacking material', 'material arrangement'],
                            ],
                            [
                                'code' => 'SITE_RESTACK',
                                'name' => 'Material Re-stacking / Relocation',
                                'unit' => 'Load',
                                'aliases' => ['restacking', 'material relocation', 'shift stack'],
                            ],
                        ],
                    ],
                    [
                        'code' => '37-02',
                        'name' => 'Housekeeping & Cleaning',
                        'activities' => [
                            [
                                'code' => 'SITE_HOUSEKEEP',
                                'name' => 'General Site Housekeeping',
                                'unit' => 'Shift',
                                'aliases' => ['site housekeeping', 'general cleaning', 'site cleaning'],
                            ],
                            [
                                'code' => 'SITE_FLOOR_CLEAN',
                                'name' => 'Floor / Work Area Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['floor cleaning', 'work area cleaning'],
                            ],
                            [
                                'code' => 'SITE_DEBRIS_COLLECT',
                                'name' => 'Construction Debris Collection',
                                'unit' => 'Load',
                                'aliases' => ['debris collection', 'waste collection'],
                            ],
                            [
                                'code' => 'SITE_DEBRIS_SHIFT',
                                'name' => 'Internal Debris Shifting',
                                'unit' => 'Load',
                                'aliases' => ['debris shifting', 'debris removal internal'],
                            ],
                            [
                                'code' => 'SITE_DUST_CLEAN',
                                'name' => 'Dust / Loose Material Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['dust cleaning', 'loose material cleaning'],
                            ],
                        ],
                    ],
                    [
                        'code' => '37-03',
                        'name' => 'Water & Dewatering Support',
                        'activities' => [
                            [
                                'code' => 'SITE_WATER_REMOVE',
                                'name' => 'Standing Water Removal',
                                'unit' => 'Hour',
                                'aliases' => ['water removal', 'remove standing water', 'water pumping'],
                            ],
                            [
                                'code' => 'SITE_DEWATER_SUPPORT',
                                'name' => 'General Dewatering Support',
                                'unit' => 'Hour',
                                'aliases' => ['dewatering support', 'site dewatering'],
                            ],
                            [
                                'code' => 'SITE_WATER_SPRAY',
                                'name' => 'Water Sprinkling for Dust Control',
                                'unit' => 'Hour',
                                'aliases' => ['water sprinkling', 'dust suppression', 'dust control watering'],
                            ],
                        ],
                    ],
                    [
                        'code' => '37-04',
                        'name' => 'Work Area Preparation',
                        'activities' => [
                            [
                                'code' => 'SITE_AREA_PREP',
                                'name' => 'Work Area Preparation',
                                'unit' => 'Sq.m',
                                'aliases' => ['area preparation', 'workfront preparation', 'work front preparation'],
                            ],
                            [
                                'code' => 'SITE_AREA_CLEAR',
                                'name' => 'Workfront Clearing',
                                'unit' => 'Sq.m',
                                'aliases' => ['workfront clearing', 'clear work area'],
                            ],
                            [
                                'code' => 'SITE_MARK_BARRICADE',
                                'name' => 'Temporary Work Area Barricading',
                                'unit' => 'Rm',
                                'aliases' => ['work area barricade', 'temporary barricading'],
                            ],
                            [
                                'code' => 'SITE_TEMP_COVER',
                                'name' => 'Temporary Covering / Protection',
                                'unit' => 'Sq.m',
                                'aliases' => ['temporary cover', 'cover materials', 'temporary protection'],
                            ],
                        ],
                    ],
                    [
                        'code' => '37-05',
                        'name' => 'Site Support Activities',
                        'activities' => [
                            [
                                'code' => 'SITE_GENERAL_LABOUR',
                                'name' => 'General Labour Support Work',
                                'unit' => 'Shift',
                                'aliases' => ['general labour', 'helper work', 'labour support'],
                            ],
                            [
                                'code' => 'SITE_EQUIP_MOVE',
                                'name' => 'Small Equipment / Tool Shifting',
                                'unit' => 'Load',
                                'aliases' => ['equipment shifting', 'tool shifting', 'machine shifting'],
                            ],
                            [
                                'code' => 'SITE_WASTE_SEG',
                                'name' => 'Waste Segregation',
                                'unit' => 'Load',
                                'aliases' => ['waste segregation', 'debris segregation'],
                            ],
                            [
                                'code' => 'SITE_SCRAP_SHIFT',
                                'name' => 'Scrap Collection / Shifting',
                                'unit' => 'Load',
                                'aliases' => ['scrap shifting', 'scrap collection'],
                            ],
                            [
                                'code' => 'SITE_MATERIAL_COVER',
                                'name' => 'Material Covering / Weather Protection',
                                'unit' => 'Sq.m',
                                'aliases' => ['material covering', 'cement cover', 'weather protection material'],
                            ],
                        ],
                    ],
                    [
                        'code' => '37-06',
                        'name' => 'Temporary Site Services Support',
                        'activities' => [
                            [
                                'code' => 'SITE_TEMP_LIGHT',
                                'name' => 'Temporary Work Lighting Setup / Relocation',
                                'unit' => 'Point',
                                'aliases' => ['temporary lighting', 'work light shifting'],
                            ],
                            [
                                'code' => 'SITE_TEMP_POWER',
                                'name' => 'Temporary Power Point Setup / Relocation',
                                'unit' => 'Point',
                                'aliases' => ['temporary power', 'site power point'],
                            ],
                            [
                                'code' => 'SITE_TEMP_WATER',
                                'name' => 'Temporary Water Point / Hose Setup',
                                'unit' => 'Point',
                                'aliases' => ['temporary water point', 'site water hose'],
                            ],
                        ],
                    ],
                ],
            ],
            'TESTING_COMMISSIONING' => [
                'description' => 'Cross-system inspections, tests, commissioning and statutory witnessing activities.',
                'sections' => [
                    [
                        'code' => '38-01',
                        'name' => 'General Inspections',
                        'activities' => [
                            [
                                'code' => 'TC_VISUAL',
                                'name' => 'General Installation Visual Inspection',
                                'unit' => 'Item',
                                'aliases' => ['visual inspection', 'installation inspection'],
                            ],
                            [
                                'code' => 'TC_DIMENSION',
                                'name' => 'Dimension / Alignment Inspection',
                                'unit' => 'Item',
                                'aliases' => ['alignment check', 'dimension check'],
                            ],
                            [
                                'code' => 'TC_PRECOM',
                                'name' => 'Pre-Commissioning Inspection',
                                'unit' => 'System',
                                'aliases' => ['pre commissioning', 'precommissioning inspection'],
                            ],
                        ],
                    ],
                    [
                        'code' => '38-02',
                        'name' => 'Pressure & Leakage Tests',
                        'activities' => [
                            [
                                'code' => 'TC_HYDRO',
                                'name' => 'General Hydrostatic Pressure Test',
                                'unit' => 'System',
                                'aliases' => ['hydro test', 'hydrostatic test'],
                            ],
                            [
                                'code' => 'TC_LEAK',
                                'name' => 'General Leakage Test',
                                'unit' => 'System',
                                'aliases' => ['leak test', 'leakage testing'],
                            ],
                            [
                                'code' => 'TC_AIR_PRESS',
                                'name' => 'Air / Pneumatic Pressure Test',
                                'unit' => 'System',
                                'aliases' => ['air pressure test', 'pneumatic test'],
                            ],
                        ],
                    ],
                    [
                        'code' => '38-03',
                        'name' => 'Electrical Tests',
                        'activities' => [
                            [
                                'code' => 'TC_ELEC_IR',
                                'name' => 'Electrical Insulation Resistance Test',
                                'unit' => 'Circuit',
                                'aliases' => ['insulation resistance test', 'megger testing'],
                            ],
                            [
                                'code' => 'TC_ELEC_EARTH',
                                'name' => 'Electrical Earthing Test',
                                'unit' => 'Point',
                                'aliases' => ['earthing test', 'earth resistance test'],
                            ],
                            [
                                'code' => 'TC_ELEC_FUNC',
                                'name' => 'Electrical Functional Test',
                                'unit' => 'System',
                                'aliases' => ['electrical functional test', 'power functional test'],
                            ],
                        ],
                    ],
                    [
                        'code' => '38-04',
                        'name' => 'Functional & Performance Tests',
                        'activities' => [
                            [
                                'code' => 'TC_FUNC',
                                'name' => 'System Functional Testing',
                                'unit' => 'System',
                                'aliases' => ['functional testing', 'system function test'],
                            ],
                            [
                                'code' => 'TC_PERFORMANCE',
                                'name' => 'System Performance Testing',
                                'unit' => 'System',
                                'aliases' => ['performance test', 'system performance'],
                            ],
                            [
                                'code' => 'TC_INTEGRATED',
                                'name' => 'Integrated Systems Testing',
                                'unit' => 'Job',
                                'aliases' => ['integrated testing', 'system integration test'],
                            ],
                        ],
                    ],
                    [
                        'code' => '38-05',
                        'name' => 'Commissioning',
                        'activities' => [
                            [
                                'code' => 'TC_STARTUP',
                                'name' => 'Equipment Start-up / Trial Run',
                                'unit' => 'Nos',
                                'aliases' => ['trial run', 'equipment startup', 'machine trial'],
                            ],
                            [
                                'code' => 'TC_COMMISSION',
                                'name' => 'System Commissioning',
                                'unit' => 'System',
                                'aliases' => ['commissioning', 'system commissioning'],
                            ],
                            [
                                'code' => 'TC_BALANCE',
                                'name' => 'System Balancing / Adjustment',
                                'unit' => 'System',
                                'aliases' => ['balancing', 'system adjustment'],
                            ],
                        ],
                    ],
                    [
                        'code' => '38-06',
                        'name' => 'Third Party & Statutory',
                        'activities' => [
                            [
                                'code' => 'TC_THIRD_PARTY',
                                'name' => 'Third-Party Inspection',
                                'unit' => 'Job',
                                'aliases' => ['third party inspection', 'tpi'],
                            ],
                            [
                                'code' => 'TC_AUTHORITY',
                                'name' => 'Authority / Statutory Inspection',
                                'unit' => 'Job',
                                'aliases' => ['authority inspection', 'statutory inspection'],
                            ],
                            [
                                'code' => 'TC_WITNESS',
                                'name' => 'Client / Consultant Witness Test',
                                'unit' => 'Job',
                                'aliases' => ['witness test', 'consultant inspection'],
                            ],
                        ],
                    ],
                ],
            ],
            'RECTIFICATION' => [
                'description' => 'Snagging, defect rectification, rework, replacement and re-testing activities.',
                'sections' => [
                    [
                        'code' => '39-01',
                        'name' => 'Snagging',
                        'activities' => [
                            [
                                'code' => 'RECT_SNAG_SURVEY',
                                'name' => 'Snag / Punch List Inspection',
                                'unit' => 'Area',
                                'aliases' => ['snagging', 'snag inspection', 'punch list'],
                            ],
                            [
                                'code' => 'RECT_SNAG_MARK',
                                'name' => 'Snag Marking / Identification',
                                'unit' => 'Area',
                                'aliases' => ['snag marking', 'defect marking'],
                            ],
                        ],
                    ],
                    [
                        'code' => '39-02',
                        'name' => 'Civil Rectification',
                        'activities' => [
                            [
                                'code' => 'RECT_CONCRETE',
                                'name' => 'Concrete Rectification',
                                'unit' => 'Sq.m',
                                'aliases' => ['concrete repair', 'rcc rectification'],
                            ],
                            [
                                'code' => 'RECT_MASONRY',
                                'name' => 'Masonry Rectification',
                                'unit' => 'Sq.m',
                                'aliases' => ['masonry repair', 'brickwork rectification'],
                            ],
                            [
                                'code' => 'RECT_PLASTER',
                                'name' => 'Plaster Rectification',
                                'unit' => 'Sq.m',
                                'aliases' => ['plaster repair', 'plaster patch'],
                            ],
                            [
                                'code' => 'RECT_WP',
                                'name' => 'Waterproofing Rectification',
                                'unit' => 'Sq.m',
                                'aliases' => ['waterproof repair', 'leak rectification'],
                            ],
                        ],
                    ],
                    [
                        'code' => '39-03',
                        'name' => 'Finishing Rectification',
                        'activities' => [
                            [
                                'code' => 'RECT_TILE',
                                'name' => 'Tile / Stone Rectification',
                                'unit' => 'Sq.m',
                                'aliases' => ['tile repair', 'stone rectification'],
                            ],
                            [
                                'code' => 'RECT_PAINT',
                                'name' => 'Paint Rectification / Touch-up',
                                'unit' => 'Sq.m',
                                'aliases' => ['paint touchup', 'painting repair'],
                            ],
                            [
                                'code' => 'RECT_CEIL',
                                'name' => 'Ceiling Rectification',
                                'unit' => 'Sq.m',
                                'aliases' => ['false ceiling repair', 'ceiling repair'],
                            ],
                            [
                                'code' => 'RECT_JOINERY',
                                'name' => 'Door / Joinery Rectification',
                                'unit' => 'Nos',
                                'aliases' => ['door adjustment', 'joinery repair'],
                            ],
                            [
                                'code' => 'RECT_GLASS',
                                'name' => 'Glass / Glazing Rectification',
                                'unit' => 'Sq.m',
                                'aliases' => ['glass repair', 'glazing rectification'],
                            ],
                        ],
                    ],
                    [
                        'code' => '39-04',
                        'name' => 'MEP Rectification',
                        'activities' => [
                            [
                                'code' => 'RECT_ELEC',
                                'name' => 'Electrical Rectification',
                                'unit' => 'Point',
                                'aliases' => ['electrical repair', 'electrical snag'],
                            ],
                            [
                                'code' => 'RECT_PLUMB',
                                'name' => 'Plumbing Rectification',
                                'unit' => 'Point',
                                'aliases' => ['plumbing repair', 'plumbing snag'],
                            ],
                            [
                                'code' => 'RECT_HVAC',
                                'name' => 'HVAC Rectification',
                                'unit' => 'Point',
                                'aliases' => ['hvac repair', 'ac snag'],
                            ],
                            [
                                'code' => 'RECT_FIRE',
                                'name' => 'Fire System Rectification',
                                'unit' => 'Point',
                                'aliases' => ['fire system repair', 'fire snag'],
                            ],
                            [
                                'code' => 'RECT_ELV',
                                'name' => 'ELV / ICT Rectification',
                                'unit' => 'Point',
                                'aliases' => ['elv repair', 'cctv data snag'],
                            ],
                        ],
                    ],
                    [
                        'code' => '39-05',
                        'name' => 'Rework & Replacement',
                        'activities' => [
                            [
                                'code' => 'RECT_REWORK',
                                'name' => 'General Rework',
                                'unit' => 'Job',
                                'aliases' => ['rework', 'redo work', 'general rectification'],
                            ],
                            [
                                'code' => 'RECT_REPLACE',
                                'name' => 'Defective Item Replacement',
                                'unit' => 'Nos',
                                'aliases' => ['item replacement', 'replace defective item'],
                            ],
                            [
                                'code' => 'RECT_RETEST',
                                'name' => 'Re-testing After Rectification',
                                'unit' => 'System',
                                'aliases' => ['retest', 're testing after repair'],
                            ],
                        ],
                    ],
                ],
            ],
            'HANDOVER' => [
                'description' => 'Construction cleaning, final inspection, documentation, area handover and project closeout.',
                'sections' => [
                    [
                        'code' => '40-01',
                        'name' => 'Construction Cleaning',
                        'activities' => [
                            [
                                'code' => 'HAND_ROUGH_CLEAN',
                                'name' => 'Rough Construction Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['rough cleaning', 'construction cleaning'],
                            ],
                            [
                                'code' => 'HAND_DEEP_CLEAN',
                                'name' => 'Deep Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['deep cleaning', 'final deep clean'],
                            ],
                            [
                                'code' => 'HAND_FLOOR_CLEAN',
                                'name' => 'Final Floor Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['final floor cleaning', 'tile cleaning'],
                            ],
                            [
                                'code' => 'HAND_GLASS_CLEAN',
                                'name' => 'Glass / Façade Cleaning',
                                'unit' => 'Sq.m',
                                'aliases' => ['glass cleaning', 'facade cleaning'],
                            ],
                            [
                                'code' => 'HAND_FIXTURE_CLEAN',
                                'name' => 'Fixture / Equipment Cleaning',
                                'unit' => 'Nos',
                                'aliases' => ['fixture cleaning', 'equipment cleaning'],
                            ],
                            [
                                'code' => 'HAND_DEBRIS_FINAL',
                                'name' => 'Final Debris / Waste Removal',
                                'unit' => 'Load',
                                'aliases' => ['final debris removal', 'final waste removal'],
                            ],
                        ],
                    ],
                    [
                        'code' => '40-02',
                        'name' => 'Protection Removal',
                        'activities' => [
                            [
                                'code' => 'HAND_PROTECT_REMOVE',
                                'name' => 'Removal of Temporary Finish Protection',
                                'unit' => 'Sq.m',
                                'aliases' => ['remove floor protection', 'protection removal'],
                            ],
                            [
                                'code' => 'HAND_LABEL_REMOVE',
                                'name' => 'Temporary Label / Sticker Removal',
                                'unit' => 'Nos',
                                'aliases' => ['sticker removal', 'label cleaning'],
                            ],
                        ],
                    ],
                    [
                        'code' => '40-03',
                        'name' => 'Handover Inspection',
                        'activities' => [
                            [
                                'code' => 'HAND_PREINSPECT',
                                'name' => 'Pre-Handover Inspection',
                                'unit' => 'Area',
                                'aliases' => ['pre handover inspection', 'handover check'],
                            ],
                            [
                                'code' => 'HAND_FINAL_INSPECT',
                                'name' => 'Final Handover Inspection',
                                'unit' => 'Area',
                                'aliases' => ['final inspection', 'handover inspection'],
                            ],
                            [
                                'code' => 'HAND_DEMO',
                                'name' => 'Client / Operator Demonstration',
                                'unit' => 'System',
                                'aliases' => ['system demonstration', 'operator demo', 'client training'],
                            ],
                        ],
                    ],
                    [
                        'code' => '40-04',
                        'name' => 'Documentation & Closeout',
                        'activities' => [
                            [
                                'code' => 'HAND_ASBUILT',
                                'name' => 'As-Built Drawing Verification / Closeout',
                                'unit' => 'Job',
                                'aliases' => ['as built verification', 'asbuilt closeout'],
                            ],
                            [
                                'code' => 'HAND_OM',
                                'name' => 'O&M Manual / Document Handover',
                                'unit' => 'Job',
                                'aliases' => ['om manual', 'operation maintenance manual', 'document handover'],
                            ],
                            [
                                'code' => 'HAND_KEYS',
                                'name' => 'Keys / Access Credential Handover',
                                'unit' => 'Job',
                                'aliases' => ['key handover', 'access card handover'],
                            ],
                            [
                                'code' => 'HAND_SPARES',
                                'name' => 'Spares / Consumables Handover',
                                'unit' => 'Job',
                                'aliases' => ['spares handover', 'consumables handover'],
                            ],
                        ],
                    ],
                    [
                        'code' => '40-05',
                        'name' => 'Area Handover',
                        'activities' => [
                            [
                                'code' => 'HAND_ROOM',
                                'name' => 'Room / Unit Handover',
                                'unit' => 'Nos',
                                'aliases' => ['room handover', 'flat handover', 'unit handover'],
                            ],
                            [
                                'code' => 'HAND_FLOOR',
                                'name' => 'Floor / Zone Handover',
                                'unit' => 'Area',
                                'aliases' => ['floor handover', 'zone handover'],
                            ],
                            [
                                'code' => 'HAND_SYSTEM',
                                'name' => 'System Handover',
                                'unit' => 'System',
                                'aliases' => ['system handover', 'mep handover'],
                            ],
                            [
                                'code' => 'HAND_PROJECT',
                                'name' => 'Project Final Handover',
                                'unit' => 'Job',
                                'aliases' => ['project handover', 'final handover'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
