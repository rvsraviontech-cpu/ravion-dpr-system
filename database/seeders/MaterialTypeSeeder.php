<?php

namespace Database\Seeders;

use App\Models\MaterialType;
use App\Models\UnitMaster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaterialTypeSeeder extends Seeder
{
    /**
     * Seed reusable construction material types.
     */
    public function run(): void
    {
        $materials = [
            /*
            |--------------------------------------------------------------------------
            | Cementitious Materials
            |--------------------------------------------------------------------------
            */
            ['Cementitious Materials', 'Cement', 'Bag'],
            ['Cementitious Materials', 'White Cement', 'Bag'],
            ['Cementitious Materials', 'Ready Mix Concrete', 'Cum'],
            ['Cementitious Materials', 'Dry Mix Mortar', 'Bag'],
            ['Cementitious Materials', 'Repair Mortar', 'Bag'],
            ['Cementitious Materials', 'Non-Shrink Grout', 'Bag'],
            ['Cementitious Materials', 'Micro Concrete', 'Bag'],
            ['Cementitious Materials', 'Concrete Admixture', 'Litre'],
            ['Cementitious Materials', 'Curing Compound', 'Litre'],
            ['Cementitious Materials', 'Bonding Agent', 'Litre'],

            /*
            |--------------------------------------------------------------------------
            | Reinforcement, Steel & Metals
            |--------------------------------------------------------------------------
            */
            ['Steel & Metals', 'Reinforcement Steel', 'Kg'],
            ['Steel & Metals', 'Structural Steel', 'Kg'],
            ['Steel & Metals', 'Mild Steel', 'Kg'],
            ['Steel & Metals', 'Stainless Steel', 'Kg'],
            ['Steel & Metals', 'Galvanized Iron', 'Kg'],
            ['Steel & Metals', 'Binding Wire', 'Kg'],
            ['Steel & Metals', 'Welded Wire Mesh', 'Sqm'],
            ['Steel & Metals', 'Expanded Metal Mesh', 'Sqm'],
            ['Steel & Metals', 'Metal Roofing Sheet', 'Sqm'],
            ['Steel & Metals', 'Aluminium Section', 'Meter'],

            /*
            |--------------------------------------------------------------------------
            | Sand, Aggregates & Filling
            |--------------------------------------------------------------------------
            */
            ['Aggregates & Filling', 'River Sand', 'Cum'],
            ['Aggregates & Filling', 'M Sand', 'Cum'],
            ['Aggregates & Filling', 'Plaster Sand', 'Cum'],
            ['Aggregates & Filling', 'Fine Aggregate', 'Cum'],
            ['Aggregates & Filling', 'Coarse Aggregate', 'Cum'],
            ['Aggregates & Filling', 'Stone Dust', 'Cum'],
            ['Aggregates & Filling', 'Gravel', 'Cum'],
            ['Aggregates & Filling', 'Moorum', 'Cum'],
            ['Aggregates & Filling', 'Selected Earth', 'Cum'],
            ['Aggregates & Filling', 'Granular Sub-Base Material', 'Cum'],

            /*
            |--------------------------------------------------------------------------
            | Masonry Materials
            |--------------------------------------------------------------------------
            */
            ['Masonry Materials', 'Clay Brick', 'Nos'],
            ['Masonry Materials', 'Fly Ash Brick', 'Nos'],
            ['Masonry Materials', 'AAC Block', 'Nos'],
            ['Masonry Materials', 'Solid Concrete Block', 'Nos'],
            ['Masonry Materials', 'Hollow Concrete Block', 'Nos'],
            ['Masonry Materials', 'Cement Brick', 'Nos'],
            ['Masonry Materials', 'Laterite Block', 'Nos'],
            ['Masonry Materials', 'Natural Stone Block', 'Nos'],
            ['Masonry Materials', 'Block Jointing Mortar', 'Bag'],
            ['Masonry Materials', 'Masonry Mesh', 'Meter'],

            /*
            |--------------------------------------------------------------------------
            | Formwork & Scaffolding
            |--------------------------------------------------------------------------
            */
            ['Formwork & Scaffolding', 'Shuttering Plywood', 'Sheet'],
            ['Formwork & Scaffolding', 'Film Faced Plywood', 'Sheet'],
            ['Formwork & Scaffolding', 'Timber', 'Cft'],
            ['Formwork & Scaffolding', 'Wooden Batten', 'Meter'],
            ['Formwork & Scaffolding', 'Formwork Oil', 'Litre'],
            ['Formwork & Scaffolding', 'Scaffolding Pipe', 'Meter'],
            ['Formwork & Scaffolding', 'Adjustable Prop', 'Nos'],
            ['Formwork & Scaffolding', 'Scaffolding Coupler', 'Nos'],
            ['Formwork & Scaffolding', 'Base Jack', 'Nos'],
            ['Formwork & Scaffolding', 'U Head Jack', 'Nos'],

            /*
            |--------------------------------------------------------------------------
            | Waterproofing & Sealants
            |--------------------------------------------------------------------------
            */
            ['Waterproofing', 'Waterproofing Chemical', 'Litre'],
            ['Waterproofing', 'Waterproofing Membrane', 'Sqm'],
            ['Waterproofing', 'APP Membrane', 'Sqm'],
            ['Waterproofing', 'Waterproof Coating', 'Kg'],
            ['Waterproofing', 'Crystalline Waterproofing Compound', 'Kg'],
            ['Waterproofing', 'Construction Joint Sealant', 'Nos'],
            ['Waterproofing', 'Silicone Sealant', 'Nos'],
            ['Waterproofing', 'Polysulphide Sealant', 'Kg'],
            ['Waterproofing', 'Water Stopper', 'Meter'],
            ['Waterproofing', 'Expansion Joint Filler', 'Meter'],

            /*
            |--------------------------------------------------------------------------
            | Flooring, Tiles & Stone
            |--------------------------------------------------------------------------
            */
            ['Flooring & Stone', 'Floor Tile', 'Sqft'],
            ['Flooring & Stone', 'Wall Tile', 'Sqft'],
            ['Flooring & Stone', 'Vitrified Tile', 'Sqft'],
            ['Flooring & Stone', 'Ceramic Tile', 'Sqft'],
            ['Flooring & Stone', 'Anti-Skid Tile', 'Sqft'],
            ['Flooring & Stone', 'Granite', 'Sqft'],
            ['Flooring & Stone', 'Marble', 'Sqft'],
            ['Flooring & Stone', 'Kota Stone', 'Sqft'],
            ['Flooring & Stone', 'Tile Adhesive', 'Bag'],
            ['Flooring & Stone', 'Tile Grout', 'Kg'],
            ['Flooring & Stone', 'Tile Spacer', 'Nos'],
            ['Flooring & Stone', 'Skirting', 'Rft'],

            /*
            |--------------------------------------------------------------------------
            | Painting & Surface Finishes
            |--------------------------------------------------------------------------
            */
            ['Painting & Finishes', 'Wall Putty', 'Bag'],
            ['Painting & Finishes', 'Interior Primer', 'Litre'],
            ['Painting & Finishes', 'Exterior Primer', 'Litre'],
            ['Painting & Finishes', 'Interior Emulsion Paint', 'Litre'],
            ['Painting & Finishes', 'Exterior Emulsion Paint', 'Litre'],
            ['Painting & Finishes', 'Enamel Paint', 'Litre'],
            ['Painting & Finishes', 'Texture Paint', 'Kg'],
            ['Painting & Finishes', 'Wood Polish', 'Litre'],
            ['Painting & Finishes', 'Metal Primer', 'Litre'],
            ['Painting & Finishes', 'Paint Thinner', 'Litre'],

            /*
            |--------------------------------------------------------------------------
            | Doors, Windows, Glass & Hardware
            |--------------------------------------------------------------------------
            */
            ['Doors & Windows', 'Flush Door', 'Nos'],
            ['Doors & Windows', 'Wooden Door', 'Nos'],
            ['Doors & Windows', 'Fire Rated Door', 'Nos'],
            ['Doors & Windows', 'UPVC Window', 'Sqft'],
            ['Doors & Windows', 'Aluminium Window', 'Sqft'],
            ['Doors & Windows', 'Glass', 'Sqft'],
            ['Doors & Windows', 'Door Frame', 'Nos'],
            ['Doors & Windows', 'Door Hardware', 'Set'],
            ['Doors & Windows', 'Door Lock', 'Nos'],
            ['Doors & Windows', 'Door Closer', 'Nos'],

            /*
            |--------------------------------------------------------------------------
            | Electrical Systems
            |--------------------------------------------------------------------------
            */
            ['Electrical', 'Electrical Wire', 'Meter'],
            ['Electrical', 'Electrical Cable', 'Meter'],
            ['Electrical', 'Flexible Cable', 'Meter'],
            ['Electrical', 'PVC Conduit', 'Meter'],
            ['Electrical', 'GI Conduit', 'Meter'],
            ['Electrical', 'Conduit Fitting', 'Nos'],
            ['Electrical', 'Modular Switch', 'Nos'],
            ['Electrical', 'Electrical Socket', 'Nos'],
            ['Electrical', 'Switch Box', 'Nos'],
            ['Electrical', 'Junction Box', 'Nos'],
            ['Electrical', 'MCB', 'Nos'],
            ['Electrical', 'MCCB', 'Nos'],
            ['Electrical', 'RCCB', 'Nos'],
            ['Electrical', 'Distribution Board', 'Nos'],
            ['Electrical', 'Cable Tray', 'Meter'],
            ['Electrical', 'Earthing Electrode', 'Nos'],
            ['Electrical', 'LED Light Fixture', 'Nos'],
            ['Electrical', 'Ceiling Fan', 'Nos'],
            ['Electrical', 'Exhaust Fan', 'Nos'],

            /*
            |--------------------------------------------------------------------------
            | Plumbing & Water Supply
            |--------------------------------------------------------------------------
            */
            ['Plumbing', 'CPVC Pipe', 'Meter'],
            ['Plumbing', 'UPVC Pipe', 'Meter'],
            ['Plumbing', 'PVC Pipe', 'Meter'],
            ['Plumbing', 'SWR Pipe', 'Meter'],
            ['Plumbing', 'PPR Pipe', 'Meter'],
            ['Plumbing', 'HDPE Pipe', 'Meter'],
            ['Plumbing', 'GI Pipe', 'Meter'],
            ['Plumbing', 'Pipe Fitting', 'Nos'],
            ['Plumbing', 'Valve', 'Nos'],
            ['Plumbing', 'Water Tap', 'Nos'],
            ['Plumbing', 'Floor Trap', 'Nos'],
            ['Plumbing', 'Drain Cover', 'Nos'],
            ['Plumbing', 'Water Tank', 'Nos'],
            ['Plumbing', 'Water Pump', 'Nos'],
            ['Plumbing', 'Plumbing Adhesive', 'Nos'],

            /*
            |--------------------------------------------------------------------------
            | Sanitary Ware
            |--------------------------------------------------------------------------
            */
            ['Sanitary', 'Water Closet', 'Nos'],
            ['Sanitary', 'Wash Basin', 'Nos'],
            ['Sanitary', 'Urinal', 'Nos'],
            ['Sanitary', 'Kitchen Sink', 'Nos'],
            ['Sanitary', 'Shower', 'Nos'],
            ['Sanitary', 'Health Faucet', 'Nos'],
            ['Sanitary', 'Bathroom Accessory', 'Set'],
            ['Sanitary', 'Faucet', 'Nos'],
            ['Sanitary', 'Flush Tank', 'Nos'],

            /*
            |--------------------------------------------------------------------------
            | Fire Fighting
            |--------------------------------------------------------------------------
            */
            ['Fire Fighting', 'Fire Fighting Pipe', 'Meter'],
            ['Fire Fighting', 'Fire Valve', 'Nos'],
            ['Fire Fighting', 'Fire Sprinkler', 'Nos'],
            ['Fire Fighting', 'Fire Extinguisher', 'Nos'],
            ['Fire Fighting', 'Hose Reel', 'Nos'],
            ['Fire Fighting', 'Fire Hose', 'Meter'],
            ['Fire Fighting', 'Landing Valve', 'Nos'],
            ['Fire Fighting', 'Fire Alarm Device', 'Nos'],

            /*
            |--------------------------------------------------------------------------
            | HVAC
            |--------------------------------------------------------------------------
            */
            ['HVAC', 'HVAC Duct', 'Sqm'],
            ['HVAC', 'Copper Pipe', 'Meter'],
            ['HVAC', 'HVAC Insulation', 'Sqm'],
            ['HVAC', 'Air Diffuser', 'Nos'],
            ['HVAC', 'Air Grille', 'Nos'],
            ['HVAC', 'Flexible Duct', 'Meter'],
            ['HVAC', 'Refrigerant Gas', 'Kg'],
            ['HVAC', 'Air Conditioning Unit', 'Nos'],

            /*
            |--------------------------------------------------------------------------
            | False Ceiling & Drywall
            |--------------------------------------------------------------------------
            */
            ['False Ceiling & Drywall', 'Gypsum Board', 'Sqft'],
            ['False Ceiling & Drywall', 'Calcium Silicate Board', 'Sqft'],
            ['False Ceiling & Drywall', 'Ceiling Channel', 'Meter'],
            ['False Ceiling & Drywall', 'Wall Stud', 'Meter'],
            ['False Ceiling & Drywall', 'Suspension Rod', 'Meter'],
            ['False Ceiling & Drywall', 'Jointing Compound', 'Kg'],
            ['False Ceiling & Drywall', 'Drywall Screw', 'Nos'],
            ['False Ceiling & Drywall', 'Joint Tape', 'Meter'],

            /*
            |--------------------------------------------------------------------------
            | Roofing & External Development
            |--------------------------------------------------------------------------
            */
            ['Roofing & External Works', 'Roofing Sheet', 'Sqm'],
            ['Roofing & External Works', 'Roof Tile', 'Sqft'],
            ['Roofing & External Works', 'Paver Block', 'Nos'],
            ['Roofing & External Works', 'Kerb Stone', 'Nos'],
            ['Roofing & External Works', 'Drainage Channel', 'Meter'],
            ['Roofing & External Works', 'Geotextile', 'Sqm'],
            ['Roofing & External Works', 'Landscape Soil', 'Cum'],
            ['Roofing & External Works', 'Grass Turf', 'Sqft'],

            /*
            |--------------------------------------------------------------------------
            | Hardware & Consumables
            |--------------------------------------------------------------------------
            */
            ['Hardware & Consumables', 'Nail', 'Kg'],
            ['Hardware & Consumables', 'Screw', 'Nos'],
            ['Hardware & Consumables', 'Bolt and Nut', 'Set'],
            ['Hardware & Consumables', 'Anchor Fastener', 'Nos'],
            ['Hardware & Consumables', 'Welding Electrode', 'Kg'],
            ['Hardware & Consumables', 'Cutting Disc', 'Nos'],
            ['Hardware & Consumables', 'Grinding Disc', 'Nos'],
            ['Hardware & Consumables', 'Epoxy Adhesive', 'Kg'],
            ['Hardware & Consumables', 'Cleaning Chemical', 'Litre'],
            ['Hardware & Consumables', 'Safety Barricading Tape', 'Roll'],
        ];

        $sequenceByGroup = [];

        foreach ($materials as [$group, $typeName, $unitName]) {
            $sequenceByGroup[$group] =
                ($sequenceByGroup[$group] ?? 0) + 10;

            MaterialType::updateOrCreate(
                [
                    'material_type_name' => $typeName,
                ],
                [
                    'material_group' => $group,
                    'material_type_code' =>
                        $this->makeCode($group, $typeName),
                    'unit_master_id' =>
                        $this->findUnitId($unitName),
                    'sequence' => $sequenceByGroup[$group],
                    'is_active' => true,
                    'remarks' => 'Seeded construction material type.',
                    'created_by' => null,
                ]
            );
        }
    }

    /**
     * Generate a stable internal reference code.
     */
    private function makeCode(
        string $group,
        string $typeName
    ): string {
        $groupCode = Str::upper(
            Str::substr(
                Str::slug($group, ''),
                0,
                4
            )
        );

        $typeCode = Str::upper(
            Str::substr(
                Str::slug($typeName, ''),
                0,
                8
            )
        );

        return $groupCode . '-' . $typeCode;
    }

    /**
     * Find the corresponding Unit Master record.
     *
     * A missing unit does not stop the seeder. The Material Type
     * will be created with a null default unit and can be corrected
     * later from the master screen.
     */
    private function findUnitId(string $requestedUnit): ?int
    {
        $aliases = [
            'Bag' => ['bag', 'bags'],
            'Kg' => ['kg', 'kgs', 'kilogram', 'kilograms'],
            'Litre' => ['litre', 'litres', 'liter', 'liters', 'ltr'],
            'Meter' => ['meter', 'meters', 'metre', 'metres', 'mtr', 'rmt'],
            'Sqm' => ['sqm', 'sq.m', 'square meter', 'square metre'],
            'Cum' => ['cum', 'cu.m', 'cubic meter', 'cubic metre'],
            'Nos' => ['nos', 'no', 'number', 'numbers'],
            'Sheet' => ['sheet', 'sheets'],
            'Cft' => ['cft', 'cu.ft', 'cubic feet'],
            'Sqft' => ['sqft', 'sq.ft', 'square feet', 'square foot'],
            'Rft' => ['rft', 'running feet', 'running foot'],
            'Set' => ['set', 'sets'],
            'Roll' => ['roll', 'rolls'],
        ];

        $searchNames = $aliases[$requestedUnit] ?? [
            Str::lower($requestedUnit),
        ];

        return UnitMaster::query()
            ->where('is_active', true)
            ->where(function ($query) use ($searchNames) {
                foreach ($searchNames as $name) {
                    $query->orWhereRaw(
                        'LOWER(TRIM(unit_name)) = ?',
                        [Str::lower($name)]
                    );
                }
            })
            ->value('id');
    }
}