<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProjectStructureMasterSeeder extends Seeder {
 public function run(): void {
  $this->seed('spatial_block_types',[['BLOCK','Block'],['BUILDING','Building'],['TOWER','Tower'],['VILLA','Villa'],['WING','Wing'],['EXTERNAL_AREA','External Area'],['NOT_APPLICABLE','Not Applicable'],['OTHER','Other / Custom']]);
  $levels=[['CELLAR_BASEMENT','Cellar / Basement','Cellar {n}',1],['LOWER_GROUND','Lower Ground','Lower Ground',0],['GROUND','Ground Floor','Ground Floor',0],['PODIUM','Podium','Podium {n}',1],['MEZZANINE','Mezzanine','Mezzanine',0],['TYPICAL_FLOOR','Typical Floor','Floor {n}',1],['SERVICE_FLOOR','Service Floor','Service Floor {n}',1],['MACHINE_FLOOR','Machine Floor','Machine Floor',0],['TERRACE_ROOF','Terrace / Roof','Terrace / Roof',0],['CUSTOM','Other / Custom','{custom}',0]];
  foreach($levels as $i=>$r){DB::table('spatial_level_types')->updateOrInsert(['code'=>$r[0]],['name'=>$r[1],'name_pattern'=>$r[2],'requires_number'=>$r[3],'number_mode'=>$r[0]==='CUSTOM'?'custom':'integer','sort_order'=>($i+1)*10,'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);}
  $this->seed('spatial_floor_usages',[['RESIDENTIAL','Residential'],['COMMERCIAL_RETAIL','Commercial / Retail'],['OFFICE','Office'],['PARKING','Parking'],['HEALTHCARE','Healthcare'],['EDUCATIONAL','Educational'],['HOSPITALITY_BANQUET','Hospitality / Banquet'],['BANKING','Banking'],['INDUSTRIAL_SERVICE','Industrial / Service'],['AMENITIES','Amenities'],['MIXED_USE','Mixed Use'],['OTHER','Other / Custom']]);
 }
 private function seed($table,$rows){foreach($rows as $i=>$r){DB::table($table)->updateOrInsert(['code'=>$r[0]],['name'=>$r[1],'sort_order'=>($i+1)*10,'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);}}
}
