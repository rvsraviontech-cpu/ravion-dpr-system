<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ClientDrawingMasterSeeder extends Seeder {
 public function run(): void {
  $categories = [
   'SUR' => ['Survey & Existing Site Conditions', ['Boundary survey', 'Topographical survey', 'Contour plan', 'Existing utility survey', 'Existing building survey', 'Setting-out survey']],
   'PLN' => ['Planning & Concept Design', ['Concept layout', 'Site utilization plan', 'Parking concept', 'Area statement', 'Massing plan', 'Feasibility layout']],
   'AUT' => ['Statutory & Authority Approval', ['Building permission plan', 'Sanctioned plan', 'Revised sanction plan', 'Fire approval plan', 'Occupancy submission plan', 'Setback compliance plan']],
   'ARC' => ['Architectural', ['Site plan', 'Floor plan', 'Roof plan', 'Elevation', 'Section', 'Staircase detail', 'Door schedule', 'Window schedule', 'Toilet detail', 'Kitchen layout', 'Ramp detail', 'Accessibility layout', 'Reflected ceiling plan', 'Masonry layout', 'Terrace detail', 'Architectural setting-out plan']],
   'STR' => ['Structural', ['Structural general notes', 'Column layout', 'Footing layout', 'Footing reinforcement', 'Column reinforcement', 'Beam layout', 'Beam reinforcement', 'Slab layout', 'Slab reinforcement', 'Staircase reinforcement', 'Shear wall detail', 'Structural section', 'Bar bending schedule', 'Lintel detail', 'Structural steel connection', 'Expansion joint detail', 'Raft reinforcement', 'Pile cap reinforcement']],
   'GEO' => ['Geotechnical & Foundation', ['Excavation plan', 'Pile layout', 'Retaining wall detail', 'Shoring layout', 'Dewatering layout', 'Ground improvement plan']],
   'ELE' => ['Electrical', ['Lighting layout', 'Power layout', 'Single-line diagram', 'Earthing layout', 'Lightning protection', 'DB schedule', 'Cable tray layout', 'Conduit layout', 'Meter room layout', 'Emergency lighting', 'Transformer layout', 'Generator electrical layout']],
   'PLB' => ['Plumbing & Sanitary', ['Water supply layout', 'Sanitary layout', 'Plumbing riser', 'Hot water layout', 'Water tank detail', 'Pump piping', 'Fixture schedule', 'Pipe sleeve layout', 'Water meter layout']],
   'DRN' => ['Drainage & Stormwater', ['Sewer layout', 'Stormwater layout', 'Manhole detail', 'Drainage section', 'Catch basin detail', 'Sump layout', 'Drainage invert plan']],
   'HVAC' => ['Heating, Ventilation & Air Conditioning', ['Duct layout', 'Ventilation layout', 'Equipment layout', 'Refrigerant piping', 'Chilled water piping', 'HVAC schematic', 'Diffuser layout', 'Exhaust layout']],
   'FIR' => ['Fire Protection & Life Safety', ['Sprinkler layout', 'Hydrant layout', 'Fire alarm layout', 'Evacuation plan', 'Fire pump room', 'Fire riser diagram', 'Smoke extraction', 'Fire compartment plan']],
   'ELV' => ['Low Voltage & Communication', ['CCTV layout', 'Data network', 'Intercom layout', 'Access control', 'Public address', 'Television distribution', 'Security system schematic']],
   'MEC' => ['Mechanical Services', ['Pump room layout', 'Equipment installation', 'Mechanical piping', 'Equipment foundation', 'Mechanical schematic', 'Plant room layout']],
   'VTS' => ['Lifts & Vertical Transportation', ['Lift shaft detail', 'Lift pit detail', 'Lift installation', 'Lift machine room', 'Lift electrical interface', 'Escalator layout']],
   'INT' => ['Interior Design', ['Furniture layout', 'False ceiling layout', 'Kitchen detail', 'Wardrobe detail', 'Floor finish layout', 'Wall finish layout', 'Joinery detail', 'Interior elevation', 'Lighting coordination', 'Counter detail']],
   'FAC' => ['Façade & External Elevation', ['Cladding layout', 'Glazing detail', 'Elevation treatment', 'Curtain wall detail', 'Louver detail', 'Façade fixing detail']],
   'WPF' => ['Waterproofing', ['Terrace waterproofing', 'Toilet waterproofing', 'Basement waterproofing', 'Water tank waterproofing', 'Joint sealing detail', 'Balcony waterproofing']],
   'LAN' => ['Landscaping', ['Planting plan', 'Hardscape layout', 'Irrigation layout', 'Landscape lighting', 'Planter detail', 'Landscape grading']],
   'EXT' => ['External Development & Infrastructure', ['Compound wall', 'Driveway layout', 'Paving layout', 'Entrance gate detail', 'Road section', 'External grading', 'Parking marking']],
   'UTL' => ['Utility Services', ['Underground services', 'Utility connection layout', 'Water connection', 'Sewer connection', 'Electrical service connection', 'Utility crossing detail']],
   'SUS' => ['Sustainability & Renewable Energy', ['Solar panel layout', 'Rainwater harvesting', 'Greywater system', 'Solar hot water', 'Energy metering', 'Waste segregation layout']],
   'SPE' => ['Specialist Systems', ['Swimming pool layout', 'Acoustic treatment', 'Specialist equipment layout', 'Commercial kitchen layout', 'Medical gas layout', 'Building automation layout']],
   'TMP' => ['Temporary Works', ['Scaffolding layout', 'Formwork layout', 'Temporary site facilities', 'Temporary access', 'Temporary electrical layout', 'Temporary drainage', 'Excavation support']],
   'COA' => ['Coordination & Composite Services', ['Combined services layout', 'Coordinated ceiling layout', 'Builders work drawing', 'Penetration layout', 'Service shaft coordination', 'Plant room coordination']],
  ];
  $purposes = [
   'CON' => 'Concept Design',
   'PRE' => 'Preliminary Design',
   'REV' => 'For Review',
   'FAP' => 'For Approval',
   'INF' => 'For Information',
   'TEN' => 'Tender Drawing',
   'GFC' => 'Good for Construction',
   'SHP' => 'Shop Drawing',
   'FAB' => 'Fabrication Drawing',
   'INS' => 'Installation Drawing',
   'RCD' => 'Record Drawing',
   'ASB' => 'As-Built Drawing',
  ];
  DB::transaction(function () use ($categories, $purposes) {
   $order = 0;
   foreach ($categories as $code => [$name, $types]) {
    $order++;
    $id = DB::table('client_drawing_categories')->where('code',$code)->value('id');
    if (!$id) $id = DB::table('client_drawing_categories')->insertGetId(['code'=>$code,'name'=>$name,'sort_order'=>$order,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
    foreach ($types as $i => $type) {
     $typeCode = $code.'-'.str_pad((string)($i+1),3,'0',STR_PAD_LEFT);
     if (!DB::table('client_drawing_types')->where('category_id',$id)->where('code',$typeCode)->exists()) DB::table('client_drawing_types')->insert(['category_id'=>$id,'code'=>$typeCode,'name'=>$type,'sort_order'=>$i+1,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
    }
   }
   $order = 0;
   foreach ($purposes as $code => $name) {
    $order++;
    if (!DB::table('client_drawing_purposes')->where('code',$code)->exists()) DB::table('client_drawing_purposes')->insert(['code'=>$code,'name'=>$name,'sort_order'=>$order,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
   }
  });
 }
}
