<?php
namespace App\Services\ClientControl;
use App\Models\User;
use App\Notifications\SupportTicketNotification;
use Illuminate\Support\Facades\DB;
class SupportNotifications {
 public static function send(int $ticket, string $event, ?int $actor=null): void {
  $t=DB::table('client_support_tickets as t')->join('projects as p','p.id','=','t.project_id')->where('t.id',$ticket)->first(['t.id','t.ticket_number','t.project_id','t.assigned_to','p.assigned_pmo_id']);
  if(!$t) return;
  $users=User::with('role')->where('account_status','Active')->where(function($q)use($t){ $q->whereHas('role',fn($r)=>$r->whereIn('name',['Admin','CEO']))->orWhere('id',$t->assigned_pmo_id); })->get();
  foreach($users as $u){
   if($actor!==null && (int)$u->id===$actor) continue;
   if(!self::allowed($u,$t)) continue;
   $u->notify(new SupportTicketNotification(['title'=>$event,'ticket_number'=>$t->ticket_number,'ticket_id'=>(int)$t->id]));
  }
 }
 public static function allowed(User $u, object $t): bool {
  return $u->isActive() && $u->hasPermission('client_control.support.view') && (in_array($u->role?->name,['Admin','CEO'],true) || ($u->role?->name==='PMO' && (int)$u->id===(int)$t->assigned_to && (int)$u->id===(int)$t->assigned_pmo_id));
 }
 public static function canOpen(User $u,int $id): bool {
  $t=DB::table('client_support_tickets as t')->join('projects as p','p.id','=','t.project_id')->where('t.id',$id)->first(['t.assigned_to','p.assigned_pmo_id']);
  return $t && self::allowed($u,$t);
 }
}
