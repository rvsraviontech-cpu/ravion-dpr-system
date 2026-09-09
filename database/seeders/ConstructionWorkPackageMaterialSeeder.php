<?php

namespace Database\Seeders;

use App\Models\ConstructionWorkPackage;
use App\Models\MaterialType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConstructionWorkPackageMaterialSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $workPackages = ConstructionWorkPackage::query()
                ->pluck('id', 'code');

            $materials = MaterialType::query()
                ->get()
                ->keyBy('material_type_name');

            $mappings = [

                /*
                |--------------------------------------------------------------------------
                | Aggregates & Filling
                |--------------------------------------------------------------------------
                */

                'Coarse Aggregate' => [
                    'FOUND-PCC',
                    'FOUND-FOOT',
                    'FOUND-RAFT',
                    'RCC-CONCRETE',
                ],

                'Fine Aggregate' => [
                    'FOUND-PCC',
                    'FOUND-FOOT',
                    'FOUND-RAFT',
                    'RCC-CONCRETE',
                    'MASON-MORTAR',
                    'PLASTER-INT',
                    'PLASTER-EXT',
                    'FLOOR-SCREED',
                ],

                'Granular Sub-Base Material' => [
                    'EARTH-SUBBASE',
                    'EXT-ROAD',
                    'EXT-PAVER',
                ],

                'Gravel' => [
                    'EARTH-FILL',
                    'EARTH-SUBBASE',
                    'EXT-ROAD',
                    'EXT-DRAIN',
                ],

                'M Sand' => [
                    'FOUND-PCC',
                    'RCC-CONCRETE',
                    'MASON-MORTAR',
                    'PLASTER-INT',
                    'PLASTER-EXT',
                    'FLOOR-SCREED',
                ],

                'Moorum' => [
                    'EARTH-FILL',
                    'EARTH-COMP',
                    'EARTH-SUBBASE',
                    'EXT-ROAD',
                ],

                'Plaster Sand' => [
                    'PLASTER-INT',
                    'PLASTER-EXT',
                    'PLASTER-CEIL',
                    'PLASTER-REPAIR',
                ],

                'River Sand' => [
                    'FOUND-PCC',
                    'RCC-CONCRETE',
                    'MASON-MORTAR',
                    'PLASTER-INT',
                    'PLASTER-EXT',
                    'FLOOR-SCREED',
                ],

                'Selected Earth' => [
                    'EARTH-FILL',
                    'EARTH-COMP',
                ],

                'Stone Dust' => [
                    'EARTH-SUBBASE',
                    'EXT-PAVER',
                    'EXT-KERB',
                    'FLOOR-SCREED',
                ],

                /*
                |--------------------------------------------------------------------------
                | Cementitious Materials
                |--------------------------------------------------------------------------
                */

                'Cement' => [
                    'FOUND-PCC',
                    'FOUND-FOOT',
                    'FOUND-PILE',
                    'FOUND-RAFT',
                    'FOUND-PED',
                    'FOUND-PLINTH',
                    'FOUND-UGT',
                    'RCC-CONCRETE',
                    'MASON-MORTAR',
                    'PLASTER-INT',
                    'PLASTER-EXT',
                    'PLASTER-CEIL',
                    'PLASTER-REPAIR',
                    'FLOOR-SCREED',
                ],

                'White Cement' => [
                    'FLOOR-ADH',
                    'PAINT-PUTTY',
                    'PAINT-TOUCH',
                ],

                'Ready Mix Concrete' => [
                    'FOUND-FOOT',
                    'FOUND-PILE',
                    'FOUND-RAFT',
                    'FOUND-PED',
                    'FOUND-PLINTH',
                    'FOUND-UGT',
                    'RCC-COLUMN',
                    'RCC-BEAM',
                    'RCC-SLAB',
                    'RCC-STAIR',
                    'RCC-WALL',
                    'RCC-CONCRETE',
                ],

                'Dry Mix Mortar' => [
                    'MASON-MORTAR',
                    'PLASTER-INT',
                    'PLASTER-EXT',
                    'PLASTER-REPAIR',
                ],

                'Repair Mortar' => [
                    'RCC-REPAIR',
                    'PLASTER-REPAIR',
                    'TEST-SNAG',
                ],

                'Non-Shrink Grout' => [
                    'RCC-REPAIR',
                    'STEEL-STRUCT',
                    'TEST-SNAG',
                ],

                'Micro Concrete' => [
                    'RCC-REPAIR',
                    'TEST-SNAG',
                ],

                'Concrete Admixture' => [
                    'FOUND-PCC',
                    'FOUND-FOOT',
                    'FOUND-RAFT',
                    'RCC-CONCRETE',
                ],

                'Curing Compound' => [
                    'RCC-CURING',
                ],

                'Bonding Agent' => [
                    'RCC-REPAIR',
                    'PLASTER-REPAIR',
                    'FLOOR-SCREED',
                    'TEST-SNAG',
                ],

                /*
                |--------------------------------------------------------------------------
                | Steel & Metals
                |--------------------------------------------------------------------------
                */

                'Reinforcement Steel' => [
                    'FOUND-FOOT',
                    'FOUND-PILE',
                    'FOUND-RAFT',
                    'FOUND-PED',
                    'FOUND-PLINTH',
                    'FOUND-UGT',
                    'RCC-REBAR',
                    'RCC-COLUMN',
                    'RCC-BEAM',
                    'RCC-SLAB',
                    'RCC-STAIR',
                    'RCC-WALL',
                ],

                'Binding Wire' => [
                    'RCC-REBAR',
                    'FOUND-FOOT',
                    'FOUND-RAFT',
                ],

                'Structural Steel' => [
                    'STEEL-STRUCT',
                    'STEEL-FAB',
                ],

                'Mild Steel' => [
                    'STEEL-STRUCT',
                    'STEEL-FAB',
                    'STEEL-RAIL',
                    'STEEL-GRATE',
                ],

                'Stainless Steel' => [
                    'STEEL-FAB',
                    'STEEL-RAIL',
                    'STEEL-GRATE',
                ],

                'Galvanized Iron' => [
                    'STEEL-FAB',
                    'STEEL-GRATE',
                    'ROOF-SHEET',
                ],

                'Welded Wire Mesh' => [
                    'RCC-REBAR',
                    'MASON-REINF',
                    'FLOOR-SCREED',
                ],

                'Expanded Metal Mesh' => [
                    'MASON-REINF',
                    'PLASTER-REPAIR',
                    'STEEL-GRATE',
                ],

                'Metal Roofing Sheet' => [
                    'ROOF-SHEET',
                ],

                'Aluminium Section' => [
                    'DOOR-ALU',
                    'DOOR-GLASS',
                    'STEEL-FAB',
                ],

                /*
                |--------------------------------------------------------------------------
                | Masonry Materials
                |--------------------------------------------------------------------------
                */

                'Clay Brick' => [
                    'MASON-BRICK',
                ],

                'Fly Ash Brick' => [
                    'MASON-BRICK',
                ],

                'AAC Block' => [
                    'MASON-AAC',
                    'MASON-PART',
                ],

                'Solid Concrete Block' => [
                    'MASON-CONCBLOCK',
                    'MASON-PART',
                    'EXT-COMPOUND',
                ],

                'Hollow Concrete Block' => [
                    'MASON-CONCBLOCK',
                    'MASON-PART',
                ],

                'Cement Brick' => [
                    'MASON-BRICK',
                    'EXT-COMPOUND',
                ],

                'Laterite Block' => [
                    'MASON-CONCBLOCK',
                    'EXT-COMPOUND',
                ],

                'Natural Stone Block' => [
                    'MASON-CONCBLOCK',
                    'EXT-COMPOUND',
                ],

                'Block Jointing Mortar' => [
                    'MASON-AAC',
                    'MASON-CONCBLOCK',
                    'MASON-PART',
                ],

                'Masonry Mesh' => [
                    'MASON-REINF',
                    'MASON-BRICK',
                    'MASON-AAC',
                    'MASON-CONCBLOCK',
                ],

                /*
                |--------------------------------------------------------------------------
                | Formwork & Scaffolding
                |--------------------------------------------------------------------------
                */

                'Shuttering Plywood' => [
                    'RCC-FORM',
                    'TEMP-PLY',
                ],

                'Film Faced Plywood' => [
                    'RCC-FORM',
                    'TEMP-PLY',
                ],

                'Timber' => [
                    'RCC-FORM',
                    'TEMP-ACCESS',
                ],

                'Wooden Batten' => [
                    'RCC-FORM',
                    'TEMP-ACCESS',
                ],

                'Formwork Oil' => [
                    'RCC-FORM',
                    'TEMP-ACCESS',
                ],

                'Scaffolding Pipe' => [
                    'TEMP-SCAF',
                ],

                'Adjustable Prop' => [
                    'TEMP-PROP',
                    'RCC-FORM',
                ],

                'Scaffolding Coupler' => [
                    'TEMP-SCAF',
                    'TEMP-ACCESS',
                ],

                'Base Jack' => [
                    'TEMP-SCAF',
                    'TEMP-ACCESS',
                ],

                'U Head Jack' => [
                    'TEMP-SCAF',
                    'TEMP-PROP',
                ],

                /*
                |--------------------------------------------------------------------------
                | Waterproofing
                |--------------------------------------------------------------------------
                */

                'Waterproofing Chemical' => [
                    'WATER-BASE',
                    'WATER-TOILET',
                    'WATER-TERRACE',
                    'WATER-BALCONY',
                    'WATER-TANK',
                ],

                'Waterproofing Membrane' => [
                    'WATER-BASE',
                    'WATER-TOILET',
                    'WATER-TERRACE',
                    'WATER-BALCONY',
                    'WATER-TANK',
                ],

                'APP Membrane' => [
                    'WATER-BASE',
                    'WATER-TERRACE',
                    'ROOF-SEAL',
                ],

                'Waterproof Coating' => [
                    'WATER-TOILET',
                    'WATER-TERRACE',
                    'WATER-BALCONY',
                    'WATER-TANK',
                ],

                'Crystalline Waterproofing Compound' => [
                    'WATER-BASE',
                    'WATER-TANK',
                    'FOUND-UGT',
                ],

                'Construction Joint Sealant' => [
                    'WATER-JOINT',
                    'WATER-SEAL',
                ],

                'Silicone Sealant' => [
                    'WATER-SEAL',
                    'DOOR-GLASS',
                    'ROOF-SEAL',
                ],

                'Polysulphide Sealant' => [
                    'WATER-JOINT',
                    'WATER-SEAL',
                ],

                'Water Stopper' => [
                    'WATER-JOINT',
                    'FOUND-UGT',
                    'RCC-WALL',
                ],

                'Expansion Joint Filler' => [
                    'WATER-JOINT',
                    'WATER-SEAL',
                ],

                /*
                |--------------------------------------------------------------------------
                | Flooring & Stone
                |--------------------------------------------------------------------------
                */

                'Floor Tile' => [
                    'FLOOR-TILE',
                ],

                'Wall Tile' => [
                    'FLOOR-WALLTILE',
                ],

                'Vitrified Tile' => [
                    'FLOOR-TILE',
                ],

                'Ceramic Tile' => [
                    'FLOOR-TILE',
                    'FLOOR-WALLTILE',
                ],

                'Anti-Skid Tile' => [
                    'FLOOR-TILE',
                ],

                'Granite' => [
                    'FLOOR-GRANITE',
                    'FLOOR-SKIRT',
                ],

                'Marble' => [
                    'FLOOR-MARBLE',
                    'FLOOR-SKIRT',
                ],

                'Kota Stone' => [
                    'FLOOR-STONE',
                    'FLOOR-SKIRT',
                ],

                'Tile Adhesive' => [
                    'FLOOR-TILE',
                    'FLOOR-WALLTILE',
                    'FLOOR-ADH',
                ],

                'Tile Grout' => [
                    'FLOOR-TILE',
                    'FLOOR-WALLTILE',
                    'FLOOR-ADH',
                ],

                'Tile Spacer' => [
                    'FLOOR-TILE',
                    'FLOOR-WALLTILE',
                ],

                'Skirting' => [
                    'FLOOR-SKIRT',
                ],

                /*
                |--------------------------------------------------------------------------
                | Painting & Finishes
                |--------------------------------------------------------------------------
                */

                'Wall Putty' => [
                    'PAINT-PUTTY',
                    'PAINT-INT',
                    'PAINT-EXT',
                ],

                'Interior Primer' => [
                    'PAINT-PRIMER',
                    'PAINT-INT',
                ],

                'Exterior Primer' => [
                    'PAINT-PRIMER',
                    'PAINT-EXT',
                ],

                'Interior Emulsion Paint' => [
                    'PAINT-INT',
                ],

                'Exterior Emulsion Paint' => [
                    'PAINT-EXT',
                ],

                'Enamel Paint' => [
                    'PAINT-METAL',
                    'PAINT-WOOD',
                    'PAINT-TOUCH',
                ],

                'Texture Paint' => [
                    'PAINT-TEXTURE',
                    'PAINT-EXT',
                ],

                'Wood Polish' => [
                    'PAINT-WOOD',
                    'DOOR-WOOD',
                ],

                'Metal Primer' => [
                    'PAINT-METAL',
                    'STEEL-FAB',
                ],

                'Paint Thinner' => [
                    'PAINT-INT',
                    'PAINT-EXT',
                    'PAINT-METAL',
                    'PAINT-WOOD',
                    'PAINT-TOUCH',
                ],

                /*
                |--------------------------------------------------------------------------
                | Doors & Windows
                |--------------------------------------------------------------------------
                */

                'Flush Door' => [
                    'DOOR-SHUTTER',
                    'DOOR-WOOD',
                ],

                'Wooden Door' => [
                    'DOOR-WOOD',
                    'DOOR-SHUTTER',
                ],

                'Fire Rated Door' => [
                    'DOOR-FIRE',
                ],

                'UPVC Window' => [
                    'DOOR-UPVC',
                ],

                'Aluminium Window' => [
                    'DOOR-ALU',
                ],

                'Glass' => [
                    'DOOR-GLASS',
                ],

                'Door Frame' => [
                    'DOOR-FRAME',
                ],

                'Door Hardware' => [
                    'DOOR-HARD',
                ],

                'Door Lock' => [
                    'DOOR-HARD',
                ],

                'Door Closer' => [
                    'DOOR-HARD',
                    'DOOR-FIRE',
                ],

                /*
                |--------------------------------------------------------------------------
                | Electrical
                |--------------------------------------------------------------------------
                */

                'PVC Conduit' => [
                    'ELE-CONDUIT',
                ],

                'GI Conduit' => [
                    'ELE-CONDUIT',
                ],

                'Conduit Fitting' => [
                    'ELE-CONDUIT',
                ],

                'Junction Box' => [
                    'ELE-CONDUIT',
                ],

                'Switch Box' => [
                    'ELE-CONDUIT',
                    'ELE-SWITCH',
                ],

                'Electrical Wire' => [
                    'ELE-WIRING',
                ],

                'Electrical Cable' => [
                    'ELE-CABLE',
                ],

                'Flexible Cable' => [
                    'ELE-CABLE',
                    'ELE-WIRING',
                ],

                'Cable Tray' => [
                    'ELE-TRAY',
                ],

                'Distribution Board' => [
                    'ELE-DB',
                ],

                'MCB' => [
                    'ELE-PROTECT',
                    'ELE-DB',
                ],

                'MCCB' => [
                    'ELE-PROTECT',
                    'ELE-DB',
                ],

                'RCCB' => [
                    'ELE-PROTECT',
                    'ELE-DB',
                ],

                'Modular Switch' => [
                    'ELE-SWITCH',
                ],

                'Electrical Socket' => [
                    'ELE-SWITCH',
                ],

                'Earthing Electrode' => [
                    'ELE-EARTH',
                ],

                'LED Light Fixture' => [
                    'ELE-LIGHT',
                ],

                'Ceiling Fan' => [
                    'ELE-FAN',
                ],

                'Exhaust Fan' => [
                    'ELE-FAN',
                ],

                /*
                |--------------------------------------------------------------------------
                | Plumbing
                |--------------------------------------------------------------------------
                */

                'CPVC Pipe' => [
                    'PLB-WATER',
                ],

                'UPVC Pipe' => [
                    'PLB-WATER',
                    'PLB-DRAIN',
                    'PLB-RAIN',
                ],

                'PVC Pipe' => [
                    'PLB-WATER',
                    'PLB-DRAIN',
                    'PLB-RAIN',
                ],

                'SWR Pipe' => [
                    'PLB-DRAIN',
                    'PLB-RAIN',
                ],

                'PPR Pipe' => [
                    'PLB-WATER',
                ],

                'HDPE Pipe' => [
                    'PLB-WATER',
                    'PLB-DRAIN',
                    'EXT-DRAIN',
                ],

                'GI Pipe' => [
                    'PLB-WATER',
                    'FIRE-PIPE',
                ],

                'Pipe Fitting' => [
                    'PLB-WATER',
                    'PLB-DRAIN',
                    'PLB-RAIN',
                ],

                'Valve' => [
                    'PLB-VALVE',
                    'PLB-WATER',
                ],

                'Water Tap' => [
                    'PLB-VALVE',
                    'SAN-FAUCET',
                ],

                'Floor Trap' => [
                    'PLB-DRAIN',
                    'SAN-TRAP',
                ],

                'Drain Cover' => [
                    'PLB-DRAIN',
                    'EXT-DRAIN',
                ],

                'Water Tank' => [
                    'PLB-TANK',
                ],

                'Water Pump' => [
                    'PLB-PUMP',
                ],

                'Plumbing Adhesive' => [
                    'PLB-WATER',
                    'PLB-DRAIN',
                    'PLB-RAIN',
                ],

                /*
                |--------------------------------------------------------------------------
                | Sanitary
                |--------------------------------------------------------------------------
                */

                'Water Closet' => [
                    'SAN-WC',
                ],

                'Wash Basin' => [
                    'SAN-BASIN',
                ],

                'Urinal' => [
                    'SAN-WC',
                ],

                'Kitchen Sink' => [
                    'SAN-SINK',
                ],

                'Shower' => [
                    'SAN-SHOWER',
                ],

                'Health Faucet' => [
                    'SAN-FAUCET',
                    'SAN-ACCESS',
                ],

                'Bathroom Accessory' => [
                    'SAN-ACCESS',
                ],

                'Faucet' => [
                    'SAN-FAUCET',
                ],

                'Flush Tank' => [
                    'SAN-WC',
                ],

                /*
                |--------------------------------------------------------------------------
                | Fire Fighting
                |--------------------------------------------------------------------------
                */

                'Fire Fighting Pipe' => [
                    'FIRE-PIPE',
                ],

                'Fire Valve' => [
                    'FIRE-VALVE',
                ],

                'Fire Sprinkler' => [
                    'FIRE-SPRINK',
                ],

                'Fire Extinguisher' => [
                    'FIRE-EXT',
                ],

                'Hose Reel' => [
                    'FIRE-HYDRANT',
                ],

                'Fire Hose' => [
                    'FIRE-HYDRANT',
                ],

                'Landing Valve' => [
                    'FIRE-HYDRANT',
                    'FIRE-VALVE',
                ],

                'Fire Alarm Device' => [
                    'FIRE-ALARM',
                    'FIRE-DETECT',
                ],

                /*
                |--------------------------------------------------------------------------
                | HVAC
                |--------------------------------------------------------------------------
                */

                'HVAC Duct' => [
                    'HVAC-DUCT',
                ],

                'Copper Pipe' => [
                    'HVAC-COPPER',
                ],

                'HVAC Insulation' => [
                    'HVAC-INSUL',
                ],

                'Air Diffuser' => [
                    'HVAC-GRILLE',
                ],

                'Air Grille' => [
                    'HVAC-GRILLE',
                ],

                'Flexible Duct' => [
                    'HVAC-DUCT',
                    'HVAC-GRILLE',
                ],

                'Refrigerant Gas' => [
                    'HVAC-EQUIP',
                ],

                'Air Conditioning Unit' => [
                    'HVAC-EQUIP',
                ],

                /*
                |--------------------------------------------------------------------------
                | False Ceiling & Drywall
                |--------------------------------------------------------------------------
                */

                'Gypsum Board' => [
                    'CEIL-GYPSUM',
                    'CEIL-DRYWALL',
                ],

                'Calcium Silicate Board' => [
                    'CEIL-GYPSUM',
                    'CEIL-DRYWALL',
                ],

                'Ceiling Channel' => [
                    'CEIL-FRAME',
                    'CEIL-GYPSUM',
                ],

                'Wall Stud' => [
                    'CEIL-FRAME',
                    'CEIL-DRYWALL',
                ],

                'Suspension Rod' => [
                    'CEIL-FRAME',
                    'CEIL-GRID',
                ],

                'Jointing Compound' => [
                    'CEIL-JOINT',
                ],

                'Drywall Screw' => [
                    'CEIL-DRYWALL',
                    'CEIL-ACCESS',
                ],

                'Joint Tape' => [
                    'CEIL-JOINT',
                ],

                /*
                |--------------------------------------------------------------------------
                | Roofing & External Works
                |--------------------------------------------------------------------------
                */

                'Roofing Sheet' => [
                    'ROOF-SHEET',
                ],

                'Roof Tile' => [
                    'ROOF-SHEET',
                ],

                'Paver Block' => [
                    'EXT-PAVER',
                ],

                'Kerb Stone' => [
                    'EXT-KERB',
                ],

                'Drainage Channel' => [
                    'EXT-DRAIN',
                ],

                'Geotextile' => [
                    'EXT-ROAD',
                    'EXT-DRAIN',
                    'EXT-LAND',
                ],

                'Landscape Soil' => [
                    'EXT-LAND',
                ],

                'Grass Turf' => [
                    'EXT-LAND',
                ],

                /*
                |--------------------------------------------------------------------------
                | Hardware & Consumables
                |--------------------------------------------------------------------------
                */

                'Nail' => [
                    'CONS-FAST',
                    'RCC-FORM',
                    'TEMP-ACCESS',
                ],

                'Screw' => [
                    'CONS-FAST',
                    'DOOR-HARD',
                    'CEIL-ACCESS',
                ],

                'Bolt and Nut' => [
                    'CONS-FAST',
                    'STEEL-FAB',
                    'STEEL-STRUCT',
                ],

                'Anchor Fastener' => [
                    'CONS-FAST',
                    'STEEL-FAB',
                    'ELE-TRAY',
                    'HVAC-SUPPORT',
                ],

                'Welding Electrode' => [
                    'CONS-WELD',
                    'STEEL-WELD',
                    'STEEL-FAB',
                ],

                'Cutting Disc' => [
                    'CONS-CUT',
                    'STEEL-FAB',
                ],

                'Grinding Disc' => [
                    'CONS-CUT',
                    'STEEL-FAB',
                ],

                'Epoxy Adhesive' => [
                    'CONS-SEAL',
                    'RCC-REPAIR',
                    'FLOOR-ADH',
                    'TEST-SNAG',
                ],

                'Cleaning Chemical' => [
                    'CONS-GENERAL',
                    'TEST-HAND',
                ],

                'Safety Barricading Tape' => [
                    'SAFE-BARR',
                    'SAFE-SIGN',
                ],
            ];

            $errors = [];
            $mappedMaterialIds = [];
            $insertedMappings = 0;

            foreach ($mappings as $materialName => $packageCodes) {
                $material = $materials->get($materialName);

                if (!$material) {
                    $errors[] = "Material not found: {$materialName}";
                    continue;
                }

                $mappedMaterialIds[] = $material->id;

                foreach (array_values(array_unique($packageCodes)) as $index => $packageCode) {
                    $workPackageId = $workPackages->get($packageCode);

                    if (!$workPackageId) {
                        $errors[] =
                            "Work Package not found: {$packageCode} "
                            . "(Material: {$materialName})";

                        continue;
                    }

                    DB::table('construction_work_package_material_type')
                        ->updateOrInsert(
                            [
                                'construction_work_package_id' => $workPackageId,
                                'material_type_id' => $material->id,
                            ],
                            [
                                'is_preferred' => $index === 0,
                                'sort_order' => ($index + 1) * 10,
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );

                    $insertedMappings++;
                }
            }

            if (!empty($errors)) {
                throw new RuntimeException(
                    "Construction Work Package Material mapping failed:\n"
                    . implode("\n", $errors)
                );
            }

            $unmappedMaterials = MaterialType::query()
                ->whereNotIn('id', array_unique($mappedMaterialIds))
                ->orderBy('material_group')
                ->orderBy('material_type_name')
                ->get([
                    'material_group',
                    'material_type_name',
                ]);

            $this->command?->info(
                'Work Package material mappings processed: '
                . $insertedMappings
            );

            $this->command?->info(
                'Mapped Material Types: '
                . count(array_unique($mappedMaterialIds))
            );

            if ($unmappedMaterials->isEmpty()) {
                $this->command?->info(
                    'All existing Material Types have an initial Work Package mapping.'
                );
            } else {
                $this->command?->warn(
                    'Unmapped Material Types: '
                    . $unmappedMaterials->count()
                );

                foreach ($unmappedMaterials as $material) {
                    $this->command?->warn(
                        $material->material_group
                        . ' → '
                        . $material->material_type_name
                    );
                }
            }
        });
    }
}