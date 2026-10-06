<?php

namespace Database\Seeders;

use App\Models\MachineryTool;
use Illuminate\Database\Seeder;

class MachineryEquipmentMasterSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = [

            /*
            |--------------------------------------------------------------------------
            | EARTHMOVING & EXCAVATION
            |--------------------------------------------------------------------------
            */

            $this->item(
                'EXCAVATOR',
                'Hydraulic Excavator',
                'Earthmoving & Excavation',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true,
                'Hydraulic excavator for bulk excavation, trenching and earthwork.'
            ),

            $this->item(
                'MINI_EXCAVATOR',
                'Mini Excavator',
                'Earthmoving & Excavation',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'BACKHOE_LOADER',
                'Backhoe Loader / JCB',
                'Earthmoving & Excavation',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'BULLDOZER',
                'Bulldozer',
                'Earthmoving & Excavation',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'TRENCHER',
                'Trencher',
                'Earthmoving & Excavation',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            /*
            |--------------------------------------------------------------------------
            | LOADING & MATERIAL HANDLING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'WHEEL_LOADER',
                'Wheel Loader',
                'Loading & Material Handling',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'SKID_STEER',
                'Skid Steer Loader',
                'Loading & Material Handling',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'TELEHANDLER',
                'Telehandler',
                'Loading & Material Handling',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'FORKLIFT',
                'Forklift',
                'Loading & Material Handling',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'ELECTRIC_FORKLIFT',
                'Electric Forklift',
                'Loading & Material Handling',
                'individual',
                'hour_meter',
                'battery',
                true,
                true,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | TRANSPORTATION & HAULAGE
            |--------------------------------------------------------------------------
            */

            $this->item(
                'TIPPER_TRUCK',
                'Tipper / Dump Truck',
                'Transportation & Haulage',
                'individual',
                'odometer',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'TRACTOR_TRAILER',
                'Tractor with Trailer',
                'Transportation & Haulage',
                'individual',
                'odometer',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'WATER_TANKER',
                'Water Tanker',
                'Transportation & Haulage',
                'individual',
                'odometer',
                'diesel',
                true,
                true,
                true
            ),

            /*
            |--------------------------------------------------------------------------
            | LIFTING & HOISTING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'TOWER_CRANE',
                'Tower Crane',
                'Lifting & Hoisting',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'MOBILE_CRANE',
                'Mobile Crane',
                'Lifting & Hoisting',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'CRAWLER_CRANE',
                'Crawler Crane',
                'Lifting & Hoisting',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'MATERIAL_HOIST',
                'Material Hoist',
                'Lifting & Hoisting',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'PASSENGER_HOIST',
                'Passenger & Material Hoist',
                'Lifting & Hoisting',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'CHAIN_BLOCK',
                'Chain Pulley Block',
                'Lifting & Hoisting',
                'pooled',
                'none',
                'none',
                false,
                false,
                false
            ),

            $this->item(
                'ELECTRIC_CHAIN_HOIST',
                'Electric Chain Hoist',
                'Lifting & Hoisting',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | CONCRETE
            |--------------------------------------------------------------------------
            */

            $this->item(
                'BATCHING_PLANT',
                'Concrete Batching Plant',
                'Concrete Production & Placement',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'CONCRETE_MIXER',
                'Concrete Mixer',
                'Concrete Production & Placement',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'DIESEL_CONCRETE_MIXER',
                'Diesel Concrete Mixer',
                'Concrete Production & Placement',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'TRANSIT_MIXER',
                'Transit Mixer',
                'Concrete Production & Placement',
                'individual',
                'odometer',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'CONCRETE_PUMP',
                'Concrete Pump',
                'Concrete Production & Placement',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'BOOM_PUMP',
                'Concrete Boom Pump',
                'Concrete Production & Placement',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'NEEDLE_VIBRATOR',
                'Needle Vibrator',
                'Concrete Compaction',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'PETROL_VIBRATOR',
                'Petrol Concrete Vibrator',
                'Concrete Compaction',
                'pooled',
                'none',
                'petrol',
                true,
                false,
                true
            ),

            $this->item(
                'SURFACE_VIBRATOR',
                'Surface Vibrator',
                'Concrete Compaction',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'VIBRATING_SCREED',
                'Vibrating Screed',
                'Concrete Compaction',
                'individual',
                'none',
                'petrol',
                true,
                false,
                true
            ),

            /*
            |--------------------------------------------------------------------------
            | REBAR PROCESSING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'BAR_CUTTING_MACHINE',
                'Bar Cutting Machine',
                'Rebar Processing',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'BAR_BENDING_MACHINE',
                'Bar Bending Machine',
                'Rebar Processing',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'BAR_STRAIGHTENING_MACHINE',
                'Bar Straightening Machine',
                'Rebar Processing',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'REBAR_THREADING_MACHINE',
                'Rebar Threading Machine',
                'Rebar Processing',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | MASONRY & PLASTERING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'MORTAR_MIXER',
                'Mortar Mixer',
                'Masonry & Plastering Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'PLASTER_SPRAY_MACHINE',
                'Plaster Spray Machine',
                'Masonry & Plastering Equipment',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'BLOCK_CUTTING_MACHINE',
                'Block Cutting Machine',
                'Masonry & Plastering Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | COMPACTION
            |--------------------------------------------------------------------------
            */

            $this->item(
                'PLATE_COMPACTOR',
                'Plate Compactor',
                'Compaction Equipment',
                'individual',
                'none',
                'petrol',
                true,
                false,
                true
            ),

            $this->item(
                'TAMPING_RAMMER',
                'Tamping Rammer / Jumping Jack',
                'Compaction Equipment',
                'individual',
                'none',
                'petrol',
                true,
                false,
                true
            ),

            $this->item(
                'WALK_BEHIND_ROLLER',
                'Walk Behind Roller',
                'Compaction Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'VIBRATORY_ROLLER',
                'Vibratory Soil Compactor / Roller',
                'Compaction Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            /*
            |--------------------------------------------------------------------------
            | ROAD & EXTERNAL DEVELOPMENT
            |--------------------------------------------------------------------------
            */

            $this->item(
                'MOTOR_GRADER',
                'Motor Grader',
                'Road & External Development',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'PAVER_FINISHER',
                'Paver Finisher',
                'Road & External Development',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'ROAD_CUTTER',
                'Road Cutting Machine',
                'Road & External Development',
                'individual',
                'none',
                'diesel',
                true,
                false,
                true
            ),

            /*
            |--------------------------------------------------------------------------
            | FOUNDATION & PILING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'PILING_RIG',
                'Piling Rig',
                'Piling & Foundation Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'BORED_PILING_RIG',
                'Bored Piling Rig',
                'Piling & Foundation Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'PILE_DRIVING_RIG',
                'Pile Driving Rig',
                'Piling & Foundation Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'BENTONITE_PUMP',
                'Bentonite / Slurry Pump',
                'Piling & Foundation Equipment',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | PUMPS & DEWATERING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'DEWATERING_PUMP',
                'Dewatering Pump',
                'Dewatering & Pumping',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'ELECTRIC_DEWATERING_PUMP',
                'Electric Dewatering Pump',
                'Dewatering & Pumping',
                'individual',
                'hour_meter',
                'electric',
                false,
                true,
                false
            ),

            $this->item(
                'SUBMERSIBLE_PUMP',
                'Submersible Pump',
                'Dewatering & Pumping',
                'individual',
                'hour_meter',
                'electric',
                false,
                true,
                false
            ),

            $this->item(
                'WATER_PUMP',
                'General Water Pump',
                'Dewatering & Pumping',
                'individual',
                'none',
                'electric',
                false,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | POWER GENERATION & ELECTRICAL
            |--------------------------------------------------------------------------
            */

            $this->item(
                'DG_SET',
                'Diesel Generator Set',
                'Power Generation',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'PORTABLE_GENERATOR',
                'Portable Generator',
                'Power Generation',
                'individual',
                'hour_meter',
                'petrol',
                true,
                true,
                true
            ),

            $this->item(
                'SITE_DISTRIBUTION_PANEL',
                'Site Electrical Distribution Panel',
                'Electrical Equipment',
                'individual',
                'none',
                'electric',
                false,
                false,
                false
            ),

            $this->item(
                'CABLE_DRUM_STAND',
                'Cable Drum Stand',
                'Electrical Equipment',
                'pooled',
                'none',
                'none',
                false,
                false,
                false
            ),

            $this->item(
                'CABLE_PULLING_WINCH',
                'Cable Pulling Winch',
                'Electrical Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | WELDING & CUTTING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'ARC_WELDING_MACHINE',
                'Arc Welding Machine',
                'Welding & Cutting',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'MIG_WELDING_MACHINE',
                'MIG Welding Machine',
                'Welding & Cutting',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'TIG_WELDING_MACHINE',
                'TIG Welding Machine',
                'Welding & Cutting',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'GAS_CUTTING_SET',
                'Gas Cutting Set',
                'Welding & Cutting',
                'pooled',
                'none',
                'other',
                true,
                false,
                false
            ),

            $this->item(
                'PLASMA_CUTTER',
                'Plasma Cutting Machine',
                'Welding & Cutting',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | COMPRESSORS & PNEUMATIC
            |--------------------------------------------------------------------------
            */

            $this->item(
                'AIR_COMPRESSOR',
                'Air Compressor',
                'Air Compressors & Pneumatic Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'ELECTRIC_AIR_COMPRESSOR',
                'Electric Air Compressor',
                'Air Compressors & Pneumatic Equipment',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'PNEUMATIC_BREAKER',
                'Pneumatic Breaker',
                'Air Compressors & Pneumatic Equipment',
                'pooled',
                'none',
                'none',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | ACCESS & SCAFFOLDING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'SCISSOR_LIFT',
                'Scissor Lift',
                'Scaffolding & Access Equipment',
                'individual',
                'hour_meter',
                'battery',
                true,
                true,
                false
            ),

            $this->item(
                'BOOM_LIFT',
                'Boom Lift / Cherry Picker',
                'Scaffolding & Access Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'SUSPENDED_PLATFORM',
                'Suspended Working Platform / Gondola',
                'Scaffolding & Access Equipment',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | DEMOLITION / DRILLING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'DEMOLITION_HAMMER',
                'Demolition Hammer',
                'Demolition Equipment',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'HYDRAULIC_BREAKER',
                'Hydraulic Breaker Attachment',
                'Demolition Equipment',
                'individual',
                'hour_meter',
                'none',
                true,
                true,
                false
            ),

            $this->item(
                'CORE_CUTTING_MACHINE',
                'Core Cutting Machine',
                'Drilling & Coring Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'CORE_DRILL_MACHINE',
                'Core Drill Machine',
                'Drilling & Coring Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | CARPENTRY / FLOORING / FINISHING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'CIRCULAR_SAW',
                'Circular Saw',
                'Carpentry & Woodworking Equipment',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'TABLE_SAW',
                'Table Saw',
                'Carpentry & Woodworking Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'TILE_CUTTER',
                'Electric Tile Cutting Machine',
                'Flooring & Finishing Equipment',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'FLOOR_GRINDER',
                'Floor Grinding Machine',
                'Flooring & Finishing Equipment',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            $this->item(
                'FLOOR_POLISHER',
                'Floor Polishing Machine',
                'Flooring & Finishing Equipment',
                'individual',
                'hour_meter',
                'electric',
                true,
                true,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | PAINTING & WATERPROOFING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'AIRLESS_PAINT_SPRAYER',
                'Airless Paint Sprayer',
                'Painting Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'PRESSURE_WASHER',
                'High Pressure Washer',
                'Painting Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'WATERPROOF_SPRAYER',
                'Waterproofing Spray Machine',
                'Waterproofing Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'BITUMEN_BOILER',
                'Bitumen Boiler / Heating Kettle',
                'Waterproofing Equipment',
                'individual',
                'none',
                'other',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | PLUMBING / HVAC / FIRE
            |--------------------------------------------------------------------------
            */

            $this->item(
                'PIPE_THREADING_MACHINE',
                'Pipe Threading Machine',
                'Plumbing Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'PIPE_CUTTING_MACHINE',
                'Pipe Cutting Machine',
                'Plumbing Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'PIPE_FUSION_MACHINE',
                'HDPE / PPR Pipe Fusion Machine',
                'Plumbing Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'DRAIN_CLEANING_MACHINE',
                'Drain Cleaning Machine',
                'Plumbing Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'VACUUM_PUMP',
                'HVAC Vacuum Pump',
                'HVAC Installation Equipment',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'REFRIGERANT_RECOVERY_MACHINE',
                'Refrigerant Recovery Machine',
                'HVAC Installation Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'DUCT_GROOVING_MACHINE',
                'Duct Grooving / Pittsburgh Machine',
                'HVAC Installation Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'FIRE_PIPE_GROOVING_MACHINE',
                'Fire Pipe Grooving Machine',
                'Fire Fighting Installation Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'HYDRO_TEST_PUMP',
                'Hydrostatic Test Pump',
                'Fire Fighting Installation Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | SURVEYING & ENGINEERING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'TOTAL_STATION',
                'Total Station',
                'Surveying & Engineering Equipment',
                'individual',
                'none',
                'battery',
                true,
                false,
                false
            ),

            $this->item(
                'AUTO_LEVEL',
                'Auto Level',
                'Surveying & Engineering Equipment',
                'individual',
                'none',
                'none',
                true,
                false,
                false
            ),

            $this->item(
                'LASER_LEVEL',
                'Laser Level',
                'Surveying & Engineering Equipment',
                'individual',
                'none',
                'battery',
                true,
                false,
                false
            ),

            $this->item(
                'THEODOLITE',
                'Theodolite',
                'Surveying & Engineering Equipment',
                'individual',
                'none',
                'battery',
                true,
                false,
                false
            ),

            $this->item(
                'GPS_SURVEY_EQUIPMENT',
                'GNSS / GPS Survey Equipment',
                'Surveying & Engineering Equipment',
                'individual',
                'none',
                'battery',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | TESTING & QUALITY
            |--------------------------------------------------------------------------
            */

            $this->item(
                'COMPRESSION_TESTING_MACHINE',
                'Compression Testing Machine',
                'Testing & Quality Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'SLUMP_CONE_SET',
                'Concrete Slump Cone Set',
                'Testing & Quality Equipment',
                'pooled',
                'none',
                'none',
                false,
                false,
                false
            ),

            $this->item(
                'REBOUND_HAMMER',
                'Concrete Rebound Hammer',
                'Testing & Quality Equipment',
                'individual',
                'none',
                'none',
                true,
                false,
                false
            ),

            $this->item(
                'COVER_METER',
                'Rebar Cover Meter',
                'Testing & Quality Equipment',
                'individual',
                'none',
                'battery',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | MATERIAL HANDLING TOOLS
            |--------------------------------------------------------------------------
            */

            $this->item(
                'HAND_TROLLEY',
                'Hand Trolley',
                'Material Handling Tools',
                'pooled',
                'none',
                'none',
                false,
                false,
                false
            ),

            $this->item(
                'PLATFORM_TROLLEY',
                'Platform Trolley',
                'Material Handling Tools',
                'pooled',
                'none',
                'none',
                false,
                false,
                false
            ),

            $this->item(
                'PALLET_TRUCK',
                'Manual Pallet Truck',
                'Material Handling Tools',
                'pooled',
                'none',
                'none',
                false,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | POWER TOOLS
            |--------------------------------------------------------------------------
            */

            $this->item(
                'ANGLE_GRINDER',
                'Angle Grinder',
                'Power Tools',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'DRILL_MACHINE',
                'Electric Drill Machine',
                'Power Tools',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'ROTARY_HAMMER',
                'Rotary Hammer Drill',
                'Power Tools',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'IMPACT_WRENCH',
                'Impact Wrench',
                'Power Tools',
                'pooled',
                'none',
                'electric',
                true,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | CLEANING
            |--------------------------------------------------------------------------
            */

            $this->item(
                'INDUSTRIAL_VACUUM',
                'Industrial Vacuum Cleaner',
                'Cleaning Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),

            $this->item(
                'FLOOR_SCRUBBER',
                'Floor Scrubber / Cleaning Machine',
                'Cleaning Equipment',
                'individual',
                'hour_meter',
                'battery',
                true,
                true,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | SAFETY / RESCUE
            |--------------------------------------------------------------------------
            */

            $this->item(
                'GAS_DETECTOR',
                'Portable Gas Detector',
                'Safety & Rescue Equipment',
                'individual',
                'none',
                'battery',
                true,
                false,
                false
            ),

            $this->item(
                'RESCUE_TRIPOD',
                'Confined Space Rescue Tripod',
                'Safety & Rescue Equipment',
                'individual',
                'none',
                'none',
                false,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | TEMPORARY SITE EQUIPMENT
            |--------------------------------------------------------------------------
            */

            $this->item(
                'LIGHTING_TOWER',
                'Mobile Lighting Tower',
                'Temporary Site Equipment',
                'individual',
                'hour_meter',
                'diesel',
                true,
                true,
                true
            ),

            $this->item(
                'SITE_FLOOD_LIGHT',
                'Site Flood Light',
                'Temporary Site Equipment',
                'pooled',
                'none',
                'electric',
                false,
                false,
                false
            ),

            /*
            |--------------------------------------------------------------------------
            | SPECIALIZED BUILDING EQUIPMENT
            |--------------------------------------------------------------------------
            */

            $this->item(
                'GLASS_LIFTER',
                'Vacuum Glass Lifter',
                'Specialized Building Equipment',
                'individual',
                'none',
                'battery',
                true,
                false,
                false
            ),

            $this->item(
                'MATERIAL_LIFT',
                'Portable Material Lift',
                'Specialized Building Equipment',
                'individual',
                'none',
                'electric',
                true,
                false,
                false
            ),
        ];

        foreach ($equipment as $index => $item) {
            $item['sort_order'] = ($index + 1) * 10;

            MachineryTool::updateOrCreate(
                [
                    'code' => $item['code'],
                ],
                $item
            );
        }
    }

    private function item(
        string $code,
        string $name,
        string $category,
        string $trackingMode,
        string $meterType,
        string $fuelType,
        bool $requiresOperator,
        bool $requiresMeterReading,
        bool $requiresFuelTracking,
        ?string $description = null
    ): array {
        return [
            'code' => $code,
            'machine_name' => $name,
            'category' => $category,
            'description' => $description,
            'ownership_type' => null,
            'unit' => 'Nos',
            'tracking_mode' => $trackingMode,
            'meter_type' => $meterType,
            'fuel_type' => $fuelType,
            'requires_operator' => $requiresOperator,
            'requires_meter_reading' => $requiresMeterReading,
            'requires_fuel_tracking' => $requiresFuelTracking,
            'is_active' => true,
        ];
    }
}