<?php
namespace App\Http\Controllers\ClientControl;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\ClientControl\SupportCommunication as SC;

use App\Services\ClientControl\SupportNotifications as SN;

class ClientSupportDeskController extends Controller {
 private function projects(Request $r, string $action='view') {
  $u=$r->user('web'); abort_unless($u && $u->isActive() && in_array($u->role?->name,['Admin','CEO','PMO'],true) && $u->hasPermission('client_control.support.view') && $u->hasPermission('client_control.support.'.$action),403);
  return Project::query()->when($u->role?->name==='PMO',fn($q)=>$q->where('assigned_pmo_id',$u->id));
 }
 private function tickets(Request $r) { return DB::table('client_support_tickets as t')->join('projects as p','p.id','=','t.project_id')->join('client_support_categories as c','c.id','=','t.category_id')->leftJoin('users as u','u.id','=','t.assigned_to')->whereIn('t.project_id',$this->projects($r)->select('id'))->select('t.*','p.project_name','c.name as category','u.name as assignee'); }
 public function index(Request $r) {
  $projects=$this->projects($r)->orderBy('project_name')->get(); $categories=DB::table('client_support_categories')->orderBy('sort_order')->get();
  $q=$this->tickets($r); $counts=(clone $q)->reorder()->select('t.status',DB::raw('count(*) as total'))->groupBy('t.status')->pluck('total','status');
  $q=$this->filtered($r,$q);
  $tickets=$q->orderByDesc('t.last_activity_at')->orderByDesc('t.id')->paginate(25)->withQueryString();
  return view('client-control.admin.support.index',compact('projects','categories','counts','tickets'));
 }
 private function filtered(Request $r,$q) {
  foreach(['project_id','category_id','status','priority'] as $f) if($r->filled($f)) $q->where('t.'.$f,$r->input($f));
  if($r->filled('search')) { $s='%'.$r->input('search').'%'; $q->where(fn($q)=>$q->where('t.ticket_number','like',$s)->orWhere('t.subject','like',$s)); }
  return $q;
 }
 public function exportRegister(Request $r,string $format) {
  $this->projects($r,'export');
  $tickets=$this->filtered($r,$this->tickets($r))->leftJoin('client_users as cu','cu.id','=','t.client_user_id')->addSelect('cu.name as client_name')->orderByDesc('t.last_activity_at')->orderByDesc('t.id')->get();
  $rows=[]; foreach($tickets as $t) { $rows[]= [count($rows)+1,$t->ticket_number,$t->project_name,$t->client_name,$t->request_type,$t->category,$t->subject,$t->priority,$t->assignee??'Unassigned',$t->created_at,$t->expected_resolution_date,$t->status,$t->resolved_at,(!in_array($t->status,['Resolved','Closed']) && $t->expected_resolution_date && $t->expected_resolution_date<now()->toDateString()) ? (int) \Carbon\Carbon::parse($t->expected_resolution_date)->diffInDays(now()->startOfDay()) : 0]; }
  $sections=[['title'=>'Ticket Register','headers'=>['S.No.','Ticket No.','Project','Client','Type','Category','Subject','Priority','Assigned to','Submitted','Due date','Status','Resolved','Overdue days'],'rows'=>$rows]];
  return $this->report($r,$format,'Client Support Ticket Register','Support-Ticket-Register',$sections,true);
 }
 public function exportTicket(Request $r,int $ticket,string $format) {
  $this->projects($r,'export'); $t=$this->tickets($r)->leftJoin('client_users as cu','cu.id','=','t.client_user_id')->addSelect('cu.name as client_name')->where('t.id',$ticket)->first(); abort_unless($t,404);
  $u=$r->user('web'); $messages=DB::table('client_support_messages as m')->leftJoin('users as u','u.id','=','m.user_id')->where('m.ticket_id',$ticket)->when(!$u->hasPermission('client_control.support.internal_notes'),fn($q)=>$q->where('m.visibility','Public'))->orderBy('m.id')->get(['m.*','u.name as author']);
  $attachments=$u->hasPermission('client_control.support.download')?SC::attachments($messages):collect();
  $sections=[['title'=>'Ticket Details','headers'=>['Field','Value'],'rows'=>[['Ticket',$t->ticket_number],['Project',$t->project_name],['Client',$t->client_name],['Subject',$t->subject],['Category',$t->category],['Type',$t->request_type],['Priority',$t->priority],['Status',$t->status],['Assigned to',$t->assignee??'Unassigned'],['Submitted',$t->created_at],['Expected resolution',$t->expected_resolution_date],['Resolved',$t->resolved_at],['Closed',$t->closed_at],['Resolution',$t->resolution_remarks]]],['title'=>'Conversation','headers'=>['Date','Author','Visibility','Message','Attachments'],'rows'=>$messages->map(fn($m)=>[$m->created_at,$m->author??'Client',$m->visibility,$m->body,($attachments->get($m->id)??collect())->pluck('original_filename')->implode(', ')])->all()]];
  if($u->hasPermission('client_control.support.audit')) { $events=DB::table('client_support_events')->where('ticket_id',$ticket)->orderBy('id')->get(); $sections[]=['title'=>'Activity History','headers'=>['Date','Action','ERP user ID','Client user ID'],'rows'=>$events->map(fn($e)=>[$e->created_at,$e->action,$e->user_id,$e->client_user_id])->all()]; }
  return $this->report($r,$format,'Support Ticket '.$t->ticket_number,$t->ticket_number,$sections,false);
 }
 private function report(Request $r,string $format,string $title,string $name,array $sections,bool $landscape) {
  abort_unless(in_array($format,['pdf','xlsx'],true),404); $generated=now()->format('d M Y H:i'); $by=$r->user('web')->name; $filename=now()->format('d-m-Y').'-'.$name.'-Ravion';
  if($format==='pdf') return \Barryvdh\DomPDF\Facade\Pdf::loadView('client-control.admin.support.report',compact('title','sections','generated','by','landscape'))->setPaper('a4',$landscape?'landscape':'portrait')->download($filename.'.pdf');
  return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClientSupportReportExport($title,$sections,$generated,$by),$filename.'.xlsx');
 }
 private function grants($project) { return DB::table('client_project_grants as g')->join('client_users as c','c.id','=','g.client_user_id')->where('g.project_id',$project)->where('g.status','Approved')->whereNull('g.revoked_at')->where(fn($q)=>$q->whereNull('g.starts_at')->orWhere('g.starts_at','<=',now()))->where(fn($q)=>$q->whereNull('g.expires_at')->orWhere('g.expires_at','>',now())); }
 public function create(Request $r) {
  $projects=$this->projects($r,'create')->orderBy('project_name')->get(); $project=$r->filled('project')?$this->projects($r,'create')->findOrFail($r->integer('project')):null;
  $grants=$project?$this->grants($project->id)->select('g.id','g.client_user_id')->get():collect(); $categories=DB::table('client_support_categories')->where('is_active',true)->orderBy('sort_order')->get();
  return view('client-control.admin.support.create',compact('projects','project','grants','categories'));
 }
 public function store(Request $r) {
  $data=$r->validate(['project_id'=>'required|integer','grant_id'=>'required|integer','category_id'=>['required',Rule::exists('client_support_categories','id')->where('is_active',true)],'subject'=>'required|string|max:255','body'=>'required|string|max:20000','request_type'=>['required',Rule::in(['Request','Issue','Clarification','Complaint','Information','Other'])],'priority'=>['required',Rule::in(['Normal','High','Urgent'])]]);
  $id=DB::transaction(function()use($r,$data){
   $p=$this->projects($r,'create')->lockForUpdate()->findOrFail($data['project_id']); $g=$this->grants($p->id)->where('g.id',$data['grant_id'])->select('g.*')->lockForUpdate()->first(); abort_unless($g,422,'An active approved client grant is required.');
   $year=now()->year; $division='RH'; $now=now(); DB::table('client_support_sequences')->insertOrIgnore(['division_code'=>$division,'year'=>$year,'last_number'=>0,'created_at'=>$now,'updated_at'=>$now]); $seq=DB::table('client_support_sequences')->where('division_code',$division)->where('year',$year)->lockForUpdate()->first(); $n=$seq->last_number+1; DB::table('client_support_sequences')->where('id',$seq->id)->update(['last_number'=>$n,'updated_at'=>$now]);
   $id=DB::table('client_support_tickets')->insertGetId(['ticket_number'=>sprintf('RH-SUP-%d-%06d',$year,$n),'project_id'=>$p->id,'category_id'=>$data['category_id'],'client_user_id'=>$g->client_user_id,'grant_id'=>$g->id,'created_by_user_id'=>$r->user('web')->id,'assigned_to'=>$p->assigned_pmo_id,'subject'=>$data['subject'],'request_type'=>$data['request_type'],'priority'=>$data['priority'],'status'=>'Open','last_activity_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
   DB::table('client_support_messages')->insert(['ticket_id'=>$id,'user_id'=>$r->user('web')->id,'visibility'=>'Public','body'=>$data['body'],'created_at'=>$now,'updated_at'=>$now]);
   DB::table('client_support_events')->insert(['ticket_id'=>$id,'project_id'=>$p->id,'user_id'=>$r->user('web')->id,'action'=>'ticket_created','details'=>json_encode(['assigned_to'=>$p->assigned_pmo_id]),'ip_address'=>$r->ip(),'created_at'=>$now]); SN::send($id,'New support ticket',$r->user('web')->id); return $id;
  }); return redirect()->route('client-control.admin.support.tickets.show',$id)->with('success','Ticket created.');
 }
 public function show(Request $r,int $ticket) { $ticket=$this->tickets($r)->where('t.id',$ticket)->first(); abort_unless($ticket,404); $messages=DB::table('client_support_messages as m')->leftJoin('users as u','u.id','=','m.user_id')->where('m.ticket_id',$ticket->id)->orderBy('m.id')->get(['m.*','u.name as author']); $attachments=SC::attachments($messages); $events=$r->user('web')->hasPermission('client_control.support.audit')?DB::table('client_support_events')->where('ticket_id',$ticket->id)->orderByDesc('id')->get():collect(); return view('client-control.admin.support.show',compact('ticket','messages','events','attachments')); }
 public function reply(Request $r,int $ticket) {
  $visibility=$r->validate(['visibility'=>'required|in:Public,Internal'])['visibility']; $action=$visibility==='Internal'?'internal_notes':'reply'; $this->projects($r,$action); $data=$r->validate(SC::rules()); $paths=[];
  try { DB::transaction(function()use($r,$ticket,$visibility,$action,$data,&$paths){ $t=DB::table('client_support_tickets')->whereIn('project_id',$this->projects($r,$action)->select('id'))->where('id',$ticket)->lockForUpdate()->first(); abort_unless($t,404); abort_if(in_array($t->status,['Resolved','Closed']) && $visibility==='Public',422,'Reopen this ticket before replying.'); SC::save($r,$t,$visibility,['user_id'=>$r->user('web')->id],$data['body'],$r->file('attachments',[]),$paths); }); } catch(\Throwable $e){ SC::cleanup($paths); throw $e; }
  return back()->with('success','Message saved.');
 }
 public function manage(Request $r,int $ticket) {
  $d=$r->validate(['action'=>'required|in:update,progress,await,resolve,close,reopen,assign','priority'=>'nullable|in:Normal,High,Urgent','subject'=>'nullable|string|max:255','expected_resolution_date'=>'nullable|date_format:Y-m-d','remarks'=>'nullable|string|max:5000']);
  $action=$d['action']; $permission=match($action){'resolve'=>'resolve','close'=>'close','reopen'=>'reopen','assign'=>'assign',default=>'update'};
  DB::transaction(function()use($r,$ticket,$d,$action,$permission){
   $t=DB::table('client_support_tickets')->whereIn('project_id',$this->projects($r,$permission)->select('id'))->where('id',$ticket)->lockForUpdate()->first(); abort_unless($t,404);
   $now=now(); $changes=[]; $remarks=trim($d['remarks']??'');
   if($action==='update'){ abort_if(in_array($t->status,['Resolved','Closed']),422,'Reopen before editing.'); $r->validate(['subject'=>'required|string|max:255','priority'=>'required|in:Normal,High,Urgent']); $changes=['subject'=>$d['subject'],'priority'=>$d['priority'],'expected_resolution_date'=>$d['expected_resolution_date']??null]; }
   elseif($action==='assign'){ abort_unless(in_array($r->user('web')->role?->name,['Admin','CEO']),403); $project=Project::query()->findOrFail($t->project_id); $assignee=\App\Models\User::find($project->assigned_pmo_id); abort_unless($assignee && $assignee->isActive() && $assignee->role?->name==='PMO' && $assignee->hasPermission('client_control.support.view'),422,'Assign an active authorized PMO in Project Setup first.'); $changes=['assigned_to'=>$assignee->id]; }
   else { $allowed=match($action){'progress','await','resolve'=>['Open','In Progress','Awaiting Client'],'close'=>['Resolved'],'reopen'=>['Resolved','Closed']}; abort_unless(in_array($t->status,$allowed,true),422,'Invalid status transition.'); abort_if(in_array($action,['resolve','reopen','close']) && $remarks==='',422,'Remarks are required.'); $changes['status']=match($action){'progress','reopen'=>'In Progress','await'=>'Awaiting Client','resolve'=>'Resolved','close'=>'Closed'}; if($action==='resolve'){ $changes['resolved_at']=$now; $changes['resolution_remarks']=$remarks; } if($action==='close') $changes['closed_at']=$now; if($action==='reopen'){ $changes['resolved_at']=null; $changes['closed_at']=null; $changes['resolution_remarks']=null; } }
   $before=array_intersect_key((array)$t,$changes); DB::table('client_support_tickets')->where('id',$ticket)->update($changes+['updated_at'=>$now,'last_activity_at'=>$now]);
   DB::table('client_support_events')->insert(['ticket_id'=>$ticket,'project_id'=>$t->project_id,'user_id'=>$r->user('web')->id,'action'=>'ticket_'.$action,'details'=>json_encode(['before'=>$before,'after'=>$changes,'remarks'=>$remarks],JSON_THROW_ON_ERROR),'ip_address'=>$r->ip(),'created_at'=>$now]);
   if(in_array($action,['resolve','close','reopen','assign'],true)) SN::send($ticket,match($action){'resolve'=>'Support ticket resolved','close'=>'Support ticket closed','reopen'=>'Support ticket reopened','assign'=>'Support ticket reassigned'},$r->user('web')->id);
   if($action==='resolve') DB::table('client_support_messages')->insert(['ticket_id'=>$ticket,'user_id'=>$r->user('web')->id,'visibility'=>'Public','body'=>'Resolution: '.$remarks,'created_at'=>$now,'updated_at'=>$now]);
  }); return back()->with('success','Ticket updated.');
 }
 public function attachment(Request $r,int $ticket,int $attachment) { $this->projects($r,'download'); abort_unless($this->tickets($r)->where('t.id',$ticket)->exists(),404); return SC::download($ticket,$attachment,false); }
}
