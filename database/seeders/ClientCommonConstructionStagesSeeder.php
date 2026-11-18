<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reusable residential/commercial construction catalogue.
 * Idempotent by exact stage/task name; never edits existing rows.
 * Suggested weights are a STARTING POINT, not project BOQ allocations.
 * Applicability notes are descriptive only until template filtering is implemented.
 */
class ClientCommonConstructionStagesSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = json_decode(<<<'CATALOGUE'
[
  {
    "name": "Preconstruction & Mobilisation",
    "weight": 2,
    "tasks": [
      "Site handover and access",
      "Approved drawings and permits verification",
      "Temporary site facilities",
      "Safety barricading and signage",
      "Temporary water and electricity",
      "Survey benchmarks and setting out"
    ]
  },
  {
    "name": "Site Clearing & Earthworks",
    "weight": 3,
    "tasks": [
      "Site clearing and debris removal",
      "Topsoil stripping",
      "Excavation setting out",
      "Bulk excavation",
      "Dewatering where required",
      "Excavated soil disposal",
      "Formation level checking",
      "Backfilling and compaction"
    ]
  },
  {
    "name": "Foundations & Footings",
    "weight": 8,
    "tasks": [
      "Foundation layout verification",
      "Foundation excavation",
      "Anti-termite treatment where specified",
      "PCC blinding",
      "Footing reinforcement",
      "Footing shuttering",
      "Footing concrete placement",
      "Curing and formwork removal",
      "Foundation inspection and backfilling"
    ]
  },
  {
    "name": "Plinth & Substructure",
    "weight": 6,
    "tasks": [
      "Pedestals and column starters",
      "Plinth beams reinforcement",
      "Plinth beam shuttering",
      "Plinth beam concrete",
      "Below-ground waterproofing",
      "Underground services sleeves",
      "Plinth filling and compaction",
      "DPC and plinth protection"
    ]
  },
  {
    "name": "Superstructure RCC / Steel Frame",
    "weight": 20,
    "tasks": [
      "Columns reinforcement and formwork",
      "Columns concrete",
      "Beams reinforcement and formwork",
      "Slab centering and staging",
      "Slab reinforcement",
      "MEP sleeves and embedded conduits",
      "Slab concrete",
      "Concrete curing",
      "Staircase structural works",
      "Upper-floor structural cycle",
      "Structural steel fabrication where applicable",
      "Structural steel erection where applicable"
    ]
  },
  {
    "name": "Masonry & Partitions",
    "weight": 7,
    "tasks": [
      "External blockwork",
      "Internal blockwork",
      "Door and window openings",
      "Lintels and sunshades",
      "Partition walls",
      "Service shaft walls",
      "Masonry curing and alignment checks"
    ]
  },
  {
    "name": "Roofing & Terrace Works",
    "weight": 4,
    "tasks": [
      "Roof screed and slopes",
      "Roof waterproofing substrate",
      "Terrace waterproofing",
      "Waterproofing flood test",
      "Roof insulation where specified",
      "Parapet and coping",
      "Rainwater outlets and downpipes"
    ]
  },
  {
    "name": "Plumbing & Drainage Rough-In",
    "weight": 5,
    "tasks": [
      "Water supply pipe rough-in",
      "Soil and waste pipe rough-in",
      "Floor traps and drain points",
      "Pressure testing",
      "Underground drainage connections",
      "Rainwater piping",
      "Shaft pipework and supports"
    ]
  },
  {
    "name": "Electrical & ELV Rough-In",
    "weight": 5,
    "tasks": [
      "Conduits and concealed boxes",
      "Distribution board locations",
      "Cable trays and containment",
      "Earthing and bonding",
      "Data and communication conduits",
      "Fire alarm conduits where applicable",
      "Electrical rough-in testing"
    ]
  },
  {
    "name": "HVAC & Mechanical Services",
    "weight": 4,
    "tasks": [
      "HVAC load and routing coordination",
      "Duct and pipe sleeves",
      "HVAC ductwork where applicable",
      "Refrigerant and condensate piping",
      "Ventilation fans and exhaust systems",
      "Equipment supports and foundations",
      "Mechanical pressure and leak testing"
    ]
  },
  {
    "name": "Internal & External Plaster",
    "weight": 6,
    "tasks": [
      "Internal wall plaster",
      "Ceiling plaster where applicable",
      "External wall plaster",
      "Corners and level checks",
      "Plaster curing",
      "Surface rectification"
    ]
  },
  {
    "name": "Waterproofing Wet Areas",
    "weight": 4,
    "tasks": [
      "Bathroom substrate preparation",
      "Wet area waterproofing",
      "Balcony waterproofing",
      "Wet area ponding test",
      "Kitchen and utility waterproofing where specified",
      "Treatment of construction joints"
    ]
  },
  {
    "name": "Flooring & Wall Finishes",
    "weight": 6,
    "tasks": [
      "Floor screed and level preparation",
      "Bathroom wall tiling",
      "Bathroom floor tiling",
      "General floor tiling or stone",
      "Staircase finishes",
      "Skirting and thresholds",
      "Tile grouting and protection"
    ]
  },
  {
    "name": "Doors Windows & Glazing",
    "weight": 5,
    "tasks": [
      "Door frames installation",
      "Door shutters and hardware",
      "Window frames installation",
      "Window glazing and sealing",
      "Fire-rated doors where applicable",
      "Balustrades and railings"
    ]
  },
  {
    "name": "Ceilings Joinery & Interior Finishes",
    "weight": 3,
    "tasks": [
      "False ceiling framing where specified",
      "False ceiling boards and finishing",
      "Built-in joinery where specified",
      "Wall cladding where specified",
      "Interior finishing touch-ups"
    ]
  },
  {
    "name": "Painting & Surface Coatings",
    "weight": 4,
    "tasks": [
      "Wall putty and primer",
      "Internal paint coats",
      "External primer and coating",
      "Metal and wood protective coatings",
      "Final paint touch-ups"
    ]
  },
  {
    "name": "MEP Fixtures Testing & Commissioning",
    "weight": 5,
    "tasks": [
      "Sanitary fixtures and faucets",
      "Electrical switches sockets and lights",
      "Distribution board termination",
      "Water pump and tank commissioning",
      "Plumbing pressure and flow tests",
      "Electrical insulation and earthing tests",
      "HVAC testing and balancing where applicable",
      "Fire safety system testing where applicable"
    ]
  },
  {
    "name": "External Works & Handover",
    "weight": 3,
    "tasks": [
      "External paving and approach",
      "Boundary wall and gate where specified",
      "Site drainage and landscaping where specified",
      "Final cleaning",
      "Snag list closure",
      "As-built drawings and warranties",
      "Client walkthrough and handover documentation"
    ]
  }
]
CATALOGUE, true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($catalogue): void {
            foreach ($catalogue as $position => $definition) {
                $stage = DB::table('client_common_stages')
                    ->where('name', $definition['name'])->first();

                if (!$stage) {
                    $stageId = DB::table('client_common_stages')->insertGetId([
                        'name' => $definition['name'],
                        'description' => 'Residential and commercial reference stage. Adapt scope and weight to project drawings, BOQ and contract.',
                        'suggested_weightage' => $definition['weight'],
                        'sort_order' => $position + 1,
                        'status' => 'active',
                        'created_by' => null,
                        'updated_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Do not reactivate archived stages or overwrite manual edits.
                    $stageId = $stage->id;
                    if ($stage->status !== 'active') {
                        continue;
                    }
                }

                foreach ($definition['tasks'] as $taskPosition => $name) {
                    $exists = DB::table('client_common_stage_tasks')
                        ->where('stage_id', $stageId)->where('name', $name)->exists();
                    if ($exists) {
                        continue;
                    }
                    DB::table('client_common_stage_tasks')->insert([
                        'stage_id' => $stageId,
                        'name' => $name,
                        'description' => null,
                        'suggested_weightage' => 1,
                        'sort_order' => $taskPosition + 1,
                        'status' => 'active',
                        'created_by' => null,
                        'updated_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
