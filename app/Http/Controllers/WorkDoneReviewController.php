<?php
namespace App\Http\Controllers;
use App\Models\WorkDoneHeader;
use App\Models\WorkDoneItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class WorkDoneReviewController extends Controller {
 private function reviewerOnly(): void { abort_unless(in_array(auth()->user()?->role?->name,['PMO','Admin'],true),403); }
 public function index(): View {
  $this->reviewerOnly();
  $entries=WorkDoneHeader::with(['project','engineer'])->withCount('items')->whereIn('status',['Submitted','Verified','Returned'])
   ->orderByRaw("CASE WHEN status='Submitted' THEN 0 WHEN status='Returned' THEN 1 ELSE 2 END")->latest('id')->paginate(20);
  $counts=WorkDoneHeader::selectRaw('status,count(*) as total')->whereIn('status',['Submitted','Verified','Returned'])->groupBy('status')->pluck('total','status');
  return view('work-done.review.index',compact('entries','counts'));
 }
 public function submit(WorkDoneHeader $workDone): RedirectResponse {
  abort_unless((int)$workDone->user_id===(int)auth()->id(),403);
  DB::transaction(function() use($workDone) {
   $header=WorkDoneHeader::whereKey($workDone->id)->lockForUpdate()->firstOrFail();
   abort_unless(in_array($header->status,['Draft','Returned'],true),409);
   $items=$header->items()->lockForUpdate()->get();
   if($items->isEmpty()) throw ValidationException::withMessages(['work_done'=>'Add at least one activity.']);
   if($header->status==='Returned') {
    // Verified items stay verified. Only corrected/returned items return for review.
    if($items->contains(fn($i)=>$i->review_status==='Returned')) throw ValidationException::withMessages(['work_done'=>'Resubmit returned activities individually first.']);
    abort_unless($items->every(fn($i)=>in_array($i->review_status,['Verified','Submitted'],true)),409);
   } else {
    $header->items()->where('review_status','Draft')->update(['review_status'=>'Submitted']);
   }
   $header->forceFill(['status'=>'Submitted','submitted_at'=>now(),'returned_at'=>null,'returned_by'=>null])->save();
  });
  return back()->with('success','Work Done submitted for PMO review.');
 }
 public function verifyItem(WorkDoneHeader $workDone, WorkDoneItem $item): RedirectResponse {
  $this->reviewerOnly();
  DB::transaction(function() use($workDone,$item) {
   $header=WorkDoneHeader::whereKey($workDone->id)->lockForUpdate()->firstOrFail();
   abort_unless($header->status==='Submitted',409);
   $entry=$header->items()->whereKey($item->id)->lockForUpdate()->firstOrFail();
   abort_unless($entry->review_status==='Submitted',409);
   $entry->forceFill(['review_status'=>'Verified','reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_remarks'=>null])->save();
   if(!$header->items()->where('review_status','!=','Verified')->exists()) {
    $header->forceFill(['status'=>'Verified','verified_at'=>now(),'verified_by'=>auth()->id(),'review_remarks'=>null])->save();
   }
  });
  return back()->with('success','Activity verified.');
 }
 public function returnItem(Request $request,WorkDoneHeader $workDone,WorkDoneItem $item): RedirectResponse {
  $this->reviewerOnly();
  $data=$request->validate(['review_remarks'=>['required','string','min:5','max:2000']]);
  DB::transaction(function() use($workDone,$item,$data) {
   $header=WorkDoneHeader::whereKey($workDone->id)->lockForUpdate()->firstOrFail();
   abort_unless($header->status==='Submitted',409);
   $entry=$header->items()->whereKey($item->id)->lockForUpdate()->firstOrFail();
   abort_unless($entry->review_status==='Submitted',409);
   $entry->forceFill(['review_status'=>'Returned','reviewed_by'=>auth()->id(),'reviewed_at'=>now(),'review_remarks'=>$data['review_remarks']])->save();
   $header->forceFill(['status'=>'Returned','returned_at'=>now(),'returned_by'=>auth()->id(),'review_remarks'=>'One or more activities require correction.'])->save();
  });
  return back()->with('success','Activity returned for correction; other verified activities remain locked.');
 }
 public function resubmitItem(WorkDoneHeader $workDone,WorkDoneItem $item): RedirectResponse {
  abort_unless((int)$workDone->user_id===(int)auth()->id(),403);
  DB::transaction(function() use($workDone,$item) {
   $header=WorkDoneHeader::whereKey($workDone->id)->lockForUpdate()->firstOrFail();
   abort_unless($header->status==='Returned',409);
   $entry=$header->items()->whereKey($item->id)->lockForUpdate()->firstOrFail();
   abort_unless($entry->review_status==='Returned',409);
   $entry->forceFill(['review_status'=>'Submitted','reviewed_at'=>null,'reviewed_by'=>null])->save();
   if(!$header->items()->where('review_status','Returned')->exists()) {
    $header->forceFill(['status'=>'Submitted','submitted_at'=>now(),'returned_at'=>null,'returned_by'=>null])->save();
   }
  });
  return back()->with('success','Activity resubmitted for review.');
 }
 // Retain old route names, but disable blanket approvals so no item can be bypassed.
 public function verify(WorkDoneHeader $workDone): RedirectResponse { $this->reviewerOnly(); abort(409,'Approve each activity individually.'); }
 public function returnForCorrection(Request $request,WorkDoneHeader $workDone): RedirectResponse { $this->reviewerOnly(); abort(409,'Return the specific activity for correction.'); }
}
