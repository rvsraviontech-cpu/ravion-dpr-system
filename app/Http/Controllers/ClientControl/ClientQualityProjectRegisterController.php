<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientQualityProjectRegisterController extends Controller
{
    private function permit(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }

    private function event(int $resultId, string $action, ?string $note = null): void
    {
        DB::table('client_quality_result_events')->insert([
            'result_id' => $resultId, 'action' => $action,
            'actor_id' => auth()->id(), 'note' => $note, 'created_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $this->permit('client_control.quality.view');
        $projects = DB::table('projects')->select('id', 'project_name', 'project_code')->orderBy('project_name')->get();
        $masters = DB::table('client_quality_test_masters')->where('is_active', true)
            ->orderBy('category')->orderBy('name')->get();
        $projectId = $request->integer('project_id');

        $register = DB::table('client_quality_test_results as r')
            ->join('client_quality_project_tests as pt', 'pt.id', '=', 'r.project_test_id')
            ->join('projects as p', 'p.id', '=', 'pt.project_id')
            ->join('client_quality_test_masters as m', 'm.id', '=', 'pt.test_master_id')
            ->select('r.*', 'pt.project_id', 'pt.stage_label', 'pt.location_label',
                'p.project_name', 'm.code as test_code', 'm.name as test_name',
                'm.category', 'm.requires_evidence')
            ->when($projectId, fn($q) => $q->where('pt.project_id', $projectId))
            ->orderByDesc('r.id')->paginate(20)->withQueryString();

        $evidence = DB::table('client_quality_result_evidence')
            ->whereIn('result_id', $register->pluck('id')->all())
            ->orderBy('id')->get()->groupBy('result_id');

        $counts = DB::table('client_quality_test_results as r')
            ->join('client_quality_project_tests as pt', 'pt.id', '=', 'r.project_test_id')
            ->when($projectId, fn($q) => $q->where('pt.project_id', $projectId))
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN r.status IN ('Submitted','Verified') THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN r.status = 'Approved' AND r.published_at IS NOT NULL AND r.withdrawn_at IS NULL THEN 1 ELSE 0 END) as published")
            ->first();

        return view('client-control.admin.quality.register.index',
            compact('projects', 'masters', 'register', 'evidence', 'counts', 'projectId'));
    }

    // One simple PMO submission creates the project/test reference and its first record.
    // The existing schema is retained; no bulk assignment or retest workflow is required.
    public function assign(Request $request)
    {
        $this->permit('client_control.quality.project.manage');
        $this->permit('client_control.quality.results.create');
        $v = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'test_master_id' => ['required', Rule::exists('client_quality_test_masters', 'id')->where('is_active', 1)],
            'tested_on' => ['required', 'date'],
            'result' => ['required', Rule::in(['Passed', 'Failed', 'Inconclusive'])],
            'observations' => ['nullable', 'string', 'max:10000'],
            'location_label' => ['nullable', 'string', 'max:255'],
            'evidence' => ['required', 'array', 'min:1', 'max:8'],
            'evidence.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $stored = [];
        try {
            foreach ($request->file('evidence', []) as $file) {
                $stored[] = [
                    'path' => $file->store('client-quality/evidence', 'local'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                ];
            }
            DB::transaction(function () use ($v, $stored) {
                $pt = DB::table('client_quality_project_tests')->insertGetId([
                    'project_id' => $v['project_id'], 'test_master_id' => $v['test_master_id'],
                    'location_label' => $v['location_label'] ?? null,
                    'created_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $result = DB::table('client_quality_test_results')->insertGetId([
                    'project_test_id' => $pt, 'attempt_no' => 1, 'tested_on' => $v['tested_on'],
                    'result' => $v['result'], 'observations' => $v['observations'] ?? null,
                    'status' => 'Submitted', 'created_by' => auth()->id(),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                foreach ($stored as $file) {
                    DB::table('client_quality_result_evidence')->insert($file + [
                        'result_id' => $result, 'disk' => 'local',
                        'uploaded_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $this->event($result, 'submitted');
            });
        } catch (\Throwable $e) {
            foreach ($stored as $file) Storage::disk('local')->delete($file['path']);
            throw $e;
        }
        return redirect()->route('client-control.admin.quality.register.index',
            ['project_id' => $v['project_id']])->with('success', 'Test submitted for review.');
    }

    // Legacy record endpoint: avoid exposing a second test-attempt workflow in Client Control.
    public function record(Request $request, int $test)
    {
        abort(404);
    }

    public function transition(Request $request, int $result, string $action)
    {
        $permissions = [
            'submit' => 'client_control.quality.results.create',
            'verify' => 'client_control.quality.results.verify',
            'approve' => 'client_control.quality.results.approve',
            'return' => 'client_control.quality.results.verify',
            'publish' => 'client_control.quality.publish',
            'withdraw' => 'client_control.quality.withdraw',
        ];
        abort_unless(isset($permissions[$action]), 404);
        $this->permit($permissions[$action]);
        $v = $request->validate(['note' => 'nullable|string|max:2000']);
        if (in_array($action, ['return', 'withdraw'], true) && blank($v['note'] ?? null)) {
            return back()->withErrors(['note' => 'A reason is required.']);
        }
        DB::transaction(function () use ($result, $action, $v) {
            $r = DB::table('client_quality_test_results')->where('id', $result)->lockForUpdate()->first();
            abort_unless($r, 404);
            $allowed = [
                'submit' => ['Returned'], 'verify' => ['Submitted'], 'approve' => ['Verified'],
                'return' => ['Submitted', 'Verified'], 'publish' => ['Approved'],
                'withdraw' => ['Approved'],
            ];
            abort_unless(in_array($r->status, $allowed[$action], true), 409);
            if ($action === 'publish') {
                abort_unless(!$r->published_at || $r->withdrawn_at, 409);
                abort_unless(DB::table('client_quality_result_evidence')->where('result_id', $result)->exists(), 422);
            }
            if ($action === 'withdraw') abort_unless($r->published_at && !$r->withdrawn_at, 409);
            $changes = ['updated_at' => now()];
            if ($action === 'submit') $changes['status'] = 'Submitted';
            if ($action === 'verify') {
                $changes += ['status' => 'Verified', 'verified_by' => auth()->id(), 'verified_at' => now()];
            }
            if ($action === 'approve') {
                $changes += ['status' => 'Approved', 'approved_by' => auth()->id(), 'approved_at' => now()];
            }
            if ($action === 'return') {
                $changes += ['status' => 'Returned', 'verified_by' => null, 'verified_at' => null,
                    'approved_by' => null, 'approved_at' => null];
            }
            if ($action === 'publish') {
                $changes += ['published_by' => auth()->id(), 'published_at' => now(),
                    'withdrawn_by' => null, 'withdrawn_at' => null, 'withdrawal_reason' => null];
            }
            if ($action === 'withdraw') {
                $changes += ['withdrawn_by' => auth()->id(), 'withdrawn_at' => now(),
                    'withdrawal_reason' => $v['note']];
            }
            DB::table('client_quality_test_results')->where('id', $result)->update($changes);
            $this->event($result, $action, $v['note'] ?? null);
        });
        return back()->with('success', 'Review action completed.');
    }

    public function evidence(int $evidence)
    {
        $this->permit('client_control.quality.view');
        $file = DB::table('client_quality_result_evidence')->where('id', $evidence)->first();
        abort_unless($file && Storage::disk($file->disk)->exists($file->path), 404);
        return response()->file(Storage::disk($file->disk)->path($file->path), [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
