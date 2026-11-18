<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientProgressReportController extends Controller
{
    private function permission(Request $request, string $name): void
    {
        $user = $request->user('web');
        abort_unless($user && DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $user->role_id)
            ->where('permissions.name', $name)
            ->where('permissions.is_active', 1)->exists(), 403);
    }

    private function allowed(Request $request, string $name): bool
    {
        $user = $request->user('web');
        return (bool) ($user && DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $user->role_id)
            ->where('permissions.name', $name)
            ->where('permissions.is_active', 1)->exists());
    }

    private function availableActions(Request $request, ?object $report = null, $reviews = null): array
    {
        $userId = (int) optional($request->user('web'))->id;
        $canDraft = $this->allowed($request, 'client_control.report_draft');
        $canSubmit = $this->allowed($request, 'client_control.report_submit');
        $canVerify = $this->allowed($request, 'client_control.report_pm_verify');
        $departments = [];
        if ($report && $report->status === 'Pending Department Signoffs') {
            $done = collect($reviews ?? [])->pluck('department')->all();
            foreach (self::DEPARTMENTS as $department => $permission) {
                if (!in_array($department, $done, true) && $this->allowed($request, $permission)) {
                    $departments[] = $department;
                }
            }
        }
        return [
            'draft' => $canDraft && (!$report || $report->status === 'Draft'),
            'submit' => $canSubmit && $report && $report->status === 'Draft',
            'pm_verify' => $canVerify && $report && $report->status === 'Pending PM Verification',
            'departments' => $departments,
        ];
    }

    private function audit(Request $request, string $action, int $reportId, int $projectId, array $meta = []): void
    {
        DB::table('client_audit_events')->insert([
            'project_id' => $projectId, 'actor_type' => 'ERP User',
            'actor_id' => $request->user('web')->id, 'action' => $action,
            'subject_type' => 'ClientProgressReport', 'subject_id' => $reportId,
            'metadata' => json_encode($meta), 'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }

    private function fields(Request $request): array
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'period_start' => ['required', 'date_format:Y-m-d'],
            'period_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:period_start'],
            'title' => ['required', 'string', 'max:200'],
            'executive_summary' => ['nullable', 'string', 'max:10000'],
            'completed_work' => ['nullable', 'string', 'max:10000'],
            'ongoing_work' => ['nullable', 'string', 'max:10000'],
            'next_week_outlook' => ['nullable', 'string', 'max:10000'],
            'risks_and_actions' => ['nullable', 'string', 'max:10000'],
        ]);
        $start = new \DateTimeImmutable($data['period_start']);
        $end = new \DateTimeImmutable($data['period_end']);
        if ($start->format('N') !== '1' || $end->format('N') !== '7' || $start->diff($end)->days !== 6) {
            throw ValidationException::withMessages(['period_start' => 'Use a complete Monday–Sunday reporting week.']);
        }
        return $data;
    }

    public function index(Request $request)
    {
        $this->permission($request, 'client_control.view');
        $reports = DB::table('client_progress_reports as r')
            ->join('projects as p', 'p.id', '=', 'r.project_id')
            ->select('r.*', 'p.project_name', 'p.project_code')
            ->orderByDesc('r.period_start')->paginate(20);
        return view('client-control.admin.reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $this->permission($request, 'client_control.report_draft');
        $projects = DB::table('projects')->select('id', 'project_code', 'project_name')->orderBy('project_name')->get();
        return view('client-control.admin.reports.form', ['projects' => $projects, 'report' => null, 'actions' => $this->availableActions($request)]);
    }

    public function store(Request $request)
    {
        $this->permission($request, 'client_control.report_draft');
        $data = $this->fields($request);
        $exists = DB::table('client_progress_reports')->where('project_id', $data['project_id'])
            ->where('period_start', $data['period_start'])->where('period_end', $data['period_end'])->exists();
        if ($exists) throw ValidationException::withMessages(['period_start' => 'A report already exists for this project and week.']);
        $id = DB::transaction(function () use ($request, $data) {
            $id = DB::table('client_progress_reports')->insertGetId(array_merge($data, [
                'report_number' => 'CPR-' . $data['project_id'] . '-' . str_replace('-', '', $data['period_start']),
                'status' => 'Draft', 'revision' => 1, 'created_by' => $request->user('web')->id,
                'updated_by' => $request->user('web')->id, 'created_at' => now(), 'updated_at' => now(),
            ]));
            $this->audit($request, 'progress_report.draft_created', $id, (int)$data['project_id']);
            return $id;
        });
        return redirect()->route('client-control.admin.reports.index')->with('success', 'Weekly report ' . 'CPR-' . $data['project_id'] . '-' . str_replace('-', '', $data['period_start']) . ' created as draft. Nothing has been published.');
    }

    public function edit(Request $request, int $report)
    {
        $this->permission($request, 'client_control.view');
        $item = DB::table('client_progress_reports')->where('id', $report)->first();
        abort_unless($item, 404);
        $projects = DB::table('projects')->select('id', 'project_code', 'project_name')->orderBy('project_name')->get();
        $reviews = DB::table('client_progress_report_reviews')->where('report_id', $report)->where('submission_cycle', $item->submission_cycle)->orderBy('id')->get();
        return view('client-control.admin.reports.form', ['projects' => $projects, 'report' => $item, 'reviews' => $reviews, 'actions' => $this->availableActions($request, $item, $reviews)]);
    }

    public function update(Request $request, int $report)
    {
        $this->permission($request, 'client_control.report_draft');
        $data = $this->fields($request);
        $request->validate(['revision' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($request, $report, $data) {
            $current = DB::table('client_progress_reports')->where('id', $report)->lockForUpdate()->first();
            abort_unless($current, 404);
            abort_unless($current->status === 'Draft', 422, 'Only drafts can be edited.');
            abort_unless((int)$current->revision === (int)$request->input('revision'), 409, 'Report changed in another session. Reload before editing.');
            abort_unless((int)$current->project_id === (int)$data['project_id'] && $current->period_start === $data['period_start'] && $current->period_end === $data['period_end'], 422, 'Project and reporting period cannot change. Create a new report.');
            DB::table('client_progress_reports')->where('id', $report)->update(array_merge($data, [
                'revision' => $current->revision + 1, 'updated_by' => $request->user('web')->id, 'updated_at' => now(),
            ]));
            $this->audit($request, 'progress_report.draft_updated', $report, (int)$current->project_id, ['revision' => $current->revision + 1]);
        });
        return redirect()->route('client-control.admin.reports.index')->with('success', 'Weekly report draft updated successfully. Nothing has been published.');
    }

    private const DEPARTMENTS = ['QA/QC' => 'client_control.report_qa_signoff', 'Planning' => 'client_control.report_planning_signoff', 'Document Control' => 'client_control.report_document_signoff'];

    public function submit(Request $request, int $report)
    {
        $this->permission($request, 'client_control.report_submit');
        $request->validate(['revision' => ['required','integer','min:1']]);
        DB::transaction(function () use ($request, $report) {
            $r = DB::table('client_progress_reports')->where('id',$report)->lockForUpdate()->first();
            abort_unless($r,404);
            abort_unless($r->status === 'Draft',422,'Only drafts may be submitted.');
            abort_unless((int)$r->revision === (int)$request->revision,409,'Reload the report before submitting.');
            abort_unless(trim((string)$r->executive_summary) !== '' && trim((string)$r->completed_work) !== '',422,'Executive summary and completed work are required before submission.');
            $cycle = (int)$r->submission_cycle + 1;
            DB::table('client_progress_reports')->where('id',$report)->update(['status'=>'Pending PM Verification','submission_cycle'=>$cycle,'submitted_by'=>$request->user('web')->id,'submitted_at'=>now(),'pm_verified_by'=>null,'pm_verified_at'=>null,'revision'=>$r->revision+1,'updated_at'=>now()]);
            $this->audit($request,'progress_report.submitted',$report,(int)$r->project_id,['cycle'=>$cycle]);
        });
        return back()->with('success','Submitted for PM verification. Not published.');
    }

    public function pmVerify(Request $request, int $report)
    {
        $this->permission($request,'client_control.report_pm_verify');
        $data=$request->validate(['decision'=>['required','in:Approve,Return'],'remarks'=>['nullable','string','max:2000'],'revision'=>['required','integer','min:1']]);
        if ($data['decision']==='Return' && trim((string)($data['remarks']??''))==='') throw ValidationException::withMessages(['remarks'=>'Return remarks are required.']);
        DB::transaction(function () use ($request,$report,$data) {
            $r=DB::table('client_progress_reports')->where('id',$report)->lockForUpdate()->first(); abort_unless($r,404);
            abort_unless($r->status==='Pending PM Verification',422);
            abort_unless((int)$r->revision===(int)$data['revision'],409);
            $approved=$data['decision']==='Approve';
            DB::table('client_progress_report_reviews')->insert(['report_id'=>$report,'submission_cycle'=>$r->submission_cycle,'department'=>'PM','decision'=>$data['decision'],'remarks'=>$data['remarks']??null,'acted_by'=>$request->user('web')->id,'acted_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            DB::table('client_progress_reports')->where('id',$report)->update(['status'=>$approved?'Pending Department Signoffs':'Draft','pm_verified_by'=>$approved?$request->user('web')->id:null,'pm_verified_at'=>$approved?now():null,'revision'=>$r->revision+1,'updated_at'=>now()]);
            $this->audit($request,$approved?'progress_report.pm_verified':'progress_report.pm_returned',$report,(int)$r->project_id,['cycle'=>$r->submission_cycle,'remarks'=>$data['remarks']??null]);
        });
        return back()->with('success','PM review saved. Nothing published.');
    }

    public function review(Request $request,int $report)
    {
        $data=$request->validate(['department'=>['required','in:QA/QC,Planning,Document Control'],'decision'=>['required','in:Approve,Return'],'remarks'=>['nullable','string','max:2000'],'revision'=>['required','integer','min:1']]);
        $this->permission($request,self::DEPARTMENTS[$data['department']]);
        if($data['decision']==='Return' && trim((string)($data['remarks']??''))==='') throw ValidationException::withMessages(['remarks'=>'Return remarks are required.']);
        DB::transaction(function () use ($request,$report,$data) {
            $r=DB::table('client_progress_reports')->where('id',$report)->lockForUpdate()->first(); abort_unless($r,404);
            abort_unless($r->status==='Pending Department Signoffs',422);
            abort_unless((int)$r->revision===(int)$data['revision'],409);
            $already=DB::table('client_progress_report_reviews')->where('report_id',$report)->where('submission_cycle',$r->submission_cycle)->where('department',$data['department'])->exists();
            abort_if($already,422,'This department has already reviewed the current submission.');
            DB::table('client_progress_report_reviews')->insert(['report_id'=>$report,'submission_cycle'=>$r->submission_cycle,'department'=>$data['department'],'decision'=>$data['decision'],'remarks'=>$data['remarks']??null,'acted_by'=>$request->user('web')->id,'acted_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            $status='Pending Department Signoffs';
            if($data['decision']==='Return') $status='Draft';
            else {
                $approved=DB::table('client_progress_report_reviews')->where('report_id',$report)->where('submission_cycle',$r->submission_cycle)->where('decision','Approve')->whereIn('department',array_keys(self::DEPARTMENTS))->distinct()->count('department');
                if($approved===count(self::DEPARTMENTS)) $status='Approved for Publication';
            }
            DB::table('client_progress_reports')->where('id',$report)->update(['status'=>$status,'revision'=>$r->revision+1,'updated_at'=>now()]);
            $this->audit($request,'progress_report.department_reviewed',$report,(int)$r->project_id,['cycle'=>$r->submission_cycle,'department'=>$data['department'],'decision'=>$data['decision'],'remarks'=>$data['remarks']??null]);
        });
        return back()->with('success','Department review saved. Client publication remains disabled.');
    }
}
