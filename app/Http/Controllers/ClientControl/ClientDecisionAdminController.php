<?php
namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientDecisionAdminController extends Controller
{
    private function permission(string $name): void
    {
        abort_unless(auth()->user()?->hasPermission('client_control.decisions.'.$name), 403);
    }
    private function decision(int $id): object
    {
        return DB::table('client_decisions')->where('id',$id)->first() ?? abort(404);
    }
    private function event(int $id,string $action,array $details=[]): void
    {
        DB::table('client_decision_events')->insert(['decision_id'=>$id,'actor_guard'=>'web','actor_id'=>auth()->id(),'action'=>$action,'details'=>json_encode($details),'ip_address'=>request()->ip(),'created_at'=>now()]);
    }
    public function index(Request $request)
    {
        $this->permission('view');

        $validated = $request->validate([
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'status' => ['nullable', 'in:Draft,Submitted,Approved,Published,Responded,Acknowledged,Closed,Withdrawn'],
            'attention' => ['nullable', 'in:clarifications,overdue'],
        ]);

        // A clarification is pending only while the decision is published and
        // the client's question has not yet received an internal reply.
        $pendingClarification = function ($query) {
            $query->selectRaw('1')
                ->from('client_decision_clarifications as c')
                ->whereColumn('c.decision_id', 'd.id')
                ->whereNull('c.replied_at');
        };

        $base = DB::table('client_decisions as d');
        if (!empty($validated['project_id'])) {
            $base->where('d.project_id', $validated['project_id']);
        }

        // Summary counts are project-scoped but independent of the register's
        // status and attention filters.
        $summary = [
            'awaiting_client' => (clone $base)->where('d.status', 'Published')
                ->whereNotExists($pendingClarification)->count(),
            'awaiting_pmo' => (clone $base)->where('d.status', 'Published')
                ->whereExists($pendingClarification)->count(),
            'responded' => (clone $base)->whereIn('d.status', ['Responded', 'Acknowledged'])->count(),
            'closed' => (clone $base)->where('d.status', 'Closed')->count(),
            'overdue' => (clone $base)->where('d.status', 'Published')
                ->whereNotNull('d.response_due_at')
                ->whereDate('d.response_due_at', '<', today())
                ->whereNotExists($pendingClarification)->count(),
        ];

        $query = (clone $base)
            ->join('projects as p', 'p.id', '=', 'd.project_id')
            ->select('d.*', 'p.project_name', 'p.project_code')
            ->selectRaw('CASE WHEN d.status = ? AND EXISTS (SELECT 1 FROM client_decision_clarifications c WHERE c.decision_id = d.id AND c.replied_at IS NULL) THEN 1 ELSE 0 END AS awaiting_pmo_reply', ['Published']);

        if (!empty($validated['status'])) {
            $query->where('d.status', $validated['status']);
        }
        if (($validated['attention'] ?? null) === 'clarifications') {
            $query->where('d.status', 'Published')->whereExists($pendingClarification);
        } elseif (($validated['attention'] ?? null) === 'overdue') {
            $query->where('d.status', 'Published')
                ->whereNotNull('d.response_due_at')
                ->whereDate('d.response_due_at', '<', today())
                ->whereNotExists($pendingClarification);
        }

        return view('client-control.admin.decisions.index', [
            'decisions' => $query->orderByDesc('d.id')->paginate(20)->withQueryString(),
            'projects' => DB::table('projects')->select('id', 'project_name')->orderBy('project_name')->get(),
            'summary' => $summary,
        ]);
    }
    public function create()
    {
        $this->permission('create');
        return view('client-control.admin.decisions.create',['projects'=>DB::table('projects')->select('id','project_name')->orderBy('project_name')->get()]);
    }
    public function store(Request $request)
    {
        $this->permission('create');
        $v=$request->validate(['project_id'=>'required|integer|exists:projects,id','title'=>'required|string|max:255','category'=>'required|in:Design,Materials,Finishes,Layout,Commercial,Other','description'=>'required|string|max:20000','response_due_at'=>'nullable|date|after_or_equal:today','has_cost_impact'=>'nullable|boolean','has_time_impact'=>'nullable|boolean','impact_details'=>'nullable|string|max:10000','options'=>'nullable|array|max:12','options.*.label'=>'required|string|max:200','options.*.details'=>'nullable|string|max:2000','files'=>'nullable|array|max:8','files.*'=>'file|mimes:pdf,jpg,jpeg,png,webp|max:10240']);
        if(($request->boolean('has_cost_impact') || $request->boolean('has_time_impact')) && blank($v['impact_details']??null)) throw ValidationException::withMessages(['impact_details'=>'Explain all cost/time implications before publishing.']);
        $id=DB::transaction(function()use($request,$v){
            $id=DB::table('client_decisions')->insertGetId(['project_id'=>$v['project_id'],'reference'=>'P-'.str_replace('-', '', (string) Str::uuid()),'title'=>$v['title'],'category'=>$v['category'],'description'=>$v['description'],'status'=>'Draft','response_due_at'=>$v['response_due_at']??null,'has_cost_impact'=>$request->boolean('has_cost_impact'),'has_time_impact'=>$request->boolean('has_time_impact'),'impact_details'=>$v['impact_details']??null,'created_by'=>auth()->id(),'created_at'=>now(),'updated_at'=>now()]);
            DB::table('client_decisions')->where('id',$id)->update(['reference'=>'RCD-'.now()->format('Y').'-'.str_pad((string)$id,6,'0',STR_PAD_LEFT)]);
            foreach(($v['options']??[]) as $i=>$option){DB::table('client_decision_options')->insert(['decision_id'=>$id,'label'=>$option['label'],'details'=>$option['details']??null,'sort_order'=>$i,'created_at'=>now(),'updated_at'=>now()]);}
            $this->event($id,'created'); return $id;
        });
        try {
            foreach($request->file('files',[]) as $file){
                $path=$file->store('client-decisions/'.$id,'local');
                DB::table('client_decision_files')->insert(['decision_id'=>$id,'disk'=>'local','path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType()??'application/octet-stream','size_bytes'=>$file->getSize(),'uploaded_by'=>auth()->id(),'created_at'=>now(),'updated_at'=>now()]);
            }
        } catch(\Throwable $e) { report($e); return redirect()->route('client-control.admin.decisions.show',$id)->with('warning','Decision saved, but a file upload failed. Review attachments before submitting.'); }
        return redirect()->route('client-control.admin.decisions.show',$id)->with('success','Decision draft created.');
    }

    public function edit(int $decision)
    {
        $this->permission('update');
        $d = $this->decision($decision);
        abort_unless($d->status === 'Draft', 409, 'Only draft decisions can be edited.');
        return view('client-control.admin.decisions.edit', [
            'decision' => $d,
            'options' => DB::table('client_decision_options')->where('decision_id', $decision)->orderBy('sort_order')->get(),
            'files' => DB::table('client_decision_files')->where('decision_id', $decision)->get(),
        ]);
    }

    public function update(Request $request, int $decision)
    {
        $this->permission('update');
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Design,Materials,Finishes,Layout,Commercial,Other',
            'description' => 'required|string|max:20000',
            'response_due_at' => 'nullable|date',
            'has_cost_impact' => 'nullable|boolean',
            'has_time_impact' => 'nullable|boolean',
            'impact_details' => 'nullable|string|max:10000',
            'options' => 'nullable|array|max:3',
            'options.*.label' => 'nullable|string|max:200',
            'options.*.details' => 'nullable|string|max:2000',
            'files' => 'nullable|array|max:8',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'remove_files' => 'nullable|array',
            'remove_files.*' => 'integer|distinct',
        ]);
        if (($request->boolean('has_cost_impact') || $request->boolean('has_time_impact')) && blank($v['impact_details'] ?? null)) {
            throw ValidationException::withMessages(['impact_details' => 'Explain the cost or time implications.']);
        }
        $options = collect($v['options'] ?? [])->filter(fn ($o) => filled($o['label'] ?? null))->values();
        foreach (($v['options'] ?? []) as $o) {
            if (blank($o['label'] ?? null) && filled($o['details'] ?? null)) {
                throw ValidationException::withMessages(['options' => 'Provide an option label when adding option details.']);
            }
        }
        $newUploads = [];
        try {
            foreach ($request->file('files', []) as $file) {
                $path = $file->store('client-decisions/'.$decision, 'local');
                if (!$path) throw new \RuntimeException('Unable to store attachment.');
                $newUploads[] = [
                    'disk' => 'local', 'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                ];
            }
            $removePaths = DB::transaction(function () use ($decision, $request, $v, $options, $newUploads) {
                $d = DB::table('client_decisions')->where('id', $decision)->lockForUpdate()->first();
                abort_unless($d, 404);
                abort_unless($d->status === 'Draft', 409, 'This decision is no longer a draft.');
                $removeIds = $v['remove_files'] ?? [];
                $removeFiles = DB::table('client_decision_files')->where('decision_id', $decision)
                    ->whereIn('id', $removeIds)->get();
                abort_unless($removeFiles->count() === count($removeIds), 422, 'Invalid attachment selection.');
                $existingCount = DB::table('client_decision_files')->where('decision_id', $decision)->count();
                abort_if($existingCount - count($removeIds) + count($newUploads) > 8, 422, 'Maximum eight attachments per decision.');
                $before = [
                    'title' => $d->title, 'category' => $d->category, 'description' => $d->description,
                    'response_due_at' => $d->response_due_at, 'has_cost_impact' => $d->has_cost_impact,
                    'has_time_impact' => $d->has_time_impact, 'impact_details' => $d->impact_details,
                    'options' => DB::table('client_decision_options')->where('decision_id', $decision)->orderBy('sort_order')->get()->toArray(),
                ];
                DB::table('client_decisions')->where('id', $decision)->update([
                    'title' => $v['title'], 'category' => $v['category'], 'description' => $v['description'],
                    'response_due_at' => $v['response_due_at'] ?? null,
                    'has_cost_impact' => $request->boolean('has_cost_impact'),
                    'has_time_impact' => $request->boolean('has_time_impact'),
                    'impact_details' => $v['impact_details'] ?? null, 'updated_at' => now(),
                ]);
                DB::table('client_decision_options')->where('decision_id', $decision)->delete();
                foreach ($options as $i => $option) {
                    DB::table('client_decision_options')->insert([
                        'decision_id' => $decision, 'label' => $option['label'],
                        'details' => $option['details'] ?? null, 'sort_order' => $i,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                foreach ($newUploads as $upload) {
                    DB::table('client_decision_files')->insert(array_merge($upload, [
                        'decision_id' => $decision, 'uploaded_by' => auth()->id(),
                        'created_at' => now(), 'updated_at' => now(),
                    ]));
                }
                if (count($removeIds)) DB::table('client_decision_files')->where('decision_id', $decision)->whereIn('id', $removeIds)->delete();
                $this->event($decision, 'draft_updated', [
                    'before' => $before,
                    'after' => [
                        'title' => $v['title'], 'category' => $v['category'], 'description' => $v['description'],
                        'response_due_at' => $v['response_due_at'] ?? null,
                        'has_cost_impact' => $request->boolean('has_cost_impact'),
                        'has_time_impact' => $request->boolean('has_time_impact'),
                        'impact_details' => $v['impact_details'] ?? null, 'options' => $options->all(),
                    ],
                    'added_files' => array_column($newUploads, 'original_name'),
                    'removed_files' => $removeFiles->pluck('original_name')->all(),
                ]);
                return $removeFiles->pluck('path')->all();
            });
        } catch (\Throwable $e) {
            foreach ($newUploads as $upload) Storage::disk('local')->delete($upload['path']);
            throw $e;
        }
        foreach ($removePaths as $path) Storage::disk('local')->delete($path);
        return redirect()->route('client-control.admin.decisions.show', $decision)->with('success', 'Draft updated successfully.');
    }

    public function show(int $decision)
    {
        $this->permission('view');$d=$this->decision($decision);
        return view('client-control.admin.decisions.show',['decision'=>$d,'project'=>DB::table('projects')->where('id',$d->project_id)->first(),'options'=>DB::table('client_decision_options')->where('decision_id',$d->id)->orderBy('sort_order')->get(),'files'=>DB::table('client_decision_files')->where('decision_id',$d->id)->get(),'responses'=>DB::table('client_decision_responses as r')->leftJoin('client_users as u','u.id','=','r.client_user_id')->leftJoin('client_decision_options as o','o.id','=','r.option_id')->where('r.decision_id',$d->id)->select('r.*','o.label as selected_option_label')->orderByDesc('r.id')->get(),'clarifications'=>DB::table('client_decision_clarifications')->where('decision_id',$d->id)->orderBy('id')->get(),'events'=>DB::table('client_decision_events')->where('decision_id',$d->id)->orderByDesc('id')->get()]);
    }
    public function transition(Request $request,int $decision,string $action)
    {
        $permissions=['submit'=>'update','approve'=>'review','publish'=>'publish','withdraw'=>'withdraw','close'=>'close'];
        abort_unless(isset($permissions[$action]),404);$this->permission($permissions[$action]);
        DB::transaction(function()use($decision,$action){
            $d=DB::table('client_decisions')->where('id',$decision)->lockForUpdate()->first();abort_unless($d,404);
            $allowed=['submit'=>['Draft'],'approve'=>['Submitted'],'publish'=>['Approved'],'withdraw'=>['Published','Responded','Acknowledged'],'close'=>['Acknowledged']];
            abort_unless(in_array($d->status,$allowed[$action],true),422);
            if($action==='close') abort_if(DB::table('client_decision_clarifications')->where('decision_id',$decision)->whereNull('replied_at')->exists(),422,'An unanswered clarification prevents closure.');
            if($action==='publish'){
                abort_if(($d->has_cost_impact || $d->has_time_impact) && blank($d->impact_details),422,'Cost/time impact details are required.');
                abort_if(DB::table('client_decision_options')->where('decision_id',$decision)->count()===0,422,'At least one option is required.');
            }
            $status=['submit'=>'Submitted','approve'=>'Approved','publish'=>'Published','withdraw'=>'Withdrawn','close'=>'Closed'][$action];
            $update=['status'=>$status,'updated_at'=>now()];
            if($action==='approve'){$update['reviewed_by']=auth()->id();$update['reviewed_at']=now();}
            if($action==='close') abort_if(DB::table('client_decision_clarifications')->where('decision_id',$decision)->whereNull('replied_at')->exists(),422,'An unanswered clarification prevents closure.');
            if($action==='publish'){$update['published_by']=auth()->id();$update['published_at']=now();}
            if($action==='withdraw')$update['withdrawn_at']=now();
            if($action==='close')$update['closed_at']=now();
            DB::table('client_decisions')->where('id',$decision)->update($update);$this->event($decision,$action);
        });
        return back()->with('success','Decision updated.');
    }
    public function replyClarification(Request $request, int $decision, int $clarification)
    {
        $this->permission('update');
        $validated = $request->validate(['reply' => ['required', 'string', 'max:10000']]);
        DB::transaction(function () use ($request, $decision, $clarification, $validated) {
            $d = DB::table('client_decisions')->where('id', $decision)->lockForUpdate()->first();
            abort_unless($d, 404);
            abort_unless($d->status === 'Published' && $d->published_at !== null && $d->withdrawn_at === null,
                409, 'Clarifications can be answered only while the decision is published.');
            $question = DB::table('client_decision_clarifications')
                ->where('id', $clarification)->where('decision_id', $decision)
                ->whereNull('replied_at')->first();
            abort_unless($question, 409, 'This clarification has already been answered.');
            DB::table('client_decision_clarifications')->where('id', $clarification)->update([
                'reply' => $validated['reply'], 'replied_by' => auth()->id(),
                'reply_ip_address' => $request->ip(), 'replied_at' => now(), 'updated_at' => now(),
            ]);
            $this->event($decision, 'clarification_replied', ['clarification_id' => $clarification]);
        });
        return back()->with('success', 'Clarification reply recorded. The client may now respond.');
    }

    public function acknowledge(int $decision,int $response)
    {
        $this->permission('acknowledge');
        DB::transaction(function()use($decision,$response){
            $d=DB::table('client_decisions')->where('id',$decision)->lockForUpdate()->first();abort_unless($d && $d->status==='Responded',422);
            $r=DB::table('client_decision_responses')->where('decision_id',$decision)->where('id',$response)->whereNull('acknowledged_at')->whereIn('response',['Approve','Reject'])->first();abort_unless($r,422);
            DB::table('client_decision_responses')->where('id',$response)->update(['acknowledged_by'=>auth()->id(),'acknowledged_at'=>now(),'updated_at'=>now()]);
            DB::table('client_decisions')->where('id',$decision)->update(['status'=>'Acknowledged','updated_at'=>now()]);$this->event($decision,'acknowledged',['response_id'=>$response]);
        });return back()->with('success','Response acknowledged.');
    }
    public function download(int $decision,int $file)
    {
        $this->permission('view');$this->decision($decision);
        $f=DB::table('client_decision_files')->where('id',$file)->where('decision_id',$decision)->first();abort_unless($f,404);
        abort_unless($f->disk==='local' && Storage::disk('local')->exists($f->path),404);
        return Storage::disk('local')->download($f->path,$f->original_name);
    }
}
