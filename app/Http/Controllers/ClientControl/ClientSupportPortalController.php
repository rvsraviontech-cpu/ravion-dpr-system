<?php
namespace App\Http\Controllers\ClientControl;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientControl\ClientProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ClientControl\SupportCommunication as SC;

use App\Services\ClientControl\SupportNotifications as SN;

class ClientSupportPortalController extends Controller {
 private function grant(Request $r, Project $project, ClientProjectAccess $access, bool $lock=false) {
  $client=$r->user('client'); abort_unless($client,401); $access->assertAllowed($client,(int)$project->id);
  $q=DB::table('client_project_grants')->where('project_id',$project->id)->where('client_user_id',$client->id)->where('status','Approved')->whereNull('revoked_at')->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->orderByDesc('id');
  if($lock) $q->lockForUpdate(); $grant=$q->first(); abort_unless($grant,403); return $grant;
 }
 private function tickets(Request $r, Project $project) { return DB::table('client_support_tickets as t')->join('client_support_categories as c','c.id','=','t.category_id')->where('t.project_id',$project->id)->where('t.client_user_id',$r->user('client')->id)->select('t.id','t.project_id','t.ticket_number','t.subject','t.status','t.priority','t.request_type','t.created_at','t.last_activity_at','t.expected_resolution_date','c.name as category'); }
 public function index(Request $r, Project $project, ClientProjectAccess $access) {
  $this->grant($r,$project,$access);
  $contacts=DB::table('client_support_contacts as c')->join('client_support_contact_roles as d','d.id','=','c.role_id')->where('c.project_id',$project->id)->where('c.is_active',true)->where('c.is_client_visible',true)->where('d.is_active',true)->orderBy('c.sort_order')->orderBy('c.id')->select('d.name as designation')->selectRaw('CASE WHEN c.show_name THEN c.name END as name, CASE WHEN c.show_phone THEN c.phone END as phone, CASE WHEN c.show_email THEN c.email END as email, CASE WHEN c.show_availability THEN c.availability END as availability')->get();
  $categories=DB::table('client_support_categories')->where('is_active',true)->orderBy('sort_order')->get(['id','name']);  $q=$this->tickets($r,$project); $counts=(clone $q)->select('t.status',DB::raw('count(*) as total'))->groupBy('t.status')->pluck('total','status'); if($r->filled('status')) $q->where('t.status',$r->input('status')); if($r->filled('search')) { $term='%'.$r->input('search').'%'; $q->where(fn($q)=>$q->where('t.subject','like',$term)->orWhere('t.ticket_number','like',$term)); } $tickets=$q->orderByDesc('t.last_activity_at')->orderByDesc('t.id')->paginate(20)->withQueryString();
  return response()->view('client-control.support.index',compact('project','contacts','categories','tickets','counts'))->header('Cache-Control','private, no-store');
 }
 public function store(Request $r, Project $project, ClientProjectAccess $access) {
  $this->grant($r,$project,$access);
  $data=$r->validate(['category_id'=>['required',Rule::exists('client_support_categories','id')->where('is_active',true)],'subject'=>'required|string|max:255','body'=>'required|string|max:20000','request_type'=>'required|in:Request,Issue,Clarification,Complaint,Information,Other','priority'=>'required|in:Normal,High,Urgent']);
   $r->validate(SC::rules()); $paths=[]; try { $id=DB::transaction(function()use($r,$project,$access,$data,&$paths){
   $p=Project::query()->lockForUpdate()->findOrFail($project->id); $g=$this->grant($r,$p,$access,true); $now=now(); $year=$now->year;
   DB::table('client_support_sequences')->insertOrIgnore(['division_code'=>'RH','year'=>$year,'last_number'=>0,'created_at'=>$now,'updated_at'=>$now]); $seq=DB::table('client_support_sequences')->where('division_code','RH')->where('year',$year)->lockForUpdate()->first(); $n=$seq->last_number+1; DB::table('client_support_sequences')->where('id',$seq->id)->update(['last_number'=>$n,'updated_at'=>$now]);
   $id=DB::table('client_support_tickets')->insertGetId(['ticket_number'=>sprintf('RH-SUP-%d-%06d',$year,$n),'project_id'=>$p->id,'category_id'=>$data['category_id'],'client_user_id'=>$g->client_user_id,'grant_id'=>$g->id,'assigned_to'=>$p->assigned_pmo_id,'subject'=>$data['subject'],'request_type'=>$data['request_type'],'priority'=>$data['priority'],'status'=>'Open','last_activity_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
   SC::save($r,(object)['id'=>$id,'project_id'=>$p->id],'Public',['client_user_id'=>$g->client_user_id],$data['body'],$r->file('attachments',[]),$paths);
   DB::table('client_support_events')->insert(['ticket_id'=>$id,'project_id'=>$p->id,'client_user_id'=>$g->client_user_id,'action'=>'ticket_created','details'=>json_encode(['assigned_to'=>$p->assigned_pmo_id]),'ip_address'=>$r->ip(),'created_at'=>$now]); SN::send($id,'New support ticket'); return $id;
  }); } catch(\Throwable $e){ SC::cleanup($paths); throw $e; } return redirect()->route('client.support.show',[$project->id,$id])->with('success','Your request has been submitted.');
 }
 public function show(Request $r, Project $project, int $ticket, ClientProjectAccess $access) {
  $this->grant($r,$project,$access); $ticket=$this->tickets($r,$project)->where('t.id',$ticket)->first(); abort_unless($ticket,404);
  $messages=DB::table('client_support_messages')->where('ticket_id',$ticket->id)->where('visibility','Public')->orderBy('id')->get(['id','client_user_id','body','created_at']); $attachments=SC::attachments($messages);
  return response()->view('client-control.support.show',compact('project','ticket','messages','attachments'))->header('Cache-Control','private, no-store');
 }
 public function reply(Request $r,Project $project,int $ticket,ClientProjectAccess $access) {
  $this->grant($r,$project,$access); $data=$r->validate(SC::rules()); $paths=[];
  try { DB::transaction(function()use($r,$project,$ticket,$access,$data,&$paths){ $g=$this->grant($r,$project,$access,true); $t=DB::table('client_support_tickets')->where('project_id',$project->id)->where('client_user_id',$g->client_user_id)->where('id',$ticket)->lockForUpdate()->first(); abort_unless($t,404); abort_if(in_array($t->status,['Resolved','Closed']),422,'This ticket is not open for replies.'); SC::save($r,$t,'Public',['client_user_id'=>$g->client_user_id],$data['body'],$r->file('attachments',[]),$paths); SN::send($ticket,'Client replied to support ticket'); }); } catch(\Throwable $e){ SC::cleanup($paths); throw $e; }
  return back()->with('success','Reply sent.');
 }
 public function attachment(Request $r,Project $project,int $ticket,int $attachment,ClientProjectAccess $access) { $this->grant($r,$project,$access); abort_unless($this->tickets($r,$project)->where('t.id',$ticket)->exists(),404); return SC::download($ticket,$attachment,true); }
}
