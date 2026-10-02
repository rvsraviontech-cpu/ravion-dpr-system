<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class ProjectStructureMasterController extends Controller {
 private const M=[
  'block-types'=>['Block / Building Types','spatial_block_types',null],
  'level-types'=>['Level / Floor Types','spatial_level_types',null],
  'floor-usages'=>['Floor Usages','spatial_floor_usages',null],
  'functional-unit-types'=>['Functional Unit Types','spatial_functional_unit_types',null],
  'project-categories'=>['Project Categories','spatial_project_categories',null],
  'project-types'=>['Project Types','spatial_project_types',['spatial_project_category_id','spatial_project_categories','Project Category']],
  'space-categories'=>['Space Categories','spatial_space_categories',['spatial_project_category_id','spatial_project_categories','Project Category',true]],
  'space-types'=>['Space Types','spatial_space_types',['spatial_space_category_id','spatial_space_categories','Space Category']],
  'space-subtypes'=>['Space Subtypes','spatial_space_subtypes',['spatial_space_type_id','spatial_space_types','Space Type']],
  'component-types'=>['Component / Wall Types','spatial_component_types',null],
  'opening-types'=>['Opening Types','spatial_opening_types',null],
  'measurement-zones'=>['Measurement Zones','spatial_measurement_zones',null],
 ];
 public function index(Request $r){$k=$r->get('master','block-types');$c=$this->c($k);$q=DB::table($c[1]);if($s=trim((string)$r->q))$q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('code','like',"%$s%"));if($r->status)$q->where('is_active',$r->status==='active');if($c[2]&&$r->parent_id)$q->where($c[2][0],$r->parent_id);$items=$q->orderBy('sort_order')->orderBy('name')->paginate(50)->withQueryString();$parents=$c[2]?DB::table($c[2][1])->where('is_active',1)->orderBy('sort_order')->orderBy('name')->get(['id','name']):collect();return view('project-structure-masters.index',compact('k','c','items','parents')+['masters'=>self::M]);}
 public function store(Request $r,string $master){$c=$this->c($master);DB::table($c[1])->insert($this->data($r,$c)+['is_active'=>1,'is_system'=>0,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Master added successfully.');}
 public function update(Request $r,string $master,int $id){$c=$this->c($master);DB::table($c[1])->where('id',$id)->update($this->data($r,$c,$id)+['updated_at'=>now()]);return back()->with('success','Master updated successfully.');}
 public function toggle(string $master,int $id){$c=$this->c($master);$x=DB::table($c[1])->where('id',$id)->first();abort_unless($x,404);DB::table($c[1])->where('id',$id)->update(['is_active'=>!$x->is_active,'updated_at'=>now()]);return back()->with('success','Status updated successfully.');}
 private function data(Request $r,array $c,?int $id=null){$rules=['code'=>['required','string','max:120',Rule::unique($c[1],'code')->ignore($id)],'name'=>'required|string|max:180','description'=>'nullable|string','aliases_text'=>'nullable|string','sort_order'=>'nullable|integer|min:0'];if($c[2])$rules[$c[2][0]]=[!empty($c[2][3])?'nullable':'required','integer',Rule::exists($c[2][1],'id')];if($c[1]==='spatial_level_types'){$rules['name_pattern']='nullable|string|max:150';$rules['number_mode']=['required',Rule::in(['integer','custom'])];}$v=$r->validate($rules);$d=['code'=>strtoupper(trim($v['code'])),'name'=>trim($v['name']),'description'=>$v['description']??null,'aliases'=>empty($v['aliases_text'])?null:json_encode(array_values(array_filter(array_map('trim',preg_split('/[,;\n]+/',$v['aliases_text']))))),'sort_order'=>(int)($v['sort_order']??0)];if($c[2])$d[$c[2][0]]=$v[$c[2][0]]??null;if($c[1]==='spatial_level_types'){$d['name_pattern']=$v['name_pattern']??null;$d['requires_number']=$r->boolean('requires_number');$d['number_mode']=$v['number_mode'];}return $d;}
 private function c($k){abort_unless(isset(self::M[$k]),404);return self::M[$k];}
}
