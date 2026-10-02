<?php

namespace Database\Seeders;

use App\Models\WorkActivity;
use App\Models\WorkActivityAlias;
use App\Models\WorkPackage;
use App\Models\WorkSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkExecutionCatalogue1928Seeder extends Seeder
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

            'ELECTRICAL' => [
                'description' => 'Electrical containment, wiring, cabling, distribution, lighting, earthing, backup power and testing works.',
                'sections' => [
                    [
                        'code' => '19-01',
                        'name' => 'Conduits & Boxes',
                        'activities' => [
                            [
                                'code' => 'ELEC_CONDUIT_SLAB',
                                'name' => 'Electrical Conduit in RCC Slab',
                                'unit' => 'Rm',
                                'aliases' => ['slab conduit', 'electrical conduit slab', 'conduit in slab'],
                            ],
                            [
                                'code' => 'ELEC_CONDUIT_WALL',
                                'name' => 'Concealed Wall Conduit Installation',
                                'unit' => 'Rm',
                                'aliases' => ['wall conduit', 'concealed conduit', 'electrical pipe in wall'],
                            ],
                            [
                                'code' => 'ELEC_CONDUIT_EXPOSED',
                                'name' => 'Exposed Conduit Installation',
                                'unit' => 'Rm',
                                'aliases' => ['surface conduit', 'exposed electrical conduit'],
                            ],
                            [
                                'code' => 'ELEC_BOX',
                                'name' => 'Electrical Box / Junction Box Installation',
                                'unit' => 'Nos',
                                'aliases' => ['switch box', 'junction box', 'electrical box'],
                            ],
                            [
                                'code' => 'ELEC_FLOOR_BOX',
                                'name' => 'Floor Box Installation',
                                'unit' => 'Nos',
                                'aliases' => ['floor box', 'floor electrical box'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-02',
                        'name' => 'Cable Containment',
                        'activities' => [
                            [
                                'code' => 'ELEC_TRAY',
                                'name' => 'Cable Tray Installation',
                                'unit' => 'Rm',
                                'aliases' => ['cable tray', 'tray work'],
                            ],
                            [
                                'code' => 'ELEC_LADDER',
                                'name' => 'Cable Ladder Installation',
                                'unit' => 'Rm',
                                'aliases' => ['cable ladder', 'ladder tray'],
                            ],
                            [
                                'code' => 'ELEC_TRUNKING',
                                'name' => 'Electrical Trunking Installation',
                                'unit' => 'Rm',
                                'aliases' => ['trunking', 'electrical trunking'],
                            ],
                            [
                                'code' => 'ELEC_BUSDUCT',
                                'name' => 'Busduct / Busbar Trunking Installation',
                                'unit' => 'Rm',
                                'aliases' => ['bus duct', 'busbar trunking', 'busduct'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-03',
                        'name' => 'Wiring & Cabling',
                        'activities' => [
                            [
                                'code' => 'ELEC_WIRE',
                                'name' => 'Internal Electrical Wiring',
                                'unit' => 'Rm',
                                'aliases' => ['house wiring', 'electrical wiring', 'wire pulling'],
                            ],
                            [
                                'code' => 'ELEC_POWER_CABLE',
                                'name' => 'Power Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['power cable', 'cable laying', 'lt cable laying'],
                            ],
                            [
                                'code' => 'ELEC_CONTROL_CABLE',
                                'name' => 'Control Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['control cable', 'control wiring'],
                            ],
                            [
                                'code' => 'ELEC_CABLE_TERM',
                                'name' => 'Cable Termination',
                                'unit' => 'Nos',
                                'aliases' => ['cable termination', 'glanding termination'],
                            ],
                            [
                                'code' => 'ELEC_CABLE_GLAND',
                                'name' => 'Cable Glanding',
                                'unit' => 'Nos',
                                'aliases' => ['cable gland', 'glanding'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-04',
                        'name' => 'Panels & Distribution',
                        'activities' => [
                            [
                                'code' => 'ELEC_DB',
                                'name' => 'Distribution Board Installation',
                                'unit' => 'Nos',
                                'aliases' => ['db installation', 'distribution board', 'electrical db'],
                            ],
                            [
                                'code' => 'ELEC_PANEL',
                                'name' => 'Electrical Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['lt panel', 'electrical panel', 'power panel'],
                            ],
                            [
                                'code' => 'ELEC_MCC',
                                'name' => 'MCC Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['mcc panel', 'motor control center'],
                            ],
                            [
                                'code' => 'ELEC_APFC',
                                'name' => 'APFC Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['apfc panel', 'power factor panel'],
                            ],
                            [
                                'code' => 'ELEC_CHANGEOVER',
                                'name' => 'Changeover / ATS Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['ats panel', 'changeover panel'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-05',
                        'name' => 'Points & Accessories',
                        'activities' => [
                            [
                                'code' => 'ELEC_SWITCH',
                                'name' => 'Switch Installation',
                                'unit' => 'Nos',
                                'aliases' => ['switch fixing', 'electrical switch'],
                            ],
                            [
                                'code' => 'ELEC_SOCKET',
                                'name' => 'Socket Outlet Installation',
                                'unit' => 'Nos',
                                'aliases' => ['socket fixing', 'plug point', 'power socket'],
                            ],
                            [
                                'code' => 'ELEC_POWER_POINT',
                                'name' => 'Dedicated Power Point Installation',
                                'unit' => 'Nos',
                                'aliases' => ['power point', 'equipment power point'],
                            ],
                            [
                                'code' => 'ELEC_ISOLATOR',
                                'name' => 'Local Isolator Installation',
                                'unit' => 'Nos',
                                'aliases' => ['isolator fixing', 'electrical isolator'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-06',
                        'name' => 'Lighting',
                        'activities' => [
                            [
                                'code' => 'ELEC_LIGHT',
                                'name' => 'Light Fixture Installation',
                                'unit' => 'Nos',
                                'aliases' => ['light fitting', 'lighting fixture', 'light installation'],
                            ],
                            [
                                'code' => 'ELEC_EMERGENCY_LIGHT',
                                'name' => 'Emergency Light Installation',
                                'unit' => 'Nos',
                                'aliases' => ['emergency light', 'emergency lighting'],
                            ],
                            [
                                'code' => 'ELEC_EXIT_LIGHT',
                                'name' => 'Exit Sign Installation',
                                'unit' => 'Nos',
                                'aliases' => ['exit light', 'exit sign'],
                            ],
                            [
                                'code' => 'ELEC_EXTERNAL_LIGHT',
                                'name' => 'External / Landscape Light Installation',
                                'unit' => 'Nos',
                                'aliases' => ['outdoor light', 'external lighting', 'landscape light'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-07',
                        'name' => 'Earthing & Lightning Protection',
                        'activities' => [
                            [
                                'code' => 'ELEC_EARTH_PIT',
                                'name' => 'Earthing Pit Installation',
                                'unit' => 'Nos',
                                'aliases' => ['earth pit', 'earthing pit'],
                            ],
                            [
                                'code' => 'ELEC_EARTH_STRIP',
                                'name' => 'Earthing Strip / Conductor Installation',
                                'unit' => 'Rm',
                                'aliases' => ['earth strip', 'earthing conductor'],
                            ],
                            [
                                'code' => 'ELEC_EQUIP_EARTH',
                                'name' => 'Equipment Earthing Connection',
                                'unit' => 'Nos',
                                'aliases' => ['equipment earthing', 'body earthing'],
                            ],
                            [
                                'code' => 'ELEC_LIGHTNING',
                                'name' => 'Lightning Protection System Installation',
                                'unit' => 'Rm',
                                'aliases' => ['lightning arrester', 'lightning protection', 'down conductor'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-08',
                        'name' => 'Backup & Power Systems',
                        'activities' => [
                            [
                                'code' => 'ELEC_DG',
                                'name' => 'DG Set Installation / Electrical Interface',
                                'unit' => 'Nos',
                                'aliases' => ['generator installation', 'dg set', 'diesel generator'],
                            ],
                            [
                                'code' => 'ELEC_UPS',
                                'name' => 'UPS Installation',
                                'unit' => 'Nos',
                                'aliases' => ['ups installation', 'uninterruptible power supply'],
                            ],
                            [
                                'code' => 'ELEC_INVERTER',
                                'name' => 'Inverter / Battery System Installation',
                                'unit' => 'Nos',
                                'aliases' => ['inverter installation', 'battery bank'],
                            ],
                            [
                                'code' => 'ELEC_SOLAR_INTERFACE',
                                'name' => 'Solar Electrical Interface / AC-DC Cabling',
                                'unit' => 'Job',
                                'aliases' => ['solar cabling', 'solar electrical work', 'pv electrical'],
                            ],
                        ],
                    ],
                    [
                        'code' => '19-09',
                        'name' => 'Electrical Testing',
                        'activities' => [
                            [
                                'code' => 'ELEC_IR_TEST',
                                'name' => 'Insulation Resistance Testing',
                                'unit' => 'Circuit',
                                'aliases' => ['megger test', 'ir test', 'insulation test'],
                            ],
                            [
                                'code' => 'ELEC_CONTINUITY',
                                'name' => 'Electrical Continuity Testing',
                                'unit' => 'Circuit',
                                'aliases' => ['continuity test', 'earth continuity'],
                            ],
                            [
                                'code' => 'ELEC_EARTH_TEST',
                                'name' => 'Earth Resistance Testing',
                                'unit' => 'Nos',
                                'aliases' => ['earth resistance', 'earthing test'],
                            ],
                            [
                                'code' => 'ELEC_PANEL_TEST',
                                'name' => 'Panel Testing & Energization',
                                'unit' => 'Nos',
                                'aliases' => ['panel testing', 'panel energization'],
                            ],
                            [
                                'code' => 'ELEC_CIRCUIT_TEST',
                                'name' => 'Circuit Testing & Energization',
                                'unit' => 'Circuit',
                                'aliases' => ['circuit test', 'circuit energization'],
                            ],
                        ],
                    ],
                ],
            ],
            'ELV_ICT' => [
                'description' => 'ELV, data, telecom, CCTV, access control, public address, automation, BMS and related testing works.',
                'sections' => [
                    [
                        'code' => '20-01',
                        'name' => 'Data & Telecom',
                        'activities' => [
                            [
                                'code' => 'ELV_DATA_CABLE',
                                'name' => 'Data / LAN Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['lan cable', 'data cable', 'network cable'],
                            ],
                            [
                                'code' => 'ELV_DATA_OUTLET',
                                'name' => 'Data Outlet Installation',
                                'unit' => 'Nos',
                                'aliases' => ['data point', 'lan point', 'network outlet'],
                            ],
                            [
                                'code' => 'ELV_RACK',
                                'name' => 'Network Rack Installation',
                                'unit' => 'Nos',
                                'aliases' => ['server rack', 'network rack'],
                            ],
                            [
                                'code' => 'ELV_FIBER',
                                'name' => 'Fiber Optic Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['fiber cable', 'optical fiber', 'fibre optic'],
                            ],
                            [
                                'code' => 'ELV_TELEPHONE',
                                'name' => 'Telephone / Intercom Point Installation',
                                'unit' => 'Nos',
                                'aliases' => ['telephone point', 'intercom point'],
                            ],
                        ],
                    ],
                    [
                        'code' => '20-02',
                        'name' => 'CCTV & Security',
                        'activities' => [
                            [
                                'code' => 'ELV_CCTV_CABLE',
                                'name' => 'CCTV Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['cctv cable', 'camera cable'],
                            ],
                            [
                                'code' => 'ELV_CAMERA',
                                'name' => 'CCTV Camera Installation',
                                'unit' => 'Nos',
                                'aliases' => ['camera installation', 'cctv camera'],
                            ],
                            [
                                'code' => 'ELV_NVR',
                                'name' => 'NVR / DVR Installation',
                                'unit' => 'Nos',
                                'aliases' => ['nvr installation', 'dvr installation'],
                            ],
                            [
                                'code' => 'ELV_ACCESS',
                                'name' => 'Access Control Reader Installation',
                                'unit' => 'Nos',
                                'aliases' => ['access control', 'card reader'],
                            ],
                            [
                                'code' => 'ELV_DOOR_CONTACT',
                                'name' => 'Door Contact / Security Sensor Installation',
                                'unit' => 'Nos',
                                'aliases' => ['door contact', 'security sensor'],
                            ],
                        ],
                    ],
                    [
                        'code' => '20-03',
                        'name' => 'Public Address & AV',
                        'activities' => [
                            [
                                'code' => 'ELV_PA_CABLE',
                                'name' => 'PA System Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['pa cable', 'public address cable'],
                            ],
                            [
                                'code' => 'ELV_SPEAKER',
                                'name' => 'PA Speaker Installation',
                                'unit' => 'Nos',
                                'aliases' => ['pa speaker', 'public address speaker'],
                            ],
                            [
                                'code' => 'ELV_TV',
                                'name' => 'TV / MATV Point Installation',
                                'unit' => 'Nos',
                                'aliases' => ['tv point', 'matv point', 'television point'],
                            ],
                        ],
                    ],
                    [
                        'code' => '20-04',
                        'name' => 'Automation & BMS',
                        'activities' => [
                            [
                                'code' => 'ELV_BMS_CABLE',
                                'name' => 'BMS Control Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['bms cable', 'building management cable'],
                            ],
                            [
                                'code' => 'ELV_BMS_SENSOR',
                                'name' => 'BMS Sensor / Device Installation',
                                'unit' => 'Nos',
                                'aliases' => ['bms sensor', 'bms device'],
                            ],
                            [
                                'code' => 'ELV_HOME_AUTO',
                                'name' => 'Home Automation Device Installation',
                                'unit' => 'Nos',
                                'aliases' => ['home automation', 'smart home device'],
                            ],
                            [
                                'code' => 'ELV_BMS_PANEL',
                                'name' => 'BMS Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['bms panel', 'building management panel'],
                            ],
                        ],
                    ],
                    [
                        'code' => '20-05',
                        'name' => 'Wi-Fi & Wireless',
                        'activities' => [
                            [
                                'code' => 'ELV_WIFI',
                                'name' => 'Wi-Fi Access Point Installation',
                                'unit' => 'Nos',
                                'aliases' => ['wifi access point', 'wireless access point', 'wifi ap'],
                            ],
                        ],
                    ],
                    [
                        'code' => '20-06',
                        'name' => 'ELV Testing',
                        'activities' => [
                            [
                                'code' => 'ELV_DATA_TEST',
                                'name' => 'Data Cable Testing',
                                'unit' => 'Point',
                                'aliases' => ['lan testing', 'data point testing', 'fluke test'],
                            ],
                            [
                                'code' => 'ELV_CCTV_TEST',
                                'name' => 'CCTV System Testing',
                                'unit' => 'Camera',
                                'aliases' => ['camera testing', 'cctv testing'],
                            ],
                            [
                                'code' => 'ELV_ACCESS_TEST',
                                'name' => 'Access Control Testing',
                                'unit' => 'Point',
                                'aliases' => ['access control testing'],
                            ],
                            [
                                'code' => 'ELV_BMS_TEST',
                                'name' => 'BMS Point Testing',
                                'unit' => 'Point',
                                'aliases' => ['bms testing', 'bms point check'],
                            ],
                        ],
                    ],
                ],
            ],
            'PLUMBING' => [
                'description' => 'Domestic water supply piping, valves, pumps, tanks, fixtures and plumbing testing works.',
                'sections' => [
                    [
                        'code' => '21-01',
                        'name' => 'Water Supply Piping',
                        'activities' => [
                            [
                                'code' => 'PLUMB_COLD',
                                'name' => 'Cold Water Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['cold water pipe', 'water line'],
                            ],
                            [
                                'code' => 'PLUMB_HOT',
                                'name' => 'Hot Water Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['hot water pipe', 'hot water line'],
                            ],
                            [
                                'code' => 'PLUMB_RETURN',
                                'name' => 'Hot Water Return Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['hot water return', 'return line'],
                            ],
                            [
                                'code' => 'PLUMB_RISER',
                                'name' => 'Water Supply Riser Installation',
                                'unit' => 'Rm',
                                'aliases' => ['water riser', 'plumbing riser'],
                            ],
                            [
                                'code' => 'PLUMB_BRANCH',
                                'name' => 'Water Supply Branch Piping',
                                'unit' => 'Rm',
                                'aliases' => ['branch pipe', 'water branch line'],
                            ],
                        ],
                    ],
                    [
                        'code' => '21-02',
                        'name' => 'Valves & Accessories',
                        'activities' => [
                            [
                                'code' => 'PLUMB_VALVE',
                                'name' => 'Plumbing Valve Installation',
                                'unit' => 'Nos',
                                'aliases' => ['valve fixing', 'water valve'],
                            ],
                            [
                                'code' => 'PLUMB_PRV',
                                'name' => 'Pressure Reducing Valve Installation',
                                'unit' => 'Nos',
                                'aliases' => ['prv', 'pressure reducing valve'],
                            ],
                            [
                                'code' => 'PLUMB_WATER_METER',
                                'name' => 'Water Meter Installation',
                                'unit' => 'Nos',
                                'aliases' => ['water meter', 'meter fixing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '21-03',
                        'name' => 'Pumps & Tanks',
                        'activities' => [
                            [
                                'code' => 'PLUMB_PUMP',
                                'name' => 'Water Pump Installation',
                                'unit' => 'Nos',
                                'aliases' => ['water pump', 'pump installation'],
                            ],
                            [
                                'code' => 'PLUMB_BOOSTER',
                                'name' => 'Booster Pump Set Installation',
                                'unit' => 'Set',
                                'aliases' => ['booster pump', 'hydropneumatic pump'],
                            ],
                            [
                                'code' => 'PLUMB_TANK',
                                'name' => 'Water Storage Tank Installation',
                                'unit' => 'Nos',
                                'aliases' => ['water tank', 'storage tank'],
                            ],
                        ],
                    ],
                    [
                        'code' => '21-04',
                        'name' => 'Fixtures & Connections',
                        'activities' => [
                            [
                                'code' => 'PLUMB_BASIN',
                                'name' => 'Wash Basin Installation',
                                'unit' => 'Nos',
                                'aliases' => ['wash basin', 'basin fixing'],
                            ],
                            [
                                'code' => 'PLUMB_SINK',
                                'name' => 'Sink Installation',
                                'unit' => 'Nos',
                                'aliases' => ['kitchen sink', 'sink fixing'],
                            ],
                            [
                                'code' => 'PLUMB_FAUCET',
                                'name' => 'Faucet / Tap Installation',
                                'unit' => 'Nos',
                                'aliases' => ['tap fixing', 'faucet'],
                            ],
                            [
                                'code' => 'PLUMB_SHOWER',
                                'name' => 'Shower Installation',
                                'unit' => 'Nos',
                                'aliases' => ['shower fitting', 'shower installation'],
                            ],
                            [
                                'code' => 'PLUMB_GEYSER',
                                'name' => 'Water Heater / Geyser Installation',
                                'unit' => 'Nos',
                                'aliases' => ['geyser', 'water heater'],
                            ],
                        ],
                    ],
                    [
                        'code' => '21-05',
                        'name' => 'Plumbing Testing',
                        'activities' => [
                            [
                                'code' => 'PLUMB_PRESSURE_TEST',
                                'name' => 'Water Supply Pressure Testing',
                                'unit' => 'Rm',
                                'aliases' => ['pressure test', 'plumbing pressure test', 'hydro test water line'],
                            ],
                            [
                                'code' => 'PLUMB_FLUSH',
                                'name' => 'Water Supply Pipe Flushing',
                                'unit' => 'Rm',
                                'aliases' => ['pipe flushing', 'water line flushing'],
                            ],
                            [
                                'code' => 'PLUMB_DISINFECT',
                                'name' => 'Water Supply Disinfection',
                                'unit' => 'Job',
                                'aliases' => ['pipe disinfection', 'water line chlorination'],
                            ],
                        ],
                    ],
                ],
            ],
            'DRAINAGE' => [
                'description' => 'Soil, waste, vent, underground drainage, rainwater systems, sanitary fixtures and drainage testing works.',
                'sections' => [
                    [
                        'code' => '22-01',
                        'name' => 'Soil Waste Vent',
                        'activities' => [
                            [
                                'code' => 'DRAIN_SOIL',
                                'name' => 'Soil Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['soil pipe', 'soil stack'],
                            ],
                            [
                                'code' => 'DRAIN_WASTE',
                                'name' => 'Waste Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['waste pipe', 'waste line'],
                            ],
                            [
                                'code' => 'DRAIN_VENT',
                                'name' => 'Vent Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['vent pipe', 'vent stack'],
                            ],
                            [
                                'code' => 'DRAIN_STACK',
                                'name' => 'Drainage Stack Installation',
                                'unit' => 'Rm',
                                'aliases' => ['drain stack', 'soil waste stack'],
                            ],
                        ],
                    ],
                    [
                        'code' => '22-02',
                        'name' => 'Floor & Fixture Drainage',
                        'activities' => [
                            [
                                'code' => 'DRAIN_FLOOR',
                                'name' => 'Floor Trap / Drain Installation',
                                'unit' => 'Nos',
                                'aliases' => ['floor trap', 'floor drain', 'nahani trap'],
                            ],
                            [
                                'code' => 'DRAIN_WC',
                                'name' => 'WC Installation',
                                'unit' => 'Nos',
                                'aliases' => ['water closet', 'wc fixing', 'toilet commode'],
                            ],
                            [
                                'code' => 'DRAIN_URINAL',
                                'name' => 'Urinal Installation',
                                'unit' => 'Nos',
                                'aliases' => ['urinal fixing', 'urinal installation'],
                            ],
                            [
                                'code' => 'DRAIN_CLEANOUT',
                                'name' => 'Cleanout Installation',
                                'unit' => 'Nos',
                                'aliases' => ['clean out', 'drain cleanout'],
                            ],
                        ],
                    ],
                    [
                        'code' => '22-03',
                        'name' => 'Underground Drainage',
                        'activities' => [
                            [
                                'code' => 'DRAIN_UG_PIPE',
                                'name' => 'Underground Drainage Pipe Laying',
                                'unit' => 'Rm',
                                'aliases' => ['underground drainage', 'sewer pipe', 'ug drain pipe'],
                            ],
                            [
                                'code' => 'DRAIN_MANHOLE',
                                'name' => 'Drainage Manhole Construction',
                                'unit' => 'Nos',
                                'aliases' => ['manhole work', 'sewer manhole'],
                            ],
                            [
                                'code' => 'DRAIN_IC',
                                'name' => 'Inspection Chamber Construction',
                                'unit' => 'Nos',
                                'aliases' => ['inspection chamber', 'ic chamber'],
                            ],
                            [
                                'code' => 'DRAIN_GULLY',
                                'name' => 'Gully Trap Installation',
                                'unit' => 'Nos',
                                'aliases' => ['gully trap', 'gully'],
                            ],
                        ],
                    ],
                    [
                        'code' => '22-04',
                        'name' => 'Rainwater',
                        'activities' => [
                            [
                                'code' => 'DRAIN_RWP',
                                'name' => 'Rainwater Downpipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['rainwater pipe', 'rwp', 'downpipe'],
                            ],
                            [
                                'code' => 'DRAIN_RWH',
                                'name' => 'Rainwater Harvesting Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['rwh pipe', 'rainwater harvesting line'],
                            ],
                            [
                                'code' => 'DRAIN_RWH_PIT',
                                'name' => 'Rainwater Harvesting / Recharge Pit',
                                'unit' => 'Nos',
                                'aliases' => ['recharge pit', 'rwh pit', 'rainwater pit'],
                            ],
                        ],
                    ],
                    [
                        'code' => '22-05',
                        'name' => 'Drainage Testing',
                        'activities' => [
                            [
                                'code' => 'DRAIN_LEAK_TEST',
                                'name' => 'Drainage Leakage Testing',
                                'unit' => 'Rm',
                                'aliases' => ['drain leak test', 'soil pipe test'],
                            ],
                            [
                                'code' => 'DRAIN_FLOW_TEST',
                                'name' => 'Drainage Flow Testing',
                                'unit' => 'Job',
                                'aliases' => ['drain flow test', 'drainage testing'],
                            ],
                            [
                                'code' => 'DRAIN_FLUSH',
                                'name' => 'Drainage Flushing / Cleaning',
                                'unit' => 'Rm',
                                'aliases' => ['drain flushing', 'pipe cleaning'],
                            ],
                        ],
                    ],
                ],
            ],
            'FIRE_FIGHTING' => [
                'description' => 'Fire water piping, hydrants, sprinklers, pumps, portable protection, firestopping and testing works.',
                'sections' => [
                    [
                        'code' => '23-01',
                        'name' => 'Fire Water Piping',
                        'activities' => [
                            [
                                'code' => 'FIRE_PIPE',
                                'name' => 'Fire Fighting Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['fire pipe', 'fire line', 'fire fighting piping'],
                            ],
                            [
                                'code' => 'FIRE_RISER',
                                'name' => 'Fire Riser Installation',
                                'unit' => 'Rm',
                                'aliases' => ['fire riser', 'wet riser'],
                            ],
                            [
                                'code' => 'FIRE_VALVE',
                                'name' => 'Fire Valve Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire valve', 'butterfly valve fire'],
                            ],
                        ],
                    ],
                    [
                        'code' => '23-02',
                        'name' => 'Hydrant & Hose Reel',
                        'activities' => [
                            [
                                'code' => 'FIRE_HYDRANT',
                                'name' => 'Fire Hydrant Installation',
                                'unit' => 'Nos',
                                'aliases' => ['hydrant point', 'fire hydrant'],
                            ],
                            [
                                'code' => 'FIRE_HOSE_REEL',
                                'name' => 'Hose Reel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['hose reel', 'fire hose reel'],
                            ],
                            [
                                'code' => 'FIRE_BREECHING',
                                'name' => 'Breeching / Fire Brigade Inlet Installation',
                                'unit' => 'Nos',
                                'aliases' => ['breeching inlet', 'fire brigade inlet'],
                            ],
                        ],
                    ],
                    [
                        'code' => '23-03',
                        'name' => 'Sprinkler',
                        'activities' => [
                            [
                                'code' => 'FIRE_SPRINKLER_PIPE',
                                'name' => 'Sprinkler Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['sprinkler pipe', 'sprinkler line'],
                            ],
                            [
                                'code' => 'FIRE_SPRINKLER_HEAD',
                                'name' => 'Sprinkler Head Installation',
                                'unit' => 'Nos',
                                'aliases' => ['sprinkler head', 'fire sprinkler'],
                            ],
                            [
                                'code' => 'FIRE_FLOW_SWITCH',
                                'name' => 'Flow Switch Installation',
                                'unit' => 'Nos',
                                'aliases' => ['flow switch', 'sprinkler flow switch'],
                            ],
                        ],
                    ],
                    [
                        'code' => '23-04',
                        'name' => 'Fire Pumps',
                        'activities' => [
                            [
                                'code' => 'FIRE_MAIN_PUMP',
                                'name' => 'Main Fire Pump Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire pump', 'main fire pump'],
                            ],
                            [
                                'code' => 'FIRE_JOCKEY',
                                'name' => 'Jockey Pump Installation',
                                'unit' => 'Nos',
                                'aliases' => ['jockey pump'],
                            ],
                            [
                                'code' => 'FIRE_DIESEL_PUMP',
                                'name' => 'Diesel Fire Pump Installation',
                                'unit' => 'Nos',
                                'aliases' => ['diesel fire pump'],
                            ],
                            [
                                'code' => 'FIRE_PUMP_PANEL',
                                'name' => 'Fire Pump Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire pump panel'],
                            ],
                        ],
                    ],
                    [
                        'code' => '23-05',
                        'name' => 'Portable Fire Protection',
                        'activities' => [
                            [
                                'code' => 'FIRE_EXTINGUISHER',
                                'name' => 'Fire Extinguisher Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire extinguisher', 'extinguisher fixing'],
                            ],
                            [
                                'code' => 'FIRE_CABINET',
                                'name' => 'Fire Hose Cabinet Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire cabinet', 'hose cabinet'],
                            ],
                        ],
                    ],
                    [
                        'code' => '23-06',
                        'name' => 'Firestopping',
                        'activities' => [
                            [
                                'code' => 'FIRE_STOP',
                                'name' => 'MEP Penetration Firestopping',
                                'unit' => 'Nos',
                                'aliases' => ['fire stopping', 'firestop', 'penetration sealing'],
                            ],
                            [
                                'code' => 'FIRE_COLLAR',
                                'name' => 'Fire Collar Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire collar', 'pipe fire collar'],
                            ],
                        ],
                    ],
                    [
                        'code' => '23-07',
                        'name' => 'Fire Fighting Testing',
                        'activities' => [
                            [
                                'code' => 'FIRE_HYDRO_TEST',
                                'name' => 'Fire Pipe Hydrostatic Testing',
                                'unit' => 'Rm',
                                'aliases' => ['fire hydro test', 'fire pipe pressure test'],
                            ],
                            [
                                'code' => 'FIRE_PUMP_TEST',
                                'name' => 'Fire Pump Testing',
                                'unit' => 'Set',
                                'aliases' => ['fire pump test', 'pump performance test'],
                            ],
                            [
                                'code' => 'FIRE_SYSTEM_TEST',
                                'name' => 'Fire Fighting System Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['fire system commissioning', 'fire fighting testing'],
                            ],
                        ],
                    ],
                ],
            ],
            'FIRE_ALARM' => [
                'description' => 'Fire alarm cabling, detection, notification, panels, emergency communication and commissioning works.',
                'sections' => [
                    [
                        'code' => '24-01',
                        'name' => 'Fire Alarm Cabling',
                        'activities' => [
                            [
                                'code' => 'FA_CONDUIT',
                                'name' => 'Fire Alarm Conduit Installation',
                                'unit' => 'Rm',
                                'aliases' => ['fire alarm conduit'],
                            ],
                            [
                                'code' => 'FA_CABLE',
                                'name' => 'Fire Alarm Cable Laying',
                                'unit' => 'Rm',
                                'aliases' => ['fire alarm cable', 'fire cable'],
                            ],
                        ],
                    ],
                    [
                        'code' => '24-02',
                        'name' => 'Detection & Devices',
                        'activities' => [
                            [
                                'code' => 'FA_SMOKE',
                                'name' => 'Smoke Detector Installation',
                                'unit' => 'Nos',
                                'aliases' => ['smoke detector'],
                            ],
                            [
                                'code' => 'FA_HEAT',
                                'name' => 'Heat Detector Installation',
                                'unit' => 'Nos',
                                'aliases' => ['heat detector'],
                            ],
                            [
                                'code' => 'FA_MCP',
                                'name' => 'Manual Call Point Installation',
                                'unit' => 'Nos',
                                'aliases' => ['mcp', 'manual call point', 'break glass'],
                            ],
                            [
                                'code' => 'FA_HOOTER',
                                'name' => 'Hooter / Sounder Installation',
                                'unit' => 'Nos',
                                'aliases' => ['hooter', 'fire sounder', 'alarm sounder'],
                            ],
                            [
                                'code' => 'FA_STROBE',
                                'name' => 'Strobe / Visual Alarm Installation',
                                'unit' => 'Nos',
                                'aliases' => ['strobe light', 'visual alarm'],
                            ],
                            [
                                'code' => 'FA_MODULE',
                                'name' => 'Fire Alarm Module Installation',
                                'unit' => 'Nos',
                                'aliases' => ['monitor module', 'control module', 'fire module'],
                            ],
                        ],
                    ],
                    [
                        'code' => '24-03',
                        'name' => 'Panels & Interfaces',
                        'activities' => [
                            [
                                'code' => 'FA_PANEL',
                                'name' => 'Fire Alarm Control Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire alarm panel', 'facp'],
                            ],
                            [
                                'code' => 'FA_REPEATER',
                                'name' => 'Fire Alarm Repeater Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['repeater panel', 'fire repeater'],
                            ],
                            [
                                'code' => 'FA_INTERFACE',
                                'name' => 'Fire Alarm System Interface',
                                'unit' => 'Point',
                                'aliases' => ['fire alarm interface', 'cause effect interface'],
                            ],
                        ],
                    ],
                    [
                        'code' => '24-04',
                        'name' => 'Emergency Systems',
                        'activities' => [
                            [
                                'code' => 'FA_VOICE_EVAC',
                                'name' => 'Voice Evacuation Device Installation',
                                'unit' => 'Nos',
                                'aliases' => ['voice evacuation', 'evac speaker'],
                            ],
                            [
                                'code' => 'FA_REFUGE',
                                'name' => 'Refuge / Emergency Communication Point Installation',
                                'unit' => 'Nos',
                                'aliases' => ['refuge communication', 'emergency intercom'],
                            ],
                        ],
                    ],
                    [
                        'code' => '24-05',
                        'name' => 'Testing & Commissioning',
                        'activities' => [
                            [
                                'code' => 'FA_LOOP_TEST',
                                'name' => 'Fire Alarm Loop Testing',
                                'unit' => 'Loop',
                                'aliases' => ['loop testing', 'fire loop test'],
                            ],
                            [
                                'code' => 'FA_DEVICE_TEST',
                                'name' => 'Fire Alarm Device Testing',
                                'unit' => 'Point',
                                'aliases' => ['detector testing', 'fire device test'],
                            ],
                            [
                                'code' => 'FA_CAUSE_EFFECT',
                                'name' => 'Cause & Effect Testing',
                                'unit' => 'Job',
                                'aliases' => ['cause effect test', 'fire sequence testing'],
                            ],
                            [
                                'code' => 'FA_COMMISSION',
                                'name' => 'Fire Alarm System Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['fire alarm commissioning'],
                            ],
                        ],
                    ],
                ],
            ],
            'HVAC' => [
                'description' => 'Air-conditioning, ventilation, ductwork, refrigerant and chilled-water systems, equipment and commissioning works.',
                'sections' => [
                    [
                        'code' => '25-01',
                        'name' => 'Ductwork',
                        'activities' => [
                            [
                                'code' => 'HVAC_DUCT_FAB',
                                'name' => 'HVAC Duct Fabrication',
                                'unit' => 'Sq.m',
                                'aliases' => ['duct fabrication', 'gi duct fabrication'],
                            ],
                            [
                                'code' => 'HVAC_DUCT_INSTALL',
                                'name' => 'HVAC Duct Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['duct installation', 'ducting work'],
                            ],
                            [
                                'code' => 'HVAC_DUCT_INSUL',
                                'name' => 'Duct Insulation',
                                'unit' => 'Sq.m',
                                'aliases' => ['duct insulation'],
                            ],
                            [
                                'code' => 'HVAC_DUCT_CLAD',
                                'name' => 'Duct External Cladding',
                                'unit' => 'Sq.m',
                                'aliases' => ['duct cladding', 'aluminium duct cladding'],
                            ],
                            [
                                'code' => 'HVAC_FIRE_DAMPER',
                                'name' => 'Fire Damper Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fire damper'],
                            ],
                            [
                                'code' => 'HVAC_VCD',
                                'name' => 'Volume Control Damper Installation',
                                'unit' => 'Nos',
                                'aliases' => ['vcd', 'volume damper'],
                            ],
                        ],
                    ],
                    [
                        'code' => '25-02',
                        'name' => 'Air Distribution',
                        'activities' => [
                            [
                                'code' => 'HVAC_DIFFUSER',
                                'name' => 'Air Diffuser Installation',
                                'unit' => 'Nos',
                                'aliases' => ['ac diffuser', 'air diffuser'],
                            ],
                            [
                                'code' => 'HVAC_GRILLE',
                                'name' => 'Air Grille Installation',
                                'unit' => 'Nos',
                                'aliases' => ['air grille', 'ac grille'],
                            ],
                            [
                                'code' => 'HVAC_LOUVER',
                                'name' => 'HVAC Louver Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fresh air louver', 'exhaust louver'],
                            ],
                            [
                                'code' => 'HVAC_FLEX_DUCT',
                                'name' => 'Flexible Duct Installation',
                                'unit' => 'Rm',
                                'aliases' => ['flex duct', 'flexible duct'],
                            ],
                        ],
                    ],
                    [
                        'code' => '25-03',
                        'name' => 'Refrigerant & Drain Piping',
                        'activities' => [
                            [
                                'code' => 'HVAC_COPPER',
                                'name' => 'Refrigerant Copper Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['ac copper pipe', 'refrigerant pipe'],
                            ],
                            [
                                'code' => 'HVAC_COPPER_INSUL',
                                'name' => 'Refrigerant Pipe Insulation',
                                'unit' => 'Rm',
                                'aliases' => ['copper pipe insulation', 'ac pipe insulation'],
                            ],
                            [
                                'code' => 'HVAC_DRAIN',
                                'name' => 'AC Condensate Drain Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['ac drain pipe', 'condensate drain'],
                            ],
                        ],
                    ],
                    [
                        'code' => '25-04',
                        'name' => 'HVAC Equipment',
                        'activities' => [
                            [
                                'code' => 'HVAC_SPLIT',
                                'name' => 'Split AC Indoor / Outdoor Unit Installation',
                                'unit' => 'Set',
                                'aliases' => ['split ac installation', 'ac unit'],
                            ],
                            [
                                'code' => 'HVAC_VRF_IDU',
                                'name' => 'VRF / VRV Indoor Unit Installation',
                                'unit' => 'Nos',
                                'aliases' => ['vrf indoor unit', 'vrv idu'],
                            ],
                            [
                                'code' => 'HVAC_VRF_ODU',
                                'name' => 'VRF / VRV Outdoor Unit Installation',
                                'unit' => 'Nos',
                                'aliases' => ['vrf outdoor unit', 'vrv odu'],
                            ],
                            [
                                'code' => 'HVAC_AHU',
                                'name' => 'AHU Installation',
                                'unit' => 'Nos',
                                'aliases' => ['ahu', 'air handling unit'],
                            ],
                            [
                                'code' => 'HVAC_FCU',
                                'name' => 'FCU Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fcu', 'fan coil unit'],
                            ],
                            [
                                'code' => 'HVAC_EXHAUST',
                                'name' => 'Exhaust Fan Installation',
                                'unit' => 'Nos',
                                'aliases' => ['exhaust fan'],
                            ],
                            [
                                'code' => 'HVAC_FRESH_AIR',
                                'name' => 'Fresh Air Fan Installation',
                                'unit' => 'Nos',
                                'aliases' => ['fresh air fan', 'faf'],
                            ],
                        ],
                    ],
                    [
                        'code' => '25-05',
                        'name' => 'Chilled Water',
                        'activities' => [
                            [
                                'code' => 'HVAC_CHW_PIPE',
                                'name' => 'Chilled Water Pipe Installation',
                                'unit' => 'Rm',
                                'aliases' => ['chilled water pipe', 'chw pipe'],
                            ],
                            [
                                'code' => 'HVAC_CHW_INSUL',
                                'name' => 'Chilled Water Pipe Insulation',
                                'unit' => 'Rm',
                                'aliases' => ['chw insulation', 'chilled water insulation'],
                            ],
                            [
                                'code' => 'HVAC_CHILLER',
                                'name' => 'Chiller Installation',
                                'unit' => 'Nos',
                                'aliases' => ['chiller installation'],
                            ],
                            [
                                'code' => 'HVAC_PUMP',
                                'name' => 'Chilled Water Pump Installation',
                                'unit' => 'Nos',
                                'aliases' => ['chw pump', 'chilled water pump'],
                            ],
                            [
                                'code' => 'HVAC_COOL_TOWER',
                                'name' => 'Cooling Tower Installation',
                                'unit' => 'Nos',
                                'aliases' => ['cooling tower'],
                            ],
                        ],
                    ],
                    [
                        'code' => '25-06',
                        'name' => 'Ventilation & Smoke Management',
                        'activities' => [
                            [
                                'code' => 'HVAC_CAR_PARK',
                                'name' => 'Car Park Ventilation Installation',
                                'unit' => 'Job',
                                'aliases' => ['car park ventilation', 'basement ventilation'],
                            ],
                            [
                                'code' => 'HVAC_SMOKE_EXTRACT',
                                'name' => 'Smoke Extract System Installation',
                                'unit' => 'Job',
                                'aliases' => ['smoke extraction', 'smoke exhaust'],
                            ],
                            [
                                'code' => 'HVAC_PRESSURIZATION',
                                'name' => 'Staircase / Lift Lobby Pressurization Installation',
                                'unit' => 'Job',
                                'aliases' => ['staircase pressurization', 'lobby pressurization'],
                            ],
                        ],
                    ],
                    [
                        'code' => '25-07',
                        'name' => 'HVAC Testing',
                        'activities' => [
                            [
                                'code' => 'HVAC_PRESSURE_TEST',
                                'name' => 'Refrigerant / Piping Pressure Testing',
                                'unit' => 'Rm',
                                'aliases' => ['hvac pressure test', 'nitrogen test'],
                            ],
                            [
                                'code' => 'HVAC_DUCT_LEAK',
                                'name' => 'Duct Leakage Testing',
                                'unit' => 'Sq.m',
                                'aliases' => ['duct leak test', 'duct leakage'],
                            ],
                            [
                                'code' => 'HVAC_TAB',
                                'name' => 'Air Balancing / TAB',
                                'unit' => 'Job',
                                'aliases' => ['air balancing', 'tab', 'testing adjusting balancing'],
                            ],
                            [
                                'code' => 'HVAC_COMMISSION',
                                'name' => 'HVAC System Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['hvac commissioning', 'ac commissioning'],
                            ],
                        ],
                    ],
                ],
            ],
            'VERTICAL_TRANSPORT' => [
                'description' => 'Lift, escalator, travelator installation, interfaces, testing and commissioning works.',
                'sections' => [
                    [
                        'code' => '26-01',
                        'name' => 'Lift Shaft Preparation',
                        'activities' => [
                            [
                                'code' => 'LIFT_SHAFT_CHECK',
                                'name' => 'Lift Shaft Dimension / Plumb Check',
                                'unit' => 'Nos',
                                'aliases' => ['lift shaft check', 'elevator shaft check'],
                            ],
                            [
                                'code' => 'LIFT_INSERT',
                                'name' => 'Lift Embedded Insert / Bracket Preparation',
                                'unit' => 'Nos',
                                'aliases' => ['lift insert', 'elevator bracket insert'],
                            ],
                        ],
                    ],
                    [
                        'code' => '26-02',
                        'name' => 'Lift Installation',
                        'activities' => [
                            [
                                'code' => 'LIFT_GUIDE',
                                'name' => 'Lift Guide Rail Installation',
                                'unit' => 'Rm',
                                'aliases' => ['lift guide rail', 'elevator rail'],
                            ],
                            [
                                'code' => 'LIFT_MACHINE',
                                'name' => 'Lift Machine / Drive Installation',
                                'unit' => 'Nos',
                                'aliases' => ['lift machine', 'elevator motor'],
                            ],
                            [
                                'code' => 'LIFT_CAR',
                                'name' => 'Lift Car Installation',
                                'unit' => 'Nos',
                                'aliases' => ['lift car', 'elevator cabin'],
                            ],
                            [
                                'code' => 'LIFT_DOOR',
                                'name' => 'Lift Landing Door Installation',
                                'unit' => 'Nos',
                                'aliases' => ['lift door', 'elevator landing door'],
                            ],
                            [
                                'code' => 'LIFT_CONTROLLER',
                                'name' => 'Lift Controller Installation',
                                'unit' => 'Nos',
                                'aliases' => ['lift controller', 'elevator panel'],
                            ],
                            [
                                'code' => 'LIFT_WIRING',
                                'name' => 'Lift Electrical Wiring',
                                'unit' => 'Job',
                                'aliases' => ['lift wiring', 'elevator wiring'],
                            ],
                        ],
                    ],
                    [
                        'code' => '26-03',
                        'name' => 'Escalators & Moving Systems',
                        'activities' => [
                            [
                                'code' => 'LIFT_ESCALATOR',
                                'name' => 'Escalator Installation',
                                'unit' => 'Nos',
                                'aliases' => ['escalator installation', 'escalator'],
                            ],
                            [
                                'code' => 'LIFT_TRAVELATOR',
                                'name' => 'Travelator / Moving Walk Installation',
                                'unit' => 'Nos',
                                'aliases' => ['travelator', 'moving walkway'],
                            ],
                        ],
                    ],
                    [
                        'code' => '26-04',
                        'name' => 'Testing & Handover',
                        'activities' => [
                            [
                                'code' => 'LIFT_TEST',
                                'name' => 'Lift Testing & Commissioning',
                                'unit' => 'Nos',
                                'aliases' => ['lift testing', 'elevator commissioning'],
                            ],
                            [
                                'code' => 'LIFT_LOAD_TEST',
                                'name' => 'Lift Load Testing',
                                'unit' => 'Nos',
                                'aliases' => ['lift load test', 'elevator load test'],
                            ],
                            [
                                'code' => 'LIFT_ESC_TEST',
                                'name' => 'Escalator Testing & Commissioning',
                                'unit' => 'Nos',
                                'aliases' => ['escalator testing', 'escalator commissioning'],
                            ],
                        ],
                    ],
                ],
            ],
            'MEDICAL_SYSTEMS' => [
                'description' => 'Healthcare specialist systems including medical gases, patient systems, clinical interfaces and certification.',
                'sections' => [
                    [
                        'code' => '27-01',
                        'name' => 'Medical Gas Pipelines',
                        'activities' => [
                            [
                                'code' => 'MED_OXYGEN_PIPE',
                                'name' => 'Medical Oxygen Pipeline Installation',
                                'unit' => 'Rm',
                                'aliases' => ['oxygen pipe', 'medical oxygen line', 'o2 pipeline'],
                            ],
                            [
                                'code' => 'MED_AIR_PIPE',
                                'name' => 'Medical Air Pipeline Installation',
                                'unit' => 'Rm',
                                'aliases' => ['medical air pipe', 'compressed medical air'],
                            ],
                            [
                                'code' => 'MED_VAC_PIPE',
                                'name' => 'Medical Vacuum Pipeline Installation',
                                'unit' => 'Rm',
                                'aliases' => ['medical vacuum pipe', 'vacuum line'],
                            ],
                            [
                                'code' => 'MED_AGSS_PIPE',
                                'name' => 'AGSS Pipeline Installation',
                                'unit' => 'Rm',
                                'aliases' => ['agss pipe', 'anaesthetic gas scavenging'],
                            ],
                            [
                                'code' => 'MED_N2O_PIPE',
                                'name' => 'Nitrous Oxide Pipeline Installation',
                                'unit' => 'Rm',
                                'aliases' => ['nitrous oxide pipe', 'n2o line'],
                            ],
                        ],
                    ],
                    [
                        'code' => '27-02',
                        'name' => 'Medical Gas Devices',
                        'activities' => [
                            [
                                'code' => 'MED_OUTLET',
                                'name' => 'Medical Gas Outlet Installation',
                                'unit' => 'Nos',
                                'aliases' => ['medical gas outlet', 'oxygen outlet', 'gas terminal unit'],
                            ],
                            [
                                'code' => 'MED_ZONE_VALVE',
                                'name' => 'Zone Valve Box / AVSU Installation',
                                'unit' => 'Nos',
                                'aliases' => ['zone valve box', 'avsu', 'area valve service unit'],
                            ],
                            [
                                'code' => 'MED_ALARM',
                                'name' => 'Medical Gas Alarm Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['medical gas alarm', 'gas alarm panel'],
                            ],
                            [
                                'code' => 'MED_MANIFOLD',
                                'name' => 'Medical Gas Manifold Installation',
                                'unit' => 'Set',
                                'aliases' => ['oxygen manifold', 'medical gas manifold'],
                            ],
                        ],
                    ],
                    [
                        'code' => '27-03',
                        'name' => 'Patient & Clinical Systems',
                        'activities' => [
                            [
                                'code' => 'MED_BEDHEAD',
                                'name' => 'Bed Head Unit / Panel Installation',
                                'unit' => 'Nos',
                                'aliases' => ['bed head panel', 'bed head unit', 'bhu'],
                            ],
                            [
                                'code' => 'MED_NURSE_CALL',
                                'name' => 'Nurse Call Device Installation',
                                'unit' => 'Point',
                                'aliases' => ['nurse call', 'nurse call point'],
                            ],
                            [
                                'code' => 'MED_PENDANT',
                                'name' => 'OT / ICU Service Pendant Installation',
                                'unit' => 'Nos',
                                'aliases' => ['ot pendant', 'icu pendant', 'medical pendant'],
                            ],
                        ],
                    ],
                    [
                        'code' => '27-04',
                        'name' => 'Specialist Room Interfaces',
                        'activities' => [
                            [
                                'code' => 'MED_OT_INTERFACE',
                                'name' => 'Operation Theatre Specialist Service Interface',
                                'unit' => 'Job',
                                'aliases' => ['ot services', 'operation theatre interface'],
                            ],
                            [
                                'code' => 'MED_ISOLATION_INTERFACE',
                                'name' => 'Isolation Room Specialist Service Interface',
                                'unit' => 'Job',
                                'aliases' => ['isolation room services', 'negative pressure room interface'],
                            ],
                            [
                                'code' => 'MED_LAB_INTERFACE',
                                'name' => 'Laboratory Specialist Service Interface',
                                'unit' => 'Job',
                                'aliases' => ['lab services interface', 'laboratory services'],
                            ],
                        ],
                    ],
                    [
                        'code' => '27-05',
                        'name' => 'Testing & Certification',
                        'activities' => [
                            [
                                'code' => 'MED_GAS_PRESSURE',
                                'name' => 'Medical Gas Pipeline Pressure Testing',
                                'unit' => 'Rm',
                                'aliases' => ['medical gas pressure test', 'oxygen line pressure test'],
                            ],
                            [
                                'code' => 'MED_GAS_PURGE',
                                'name' => 'Medical Gas Pipeline Purging',
                                'unit' => 'Rm',
                                'aliases' => ['medical gas purging', 'oxygen pipe purge'],
                            ],
                            [
                                'code' => 'MED_GAS_COMMISSION',
                                'name' => 'Medical Gas System Testing & Commissioning',
                                'unit' => 'Job',
                                'aliases' => ['medical gas commissioning', 'mgps testing'],
                            ],
                        ],
                    ],
                ],
            ],
            'SPECIALIST_EQUIPMENT' => [
                'description' => 'Commercial kitchen, laundry and other specialist equipment installation, interfaces and commissioning.',
                'sections' => [
                    [
                        'code' => '28-01',
                        'name' => 'Commercial Kitchen',
                        'activities' => [
                            [
                                'code' => 'SPEC_KITCHEN_EQUIP',
                                'name' => 'Commercial Kitchen Equipment Installation',
                                'unit' => 'Nos',
                                'aliases' => ['commercial kitchen equipment', 'kitchen equipment'],
                            ],
                            [
                                'code' => 'SPEC_KITCHEN_HOOD',
                                'name' => 'Kitchen Exhaust Hood Installation',
                                'unit' => 'Nos',
                                'aliases' => ['kitchen hood', 'exhaust hood'],
                            ],
                            [
                                'code' => 'SPEC_KITCHEN_DUCT',
                                'name' => 'Kitchen Exhaust Duct Installation',
                                'unit' => 'Sq.m',
                                'aliases' => ['kitchen exhaust duct', 'hood duct'],
                            ],
                            [
                                'code' => 'SPEC_KITCHEN_GAS',
                                'name' => 'Commercial Kitchen Gas Piping Installation',
                                'unit' => 'Rm',
                                'aliases' => ['kitchen gas pipe', 'lpg pipeline kitchen'],
                            ],
                            [
                                'code' => 'SPEC_GREASE_TRAP',
                                'name' => 'Grease Trap Installation',
                                'unit' => 'Nos',
                                'aliases' => ['grease trap'],
                            ],
                        ],
                    ],
                    [
                        'code' => '28-02',
                        'name' => 'Laundry',
                        'activities' => [
                            [
                                'code' => 'SPEC_WASHER',
                                'name' => 'Commercial Washer Installation',
                                'unit' => 'Nos',
                                'aliases' => ['laundry washer', 'washing machine commercial'],
                            ],
                            [
                                'code' => 'SPEC_DRYER',
                                'name' => 'Commercial Dryer Installation',
                                'unit' => 'Nos',
                                'aliases' => ['laundry dryer', 'tumble dryer'],
                            ],
                            [
                                'code' => 'SPEC_LAUNDRY_EQUIP',
                                'name' => 'Laundry Equipment Installation',
                                'unit' => 'Nos',
                                'aliases' => ['laundry equipment'],
                            ],
                            [
                                'code' => 'SPEC_LAUNDRY_EXHAUST',
                                'name' => 'Laundry Exhaust Installation',
                                'unit' => 'Job',
                                'aliases' => ['laundry exhaust', 'dryer exhaust'],
                            ],
                        ],
                    ],
                    [
                        'code' => '28-03',
                        'name' => 'Specialist Equipment Interfaces',
                        'activities' => [
                            [
                                'code' => 'SPEC_EQUIP_BASE',
                                'name' => 'Specialist Equipment Base / Plinth Preparation',
                                'unit' => 'Nos',
                                'aliases' => ['equipment foundation', 'equipment plinth'],
                            ],
                            [
                                'code' => 'SPEC_EQUIP_POWER',
                                'name' => 'Specialist Equipment Power Interface',
                                'unit' => 'Point',
                                'aliases' => ['equipment power connection', 'machine power point'],
                            ],
                            [
                                'code' => 'SPEC_EQUIP_WATER',
                                'name' => 'Specialist Equipment Water / Drain Interface',
                                'unit' => 'Point',
                                'aliases' => ['equipment plumbing connection', 'machine water connection'],
                            ],
                            [
                                'code' => 'SPEC_EQUIP_INSTALL',
                                'name' => 'Specialist Equipment Installation',
                                'unit' => 'Nos',
                                'aliases' => ['special equipment installation', 'equipment fixing'],
                            ],
                        ],
                    ],
                    [
                        'code' => '28-04',
                        'name' => 'Testing & Handover',
                        'activities' => [
                            [
                                'code' => 'SPEC_EQUIP_TEST',
                                'name' => 'Specialist Equipment Testing',
                                'unit' => 'Nos',
                                'aliases' => ['equipment testing', 'machine test'],
                            ],
                            [
                                'code' => 'SPEC_EQUIP_COMMISSION',
                                'name' => 'Specialist Equipment Commissioning',
                                'unit' => 'Nos',
                                'aliases' => ['equipment commissioning', 'machine commissioning'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
