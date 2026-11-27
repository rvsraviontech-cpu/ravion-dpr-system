<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\ClientControl\SupportNotifications;
use App\Notifications\SupportTicketNotification;
class ErpNotificationController extends Controller {
 private function user(Request $r) { $u=$r->user('web'); abort_unless($u && $u->isActive(),403); return $u; }
 public function index(Request $r) { $u=$this->user($r); $notifications=$u->notifications()->paginate(25); return response()->view('notifications.index',compact('notifications'))->header('Cache-Control','private, no-store'); }
 public function open(Request $r,string $id) { $u=$this->user($r); $n=$u->notifications()->findOrFail($id); abort_unless($n->type===SupportTicketNotification::class,404); $ticket=(int)($n->data['ticket_id']??0); abort_unless(SupportNotifications::canOpen($u,$ticket),403); $n->markAsRead(); return redirect()->route('client-control.admin.support.tickets.show',$ticket); }
 public function read(Request $r,string $id) { $this->user($r)->notifications()->findOrFail($id)->markAsRead(); return back(); }
 public function readAll(Request $r) { $this->user($r)->unreadNotifications()->update(['read_at'=>now()]); return back(); }
}
