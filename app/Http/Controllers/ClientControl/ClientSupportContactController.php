<?php
namespace App\Http\Controllers\ClientControl;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class ClientSupportContactController extends Controller {
 private function scope(Request $r) {
  $u=$r->user('web'); abort_unless($u && $u->isActive() && $u->hasPermission('client_control.support.manage_contacts'),403);
  $q=Project::query();
  if (!in_array($u->role?->name,['Admin','CEO'],true)) { abort_unless($u->role?->name==='PMO',403); $q->where('assigned_pmo_id',$u->id); }
  return $q;
 }
 public function index(Request $r) {
  $projects=$this->scope($r)->orderBy('project_name')->get(['id','project_name','project_code']);
  $project=$r->filled('project') ? $this->scope($r)->findOrFail($r->integer('project')) : null;
  $contacts=$project ? DB::table('client_support_contacts as c')->join('client_support_contact_roles as d','d.id','=','c.role_id')->where('c.project_id',$project->id)->select('c.*','d.name as designation','d.is_active as role_active')->orderBy('c.sort_order')->orderBy('c.id')->get() : collect();
  $roles=DB::table('client_support_contact_roles')->orderBy('sort_order')->orderBy('name')->get();
  return view('client-control.admin.support.contacts',compact('projects','project','contacts','roles'));
 }
 public function store(Request $r, Project $project) { return $this->save($r,$project,null); }
 public function update(Request $r, Project $project,int $contact) { return $this->save($r,$project,$contact); }
 private function save(Request $r,Project $project,?int $id) {
  $this->scope($r)->findOrFail($project->id);
  DB::transaction(function() use($r,$project,$id){
   $q=DB::table('client_support_contacts')->where('project_id',$project->id);
   $old=$id ? (clone $q)->where('id',$id)->lockForUpdate()->first() : null;
   abort_if($id && !$old,404);
   $data=$r->validate(['role_id'=>['required','integer',Rule::exists('client_support_contact_roles','id')->where(fn($q)=>$q->where('is_active',true)->orWhere('id',$old?->role_id ?? 0))],'name'=>'required|string|max:150','phone'=>'nullable|string|max:50','email'=>'nullable|email|max:255','availability'=>'nullable|string|max:2000','sort_order'=>'required|integer|min:0|max:999999','show_name'=>'sometimes|boolean','show_phone'=>'sometimes|boolean','show_email'=>'sometimes|boolean','show_availability'=>'sometimes|boolean','is_active'=>'sometimes|boolean','is_client_visible'=>'sometimes|boolean']);
   foreach(['show_name','show_phone','show_email','show_availability','is_active','is_client_visible'] as $field) $data[$field]=$r->boolean($field);
   $data['updated_by']=$r->user('web')->id; $data['updated_at']=now();
   if($old) { (clone $q)->where('id',$id)->update($data); } else { $data['project_id']=$project->id; $data['created_by']=$r->user('web')->id; $data['created_at']=now(); $id=DB::table('client_support_contacts')->insertGetId($data); }
   DB::table('client_support_events')->insert(['project_id'=>$project->id,'user_id'=>$r->user('web')->id,'action'=>$old?'contact_updated':'contact_created','entity_type'=>'contact','entity_id'=>$id,'details'=>json_encode(['before'=>$old,'after'=>$data],JSON_THROW_ON_ERROR),'ip_address'=>$r->ip(),'created_at'=>now()]);
  });
  return redirect()->route('client-control.admin.support.contacts.index',['project'=>$project->id])->with('success','Contact saved successfully.');
 }
}
