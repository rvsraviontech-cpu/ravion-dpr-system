<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkDoneBibleApplicabilityController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        $user = $request->user();
        abort_unless($user, 403);
        $names = [];
        if (method_exists($user, 'roles')) {
            $names = $user->roles()->pluck('name')->all();
        } elseif (method_exists($user, 'role')) {
            $role = $user->role;
            $names = $role ? [(string) ($role->name ?? '')] : [];
        } elseif (is_string($user->role ?? null)) {
            $names = [$user->role];
        }
        abort_unless(collect($names)->contains(fn ($name) => in_array(strtolower(trim((string) $name)), ['admin', 'pmo'], true)), 403, 'Only Admin and PMO can configure Bible applicability.');
    }

    public function index(Request $request)
    {
        $this->authorizeManager($request);
        $projects = DB::table('projects as p')
            ->join('work_done_bible_project_type_assignments as a', 'a.project_id', '=', 'p.id')
            ->join('work_done_bible_project_types as t', 't.id', '=', 'a.bible_project_type_id')
            ->select('p.id', 'p.project_name', 'p.project_code', 't.code as type_code', 't.project_type as type_name', 't.id as bible_project_type_id')
            ->orderBy('p.project_name')->get();
        $projectId = (int) $request->query('project_id', 0);
        $selected = $projects->firstWhere('id', $projectId);
        $search = trim((string) $request->query('search', ''));
        $stageId = (int) $request->query('stage_id', 0);
        $stages = DB::table('work_done_bible_operations')->select('stage_id')->whereNotNull('stage_id')->distinct()->orderBy('stage_id')->pluck('stage_id');
        $operations = null;
        $history = collect();
        if ($selected) {
            $operations = DB::table('work_done_bible_operations as o')
                ->leftJoin('work_done_bible_template_operations as t', function ($join) use ($selected) {
                    $join->on('t.operation_id', '=', 'o.id')->where('t.project_type_id', '=', $selected->bible_project_type_id);
                })
                ->leftJoin('work_done_bible_project_applicabilities as a', function ($join) use ($projectId) {
                    $join->on('a.operation_id', '=', 'o.id')->where('a.project_id', '=', $projectId);
                })
                ->select('o.id', 'o.wd_code', 'o.stage_id', 'o.trade', 'o.element', 'o.micro_operation', 'o.record_type', 'o.uom', 'o.is_active', 'a.applicability', 'a.is_enabled', 'a.remarks', 't.review_status as template_review_status', 't.is_enabled as template_enabled')
                ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                    $w->where('o.wd_code', 'like', '%'.$search.'%')->orWhere('o.micro_operation', 'like', '%'.$search.'%')->orWhere('o.trade', 'like', '%'.$search.'%')->orWhere('o.element', 'like', '%'.$search.'%');
                }))
                ->when($stageId > 0, fn ($q) => $q->where('o.stage_id', $stageId))
                ->orderBy('o.wd_code')->paginate(25)->withQueryString();
            $history = DB::table('work_done_bible_project_applicability_audits as h')
                ->join('work_done_bible_operations as o', 'o.id', '=', 'h.operation_id')
                ->where('h.project_id', $projectId)
                ->select('h.*', 'o.micro_operation as operation_name')->orderByDesc('h.id')->limit(20)->get();
        }
        return view('work-done-bible.applicability', compact('projects', 'selected', 'projectId', 'search', 'stageId', 'stages', 'operations', 'history'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('work_done_bible_project_type_assignments', 'project_id')],
            'operation_id' => ['required', 'integer', Rule::exists('work_done_bible_operations', 'id')->where('is_active', 1)],
            'applicability' => ['required', Rule::in(['Applicable', 'Optional', 'Not Applicable'])],
            'is_enabled' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        if ($data['applicability'] === 'Not Applicable' && (int) $data['is_enabled'] === 1) {
            throw ValidationException::withMessages(['is_enabled' => 'Not Applicable operations cannot be enabled.']);
        }
        $changed = DB::transaction(function () use ($data, $request) {
            // Serialize changes for this project, including first-time inserts.
            DB::table('work_done_bible_project_type_assignments')->where('project_id', $data['project_id'])->lockForUpdate()->first();
            $old = DB::table('work_done_bible_project_applicabilities')->where('project_id', $data['project_id'])->where('operation_id', $data['operation_id'])->first();
            $enabled = (int) $data['is_enabled'];
            if ($old && $old->applicability === $data['applicability'] && (int) $old->is_enabled === $enabled) return false;
            $now = now();
            DB::table('work_done_bible_project_applicabilities')->updateOrInsert(
                ['project_id' => $data['project_id'], 'operation_id' => $data['operation_id']],
                ['applicability' => $data['applicability'], 'is_enabled' => $enabled, 'configured_by' => $request->user()->id, 'configured_at' => $now, 'remarks' => $data['reason'], 'created_at' => $old->created_at ?? $now, 'updated_at' => $now]
            );
            DB::table('work_done_bible_project_applicability_audits')->insert([
                'project_id' => $data['project_id'], 'operation_id' => $data['operation_id'],
                'old_applicability' => $old->applicability ?? null, 'new_applicability' => $data['applicability'],
                'old_is_enabled' => $old ? (int) $old->is_enabled : null, 'new_is_enabled' => $enabled,
                'changed_by' => $request->user()->id, 'changed_at' => $now, 'reason' => $data['reason'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
            return true;
        });
        return redirect()->route('work-done-bible.applicability.index', ['project_id' => $data['project_id'], 'search' => $request->input('search'), 'stage_id' => $request->input('stage_id')])
            ->with('success', $changed ? 'Applicability saved with audit history.' : 'No change to this operation.');
    }
}
