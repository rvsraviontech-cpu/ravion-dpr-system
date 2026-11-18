<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientWorkProgressController extends Controller
{
    private function permission(Request $request, string $name): void
    {
        $user = $request->user('web');

        abort_unless($user && DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $user->role_id)
            ->where('permissions.name', $name)
            ->where('permissions.is_active', 1)
            ->exists(), 403);
    }

    private function audit(
        Request $request,
        int $projectId,
        ?int $stageId,
        ?int $taskId,
        string $action,
        ?array $old,
        ?array $new
    ): void {
        DB::table('client_work_progress_history')->insert([
            'project_id' => $projectId,
            'stage_id' => $stageId,
            'task_id' => $taskId,
            'action' => $action,
            'old_values' => $old === null ? null : json_encode($old),
            'new_values' => $new === null ? null : json_encode($new),
            'changed_by' => $request->user('web')->id,
            'created_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $this->permission($request, 'client_control.progress.view');

        $projects = DB::table('projects')
            ->select('id', 'project_code', 'project_name', 'status')
            ->orderBy('project_name')
            ->get();

        return view('client-control.admin.progress.index', compact('projects'));
    }

    public function show(Request $request, int $project)
    {
        $this->permission($request, 'client_control.progress.view');

        $projectRecord = DB::table('projects')->where('id', $project)->first();
        abort_unless($projectRecord, 404);

        $stages = DB::table('client_work_stages')
            ->where('project_id', $project)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $tasks = DB::table('client_work_stage_tasks')
            ->whereIn('stage_id', $stages->pluck('id')->all())
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('stage_id');

        $overall = 0;
        $totalWeight = 0;

        foreach ($stages as $stage) {
            $stageTasks = $tasks->get($stage->id, collect());
            $taskWeight = $stageTasks->sum(fn ($task) => (float) $task->weightage);

            $stage->progress = $taskWeight > 0
                ? $stageTasks->sum(fn ($task) =>
                    (float) $task->weightage * (float) $task->completion_percentage
                ) / $taskWeight
                : 0;

            $overall += (float) $stage->weightage * $stage->progress / 100;
            $totalWeight += (float) $stage->weightage;
        }

        $archivedStages = DB::table('client_work_stages')
            ->where('project_id', $project)
            ->where('status', 'archived')
            ->orderBy('sort_order')->orderBy('id')->get();

        $history = DB::table('client_work_progress_history')
            ->where('project_id', $project)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('client-control.admin.progress.project', compact(
            'projectRecord',
            'stages',
            'tasks',
            'overall',
            'totalWeight',
            'history',
            'archivedStages'
        ));
    }

    public function storeStage(Request $request, int $project)
    {
        $this->permission($request, 'client_control.progress.manage');

        abort_unless(DB::table('projects')->where('id', $project)->exists(), 404);

        $data = $request->validate([
            'stage_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'weightage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($request, $project, $data) {
            // Lock the project to serialize stage-weight changes.
            DB::table('projects')->where('id', $project)->lockForUpdate()->first();

            $existing = DB::table('client_work_stages')
                ->where('project_id', $project)
                ->where('status', 'active');

            $currentWeight = (float) $existing->sum('weightage');

            if ($currentWeight + (float) $data['weightage'] > 100.00001) {
                throw ValidationException::withMessages([
                    'weightage' => 'Total active stage weightage cannot exceed 100%.',
                ]);
            }

            $sortOrder = (int) DB::table('client_work_stages')
                ->where('project_id', $project)
                ->max('sort_order') + 1;

            $id = DB::table('client_work_stages')->insertGetId([
                'project_id' => $project,
                'stage_name' => $data['stage_name'],
                'description' => $data['description'] ?? null,
                'weightage' => $data['weightage'],
                'sort_order' => $sortOrder,
                'status' => 'active',
                'created_by' => $request->user('web')->id,
                'updated_by' => $request->user('web')->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit(
                $request, $project, $id, null,
                'stage.created', null, $data
            );
        });

        return back()->with('success', 'Stage created successfully.');
    }

    public function storeTask(Request $request, int $project, int $stage)
    {
        $this->permission($request, 'client_control.progress.manage');

        $data = $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($request, $project, $stage, $data) {
            $stageRecord = DB::table('client_work_stages')
                ->where('id', $stage)
                ->where('project_id', $project)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            abort_unless($stageRecord, 404);

            $sortOrder = (int) DB::table('client_work_stage_tasks')
                ->where('stage_id', $stage)
                ->max('sort_order') + 1;

            $id = DB::table('client_work_stage_tasks')->insertGetId([
                'stage_id' => $stage,
                'task_name' => $data['task_name'],
                'description' => $data['description'] ?? null,
                'weightage' => 1,
                'completion_percentage' => 0,
                'sort_order' => $sortOrder,
                'status' => 'active',
                'created_by' => $request->user('web')->id,
                'updated_by' => $request->user('web')->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->audit(
                $request, $project, $stage, $id,
                'task.created', null, $data
            );
        });

        return back()->with('success', 'Task added successfully.');
    }

    public function updateTaskProgress(
        Request $request,
        int $project,
        int $stage,
        int $task
    ) {
        $this->permission($request, 'client_control.progress.update');

        $data = $request->validate([
            'completion_percentage' => [
                'required', 'integer', 'between:0,100', 'multiple_of:10'
            ],
        ]);

        DB::transaction(function () use ($request, $project, $stage, $task, $data) {
            $stageRecord = DB::table('client_work_stages')
                ->where('id', $stage)
                ->where('project_id', $project)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            abort_unless($stageRecord, 404);

            $record = DB::table('client_work_stage_tasks')
                ->where('id', $task)
                ->where('stage_id', $stage)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            abort_unless($record, 404);

            $previous = (float) $record->completion_percentage;
            $next = (int) $data['completion_percentage'];

            if (abs($previous - $next) < 0.001) {
                return;
            }

            DB::table('client_work_stage_tasks')
                ->where('id', $task)
                ->update([
                    'completion_percentage' => $next,
                    'actual_completion_date' => $next === 100
                        ? ($record->actual_completion_date ?: today()->toDateString())
                        : null,
                    'updated_by' => $request->user('web')->id,
                    'updated_at' => now(),
                ]);

            $this->audit(
                $request, $project, $stage, $task,
                'task.progress_updated',
                ['completion_percentage' => $previous],
                ['completion_percentage' => $next]
            );
        });

        return back()->with('success', 'Task progress updated.');
    }
    public function updateStage(Request $request, int $project, int $stage)
    {
        $this->permission($request, 'client_control.progress.manage');

        $data = $request->validate([
            'stage_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'weightage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($request, $project, $stage, $data) {
            abort_unless(DB::table('projects')->where('id', $project)->lockForUpdate()->first(), 404);

            $record = DB::table('client_work_stages')
                ->where('id', $stage)->where('project_id', $project)
                ->where('status', 'active')->lockForUpdate()->first();
            abort_unless($record, 404);

            $otherWeight = (float) DB::table('client_work_stages')
                ->where('project_id', $project)->where('status', 'active')
                ->where('id', '!=', $stage)->sum('weightage');

            if ($otherWeight + (float) $data['weightage'] > 100.00001) {
                throw ValidationException::withMessages([
                    'weightage' => 'Total active stage weightage cannot exceed 100%.',
                ]);
            }

            $old = [
                'stage_name' => $record->stage_name,
                'description' => $record->description,
                'weightage' => $record->weightage,
            ];
            $new = [
                'stage_name' => $data['stage_name'],
                'description' => $data['description'] ?? null,
                'weightage' => $data['weightage'],
            ];

            DB::table('client_work_stages')->where('id', $stage)->update($new + [
                'updated_by' => $request->user('web')->id,
                'updated_at' => now(),
            ]);
            $this->audit($request, $project, $stage, null, 'stage.updated', $old, $new);
        });

        return back()->with('success', 'Stage updated successfully.');
    }

    public function archiveStage(Request $request, int $project, int $stage)
    {
        $this->permission($request, 'client_control.progress.manage');
        $request->validate(['confirm_archive' => ['required', 'accepted']]);

        DB::transaction(function () use ($request, $project, $stage) {
            abort_unless(DB::table('projects')->where('id', $project)->lockForUpdate()->first(), 404);
            $record = DB::table('client_work_stages')
                ->where('id', $stage)->where('project_id', $project)
                ->where('status', 'active')->lockForUpdate()->first();
            abort_unless($record, 404);

            DB::table('client_work_stages')->where('id', $stage)->update([
                'status' => 'archived',
                'updated_by' => $request->user('web')->id,
                'updated_at' => now(),
            ]);
            $this->audit($request, $project, $stage, null, 'stage.archived',
                ['status' => 'active', 'weightage' => $record->weightage],
                ['status' => 'archived', 'weightage' => $record->weightage]);
        });

        return back()->with('success', 'Stage archived. Tasks and history have been preserved.');
    }

    public function restoreStage(Request $request, int $project, int $stage)
    {
        $this->permission($request, 'client_control.progress.manage');

        DB::transaction(function () use ($request, $project, $stage) {
            abort_unless(DB::table('projects')->where('id', $project)->lockForUpdate()->first(), 404);
            $record = DB::table('client_work_stages')
                ->where('id', $stage)->where('project_id', $project)
                ->where('status', 'archived')->lockForUpdate()->first();
            abort_unless($record, 404);

            $activeWeight = (float) DB::table('client_work_stages')
                ->where('project_id', $project)->where('status', 'active')->sum('weightage');
            if ($activeWeight + (float) $record->weightage > 100.00001) {
                throw ValidationException::withMessages([
                    'stage' => 'Cannot restore: active stage weightage would exceed 100%. Edit other stage weights first.',
                ]);
            }

            DB::table('client_work_stages')->where('id', $stage)->update([
                'status' => 'active',
                'updated_by' => $request->user('web')->id,
                'updated_at' => now(),
            ]);
            $this->audit($request, $project, $stage, null, 'stage.restored',
                ['status' => 'archived'], ['status' => 'active']);
        });

        return back()->with('success', 'Stage restored successfully.');
    }

}
