<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientProgressSnapshotController extends Controller
{
    private function authorizePermission(Request $request, string $permission): void
    {
        $user = $request->user('web');
        abort_unless($user && DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $user->role_id)
            ->where('permissions.name', $permission)
            ->where('permissions.is_active', 1)->exists(), 403);
    }

    private function audit(Request $request, int $project, string $action, array $payload): void
    {
        DB::table('client_work_progress_history')->insert([
            'project_id' => $project, 'stage_id' => null, 'task_id' => null,
            'action' => $action, 'old_values' => null,
            'new_values' => json_encode($payload),
            'remarks' => null, 'changed_by' => $request->user('web')->id,
            'created_at' => now(),
        ]);
    }

    public function index(Request $request, int $project)
    {
        $this->authorizePermission($request, 'client_control.progress.view');
        $projectRecord = DB::table('projects')->where('id', $project)->first();
        abort_unless($projectRecord, 404);
        $snapshots = DB::table('client_work_progress_snapshots')
            ->where('project_id', $project)->orderByDesc('revision')->get();
        return view('client-control.admin.progress.snapshots', compact('projectRecord', 'snapshots'));
    }

    public function create(Request $request, int $project)
    {
        $this->authorizePermission($request, 'client_control.progress.update');
        abort_unless(DB::table('projects')->where('id', $project)->exists(), 404);
        DB::transaction(function () use ($request, $project) {
            DB::table('projects')->where('id', $project)->lockForUpdate()->first();
            $stages = DB::table('client_work_stages')->where('project_id', $project)
                ->where('status', 'active')->orderBy('sort_order')->orderBy('id')->get();
            $totalWeight = $stages->sum(fn ($s) => (float) $s->weightage);
            if ($stages->isEmpty() || abs($totalWeight - 100) > 0.001) {
                throw ValidationException::withMessages(['snapshot' => 'Active stage weightages must total 100% before creating a snapshot.']);
            }
            $stageData = [];
            $overall = 0;
            foreach ($stages as $stage) {
                $tasks = DB::table('client_work_stage_tasks')->where('stage_id', $stage->id)
                    ->where('status', 'active')->orderBy('sort_order')->orderBy('id')->get();
                if ($tasks->isEmpty()) {
                    throw ValidationException::withMessages(['snapshot' => 'Every active stage must have at least one active task.']);
                }
                $sum = $tasks->sum(fn ($t) => (float) $t->weightage);
                if ($sum <= 0) {
                    throw ValidationException::withMessages(['snapshot' => 'Every stage must have positive total task weightage.']);
                }
                $progress = $tasks->sum(fn ($t) => (float) $t->weightage * (float) $t->completion_percentage) / $sum;
                $overall += (float) $stage->weightage * $progress / 100;
                $stageData[] = [
                    'name' => $stage->stage_name, 'weightage' => (float) $stage->weightage,
                    'completion_percentage' => round($progress, 2),
                    'tasks' => $tasks->map(fn ($t) => [
                        'name' => $t->task_name, 'weightage' => (float) $t->weightage,
                        'completion_percentage' => (float) $t->completion_percentage,
                    ])->values()->all(),
                ];
            }
            $revision = (int) DB::table('client_work_progress_snapshots')
                ->where('project_id', $project)->max('revision') + 1;
            $id = DB::table('client_work_progress_snapshots')->insertGetId([
                'project_id' => $project, 'revision' => $revision,
                'overall_completion_percentage' => round($overall, 2),
                'stage_snapshot' => json_encode($stageData), 'status' => 'draft',
                'created_by' => $request->user('web')->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->audit($request, $project, 'snapshot.created', ['snapshot_id' => $id, 'revision' => $revision]);
        });
        return back()->with('success', 'A new progress snapshot has been created.');
    }

    public function submit(Request $request, int $project, int $snapshot)
    {
        $this->authorizePermission($request, 'client_control.progress.update');
        $this->transition($request, $project, $snapshot, 'draft', 'submitted', 'snapshot.submitted');
        return back()->with('success', 'Snapshot submitted for review.');
    }

    public function approve(Request $request, int $project, int $snapshot)
    {
        $this->authorizePermission($request, 'client_control.progress.review');
        $this->transition($request, $project, $snapshot, 'submitted', 'approved', 'snapshot.approved', [
            'approved_by' => $request->user('web')->id, 'approved_at' => now(),
        ]);
        return back()->with('success', 'Snapshot approved.');
    }

    public function publish(Request $request, int $project, int $snapshot)
    {
        $this->authorizePermission($request, 'client_control.publish');
        $this->transition($request, $project, $snapshot, 'approved', 'published', 'snapshot.published', [
            'published_by' => $request->user('web')->id, 'published_at' => now(),
        ]);
        return back()->with('success', 'Approved snapshot published to clients.');
    }

    private function transition(Request $request, int $project, int $snapshot, string $from, string $to, string $action, array $extra = []): void
    {
        DB::transaction(function () use ($request, $project, $snapshot, $from, $to, $action, $extra) {
            abort_unless(DB::table('projects')->where('id', $project)->exists(), 404);
            $record = DB::table('client_work_progress_snapshots')->where('project_id', $project)
                ->where('id', $snapshot)->lockForUpdate()->first();
            abort_unless($record, 404);
            if ($record->status !== $from) {
                throw ValidationException::withMessages(['snapshot' => "Only {$from} snapshots can be {$to}."]);
            }
            DB::table('client_work_progress_snapshots')->where('id', $snapshot)
                ->update(array_merge(['status' => $to, 'updated_at' => now()], $extra));
            $this->audit($request, $project, $action, ['snapshot_id' => $snapshot, 'revision' => $record->revision, 'from' => $from, 'to' => $to]);
        });
    }
}
