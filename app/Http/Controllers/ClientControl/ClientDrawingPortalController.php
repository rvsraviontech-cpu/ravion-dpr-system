<?php
namespace App\Http\Controllers\ClientControl;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientControl\ClientProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class ClientDrawingPortalController extends Controller {
 private function publications(Request $r,Project $project,ClientProjectAccess $access) {
  $client=$r->user('client'); abort_unless($client,401); $access->assertAllowed($client,(int)$project->id);
  return DB::table('client_drawing_publications as p')->join('client_drawings as d','d.id','=','p.drawing_id')->join('client_drawing_revisions as v',function($j){$j->on('v.id','=','p.revision_id')->on('v.drawing_id','=','d.id');})->where('d.project_id',$project->id)->whereColumn('p.active_drawing_id','d.id')->whereNull('p.ended_at')->whereNull('d.archived_at')->where('v.status','Published')->select('p.*');
 }
 public function index(Request $r,Project $project,ClientProjectAccess $access) {
  $publications=$this->publications($r,$project,$access)->orderByDesc('p.published_at')->get(); foreach($publications as $p) $p->metadata=json_decode($p->snapshot,true,512,JSON_THROW_ON_ERROR);
  $responses=DB::table('client_drawing_responses')->whereIn('publication_id',$publications->pluck('id'))->where('client_user_id',$r->user('client')->id)->get()->keyBy('publication_id');
  return response()->view('client-control.drawings',['project'=>$project,'publications'=>$publications,'responses'=>$responses])->header('Cache-Control','private, no-store');
 }
 public function respond(Request $r,Project $project,int $publication,ClientProjectAccess $access) {
  $client=$r->user('client'); abort_unless($client,401); $access->assertAllowed($client,(int)$project->id);
  $data=$r->validate(['response'=>'required|in:Approved,Changes Requested','remarks'=>'nullable|string|max:5000|required_if:response,Changes Requested','confirm_response'=>'accepted']);
  DB::transaction(function() use($r,$project,$publication,$access,$client,$data){
   $candidate=$this->publications($r,$project,$access)->where('p.id',$publication)->first(); abort_unless($candidate,404);
   $drawing=DB::table('client_drawings')->where('id',$candidate->drawing_id)->where('project_id',$project->id)->lockForUpdate()->first(); abort_unless($drawing && !$drawing->archived_at,409);
   $p=DB::table('client_drawing_publications')->where('id',$publication)->where('active_drawing_id',$drawing->id)->whereNull('ended_at')->lockForUpdate()->first();
   abort_unless($p && $p->client_approval_required,409,'This publication is not accepting responses.');
   abort_unless(DB::table('client_drawing_revisions')->where('id',$p->revision_id)->where('drawing_id',$drawing->id)->where('status','Published')->exists(),409);
   $now=now();
   $grant=DB::table('client_project_grants')->where('client_user_id',$client->id)->where('project_id',$project->id)->where('status','Approved')->whereNull('revoked_at')->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',$now))->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',$now))->orderByDesc('id')->lockForUpdate()->first();
   abort_unless($grant,403); $access->assertAllowed($client,(int)$project->id);
   abort_if(DB::table('client_drawing_responses')->where('publication_id',$p->id)->where('client_user_id',$client->id)->exists(),409,'You have already responded to this publication.');
   $id=DB::table('client_drawing_responses')->insertGetId(['publication_id'=>$p->id,'client_user_id'=>$client->id,'grant_id'=>$grant->id,'response'=>$data['response'],'remarks'=>$data['remarks']??null,'ip_address'=>$r->ip(),'responded_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
   DB::table('client_drawing_events')->insert(['drawing_id'=>$drawing->id,'revision_id'=>$p->revision_id,'actor_guard'=>'client','actor_id'=>$client->id,'action'=>'client_response','details'=>json_encode(['publication_id'=>$p->id,'response_id'=>$id,'response'=>$data['response']],JSON_THROW_ON_ERROR),'ip_address'=>$r->ip(),'created_at'=>$now]);
  },3);
  return redirect()->route('client.drawings.index',$project->id)->with('success','Your drawing response has been recorded.');
 }
 public function file(Request $r,Project $project,int $publication,string $mode,ClientProjectAccess $access) {
  abort_unless(in_array($mode,['preview','download']),404);$p=$this->publications($r,$project,$access)->where('p.id',$publication)->first();abort_unless($p,404);
  if($mode==='download') abort_unless($p->client_download_allowed,403);
  $files=DB::table('client_drawing_files')->where('revision_id',$p->revision_id)->get();$f=$files->firstWhere('kind','original');
  if($mode==='preview' && !in_array($f?->mime_type,['application/pdf','image/jpeg','image/png'])) $f=$files->firstWhere('kind','preview');
  abort_unless($f && $f->disk==='local' && Storage::disk('local')->exists($f->storage_path),404);
  if($mode==='preview') abort_unless(in_array($f->mime_type,['application/pdf','image/jpeg','image/png']),404);
  if($mode==='preview' && $r->query('thumbnail')==='1') return \App\Services\ClientControl\DrawingThumbnail::response($f);
  DB::table('client_drawing_events')->insert(['drawing_id'=>$p->drawing_id,'revision_id'=>$p->revision_id,'actor_guard'=>'client','actor_id'=>$r->user('client')->id,'action'=>'client_'.$mode,'details'=>json_encode(['publication_id'=>$p->id,'file_id'=>$f->id]),'ip_address'=>$r->ip(),'created_at'=>now()]);
  return Storage::disk('local')->response($f->storage_path,$f->original_filename,['Content-Type'=>$mode==='preview'?$f->mime_type:'application/octet-stream','X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store'],$mode==='preview'?'inline':'attachment');
 }
}
