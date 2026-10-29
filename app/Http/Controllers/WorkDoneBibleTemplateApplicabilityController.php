<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkDoneBibleTemplateApplicabilityController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        $user = $request->user();
        abort_unless($user, 403);
        $names = [];
        if (method_exists($user, 'roles')) {
            $names = $user->roles()->pluck('name')->all();
        } elseif (is_string($user->role ?? null)) {
            $names = [$user->role];
        } elseif (method_exists($user, 'role')) {
            $role = $user->role;
            $names = $role ? [(string) ($role->name ?? '')] : [];
        }
        abort_unless(collect($names)->contains(fn ($name) => in_array(strtolower(trim((string) $name)), ['admin', 'pmo'], true)), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeManager($request);
        $templates = DB::table('work_done_bible_project_types')->orderBy('code')->get(['id', 'code', 'project_type']);
        $typeId = (int) $request->query('project_type_id', 0);
        $selected = $templates->firstWhere('id', $typeId);
        $search = trim((string) $request->query('search', ''));
        $stageId = (int) $request->query('stage_id', 0);
        $recordType = (string) $request->query('record_type', '');
        if (!in_array($recordType, ['', 'PHYSICAL', 'CONTROL', 'TEST'], true)) $recordType = '';
        $stages = DB::table('work_done_bible_operations')->whereNotNull('stage_id')->distinct()->orderBy('stage_id')->pluck('stage_id');
        $operations = null;
        $history = collect();
        if ($selected) {
            $operations = DB::table('work_done_bible_operations as o')
                ->leftJoin('work_done_bible_template_operations as t', function ($join) use ($typeId) {
                    $join->on('t.operation_id', '=', 'o.id')->where('t.project_type_id', '=', $typeId);
                })
                ->where('o.is_active', 1)
                ->when($stageId > 0, fn ($q) => $q->where('o.stage_id', $stageId))
                ->when($recordType !== '', fn ($q) => $q->where('o.record_type', $recordType))
                ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                    $w->where('o.micro_operation', 'like', '%'.$search.'%')
                        ->orWhere('o.trade', 'like', '%'.$search.'%')
                        ->orWhere('o.element', 'like', '%'.$search.'%');
                }))
                ->select('o.id', 'o.stage_id', 'o.trade', 'o.element', 'o.micro_operation', 'o.record_type', 'o.uom', 't.is_enabled', 't.review_status', 't.review_notes')
                ->orderBy('o.stage_id')->orderBy('o.id')->paginate(25)->withQueryString();
            $history = DB::table('work_done_bible_template_operation_audits as h')
                ->join('work_done_bible_operations as o', 'o.id', '=', 'h.operation_id')
                ->where('h.project_type_id', $typeId)
                ->select('h.*', 'o.micro_operation as operation_name')
                ->orderByDesc('h.id')->limit(20)->get();
        }
        return view('work-done-bible.template-applicability', compact('templates', 'selected', 'typeId', 'search', 'stageId', 'recordType', 'stages', 'operations', 'history'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'project_type_id' => ['required', 'integer', Rule::exists('work_done_bible_project_types', 'id')],
            'operation_ids' => ['required', 'array', 'min:1', 'max:25'],
            'operation_ids.*' => ['required', 'integer', 'distinct', Rule::exists('work_done_bible_operations', 'id')->where('is_active', 1)],
            'decision' => ['required', Rule::in(['Approve', 'Disable'])],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
        $count = DB::transaction(function () use ($request, $data) {
            DB::table('work_done_bible_project_types')->where('id', $data['project_type_id'])->lockForUpdate()->first();
            $changed = 0;
            $now = now();
            $status = 'Approved';
            $enabled = $data['decision'] === 'Approve' ? 1 : 0;
            foreach ($data['operation_ids'] as $operationId) {
                $old = DB::table('work_done_bible_template_operations')
                    ->where('project_type_id', $data['project_type_id'])->where('operation_id', $operationId)->first();
                if ($old && $old->review_status === $status && (int) $old->is_enabled === $enabled) continue;
                DB::table('work_done_bible_template_operations')->updateOrInsert(
                    ['project_type_id' => $data['project_type_id'], 'operation_id' => $operationId],
                    ['review_status' => $status, 'is_enabled' => $enabled, 'reviewed_by' => $request->user()->id,
                     'reviewed_at' => $now, 'review_notes' => $data['reason'],
                     'created_at' => $old->created_at ?? $now, 'updated_at' => $now]
                );
                DB::table('work_done_bible_template_operation_audits')->insert([
                    'project_type_id' => $data['project_type_id'], 'operation_id' => $operationId,
                    'old_review_status' => $old->review_status ?? null, 'new_review_status' => $status,
                    'old_is_enabled' => $old ? (int) $old->is_enabled : null, 'new_is_enabled' => $enabled,
                    'changed_by' => $request->user()->id, 'reason' => $data['reason'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $changed++;
            }
            return $changed;
        });
        return redirect()->route('work-done-bible.templates.index', [
            'project_type_id' => $data['project_type_id'], 'stage_id' => $request->input('stage_id'),
            'record_type' => $request->input('record_type'), 'search' => $request->input('search'),
            'page' => $request->input('page', 1),
        ])->with('success', $count.' template operation(s) updated and audited.');
    }
}
