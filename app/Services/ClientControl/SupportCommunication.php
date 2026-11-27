<?php
namespace App\Services\ClientControl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class SupportCommunication {
 public static function rules(): array { return ['body'=>'required|string|max:20000','attachments'=>'nullable|array|max:5','attachments.*'=>'required|file|mimes:pdf,jpg,jpeg,png|max:10240']; }
 public static function save($r,$ticket,$visibility,$actor,$body,$files,array &$paths) {
  $now=now(); $id=DB::table('client_support_messages')->insertGetId(array_merge($actor,['ticket_id'=>$ticket->id,'visibility'=>$visibility,'body'=>$body,'created_at'=>$now,'updated_at'=>$now]));
  foreach($files as $file) {
   $hash=hash_file('sha256',$file->getRealPath()); $path='client-support/'.Str::uuid(); $paths[]=$path;
   abort_unless(Storage::disk('local')->putFileAs('client-support',$file,basename($path)),500,'Unable to save attachment.');
   DB::table('client_support_attachments')->insert(['message_id'=>$id,'original_filename'=>Str::limit(basename(str_replace('\\','/',$file->getClientOriginalName())),240,''),'disk'=>'local','storage_path'=>$path,'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize(),'sha256'=>$hash,'created_at'=>$now,'updated_at'=>$now]);
  }
  DB::table('client_support_tickets')->where('id',$ticket->id)->update(['last_activity_at'=>$now,'updated_at'=>$now]);
  DB::table('client_support_events')->insert(array_merge($actor,['ticket_id'=>$ticket->id,'project_id'=>$ticket->project_id,'action'=>$visibility==='Internal'?'internal_note_added':'reply_added','entity_type'=>'message','entity_id'=>$id,'ip_address'=>$r->ip(),'created_at'=>$now]));
 }
 public static function cleanup(array $paths): void { foreach($paths as $path) Storage::disk('local')->delete($path); }
 public static function attachments($messages) { return DB::table('client_support_attachments')->whereIn('message_id',$messages->pluck('id'))->get()->groupBy('message_id'); }
 public static function download($ticket,$attachment,bool $publicOnly) {
  $a=DB::table('client_support_attachments as a')->join('client_support_messages as m','m.id','=','a.message_id')->where('m.ticket_id',$ticket)->where('a.id',$attachment)->when($publicOnly,fn($q)=>$q->where('m.visibility','Public'))->select('a.*')->first();
  abort_unless($a && $a->disk==='local' && Storage::disk('local')->exists($a->storage_path),404);
  return Storage::disk('local')->download($a->storage_path,$a->original_filename,['Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff','Content-Type'=>'application/octet-stream']);
 }
}
