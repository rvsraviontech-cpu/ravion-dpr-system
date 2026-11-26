<?php
namespace App\Http\Controllers\ClientControl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class ClientDrawingMasterController extends Controller {
 private function check(Request $r): void { $u=$r->user('web'); abort_unless($u && $u->isActive() && $u->hasPermission('client_control.drawings.manage_masters'),403); }
 private function table(string $kind): string { return match($kind){'categories'=>'client_drawing_categories','types'=>'client_drawing_types','purposes'=>'client_drawing_purposes',default=>abort(404)}; }
 public function index(Request $r) { $this->check($r); $f=$r->validate(['kind'=>'nullable|in:categories,types,purposes','q'=>'nullable|string|max:200','status'=>'nullable|in:active,inactive','category_id'=>'nullable|integer']); $kind=$f['kind']??'categories'; $q=DB::table($this->table($kind)); if(!empty($f['q'])) $q->where(fn($x)=>$x->where('name','like','%'.$f['q'].'%')->orWhere('code','like','%'.$f['q'].'%')); if(!empty($f['status'])) $q->where('is_active',$f['status']==='active'); if($kind==='types'&&!empty($f['category_id'])) $q->where('category_id',$f['category_id']); $rows=$q->orderBy('sort_order')->orderBy('id')->paginate(30)->withQueryString(); $categories=DB::table('client_drawing_categories')->orderBy('sort_order')->get(); return view('client-control.admin.drawings.masters.index',compact('kind','rows','categories')); }
 public function store(Request $r,string $kind) { $this->check($r); $table=$this->table($kind); $rules=['code'=>['required','regex:/^[A-Z0-9-]+$/','max:'.($kind==='types'?30:20)],'name'=>'required|string|max:200']; if($kind==='types') $rules['category_id']=['required',Rule::exists('client_drawing_categories','id')->where('is_active',1)]; $data=$r->validate($rules); $unique=Rule::unique($table,'code'); if($kind==='types') $unique->where('category_id',$data['category_id']); $r->validate(['code'=>$unique]); DB::table($table)->insert($data+['is_active'=>1,'sort_order'=>0,'created_at'=>now(),'updated_at'=>now()]); return back()->with('success','Master added.'); }
 public function update(Request $r,string $kind,int $id) { $this->check($r); $q=DB::table($this->table($kind))->where('id',$id); abort_unless($q->exists(),404); $data=$r->validate(['name'=>'required|string|max:200']); $q->update($data+['updated_at'=>now()]); return back()->with('success','Master updated.'); }
 public function status(Request $r,string $kind,int $id) { $this->check($r); $data=$r->validate(['is_active'=>'required|boolean']); $q=DB::table($this->table($kind))->where('id',$id); abort_unless($q->exists(),404); $q->update($data+['updated_at'=>now()]); return back()->with('success','Master status updated.'); }
}
