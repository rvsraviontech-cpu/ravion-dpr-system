<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WorkDoneBibleProjectTypeController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        // Fail closed: only users with an explicit Admin or PMO role can enter.
        // Supports both common role relationship shapes without granting access
        // to users whose role cannot be determined.
        $names = [];
        if (method_exists($user, 'roles')) {
            $names = $user->roles()->pluck('name')->all();
        } elseif (method_exists($user, 'role')) {
            $role = $user->role;
            $names = $role ? [(string) ($role->name ?? '')] : [];
        } elseif (is_string($user->role ?? null)) {
            $names = [$user->role];
        }
        $allowed = collect($names)->contains(fn ($name) => in_array(strtolower(trim((string) $name)), ['admin', 'pmo'], true));
        abort_unless($allowed, 403, 'Only Admin and PMO can manage Bible project types.');
    }

    public function index(Request $request)
    {
        $this->authorizeManager($request);
        $search = trim((string) $request->query('search', ''));
        $projects = DB::table('projects as p')
            ->leftJoin('work_done_bible_project_type_assignments as a', 'a.project_id', '=', 'p.id')
            ->leftJoin('work_done_bible_project_types as t', 't.id', '=', 'a.bible_project_type_id')
            ->select('p.id', 'p.project_name', 'p.project_code', 'p.project_type', 'a.bible_project_type_id', 'a.assigned_at', 't.code as bible_code', 't.project_type as bible_name')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('p.project_name', 'like', '%'.$search.'%')->orWhere('p.project_code', 'like', '%'.$search.'%')))
            ->orderBy('p.project_name')->paginate(20)->withQueryString();
        $types = DB::table('work_done_bible_project_types')->where('is_active', true)->orderBy('code')->get();
        $history = DB::table('work_done_bible_project_type_assignment_audits as h')
            ->join('projects as p', 'p.id', '=', 'h.project_id')
            ->leftJoin('work_done_bible_project_types as old', 'old.id', '=', 'h.old_bible_project_type_id')
            ->join('work_done_bible_project_types as new', 'new.id', '=', 'h.new_bible_project_type_id')
            ->select('p.project_name', 'old.code as old_code', 'new.code as new_code', 'h.changed_at', 'h.reason', 'h.changed_by')
            ->orderByDesc('h.id')->limit(30)->get();
        return view('work-done-bible.project-types', compact('projects', 'types', 'history', 'search'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'bible_project_type_id' => ['required', 'integer', Rule::exists('work_done_bible_project_types', 'id')->where('is_active', 1)],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);
        $changed = DB::transaction(function () use ($data, $request) {
            $old = DB::table('work_done_bible_project_type_assignments')->where('project_id', $data['project_id'])->lockForUpdate()->first();
            if ($old && (int) $old->bible_project_type_id === (int) $data['bible_project_type_id']) return false;
            if ($old && trim((string) ($data['reason'] ?? '')) === '') {
                throw \Illuminate\Validation\ValidationException::withMessages(['reason' => 'A reason is mandatory when changing an existing assignment.']);
            }
            $now = now();
            DB::table('work_done_bible_project_type_assignments')->updateOrInsert(
                ['project_id' => $data['project_id']],
                ['bible_project_type_id' => $data['bible_project_type_id'], 'assigned_by' => $request->user()->id, 'assigned_at' => $now, 'remarks' => $data['reason'] ?? null, 'updated_at' => $now, 'created_at' => $old->created_at ?? $now]
            );
            DB::table('work_done_bible_project_type_assignment_audits')->insert([
                'project_id' => $data['project_id'], 'old_bible_project_type_id' => $old->bible_project_type_id ?? null,
                'new_bible_project_type_id' => $data['bible_project_type_id'], 'changed_by' => $request->user()->id,
                'changed_at' => $now, 'reason' => $data['reason'] ?? null, 'created_at' => $now, 'updated_at' => $now,
            ]);
            return true;
        });
        return redirect()->route('work-done-bible.project-types.index')->with('success', $changed ? 'Bible project type assigned and audited.' : 'No change: this type is already assigned.');
    }
}
