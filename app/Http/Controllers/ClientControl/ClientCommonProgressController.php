<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientCommonProgressController extends Controller
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

    private function activeStage(int $id): object
    {
        $stage = DB::table('client_common_stages')->where('id', $id)->where('status', 'active')->first();
        abort_unless($stage, 404);
        return $stage;
    }

    public function index(Request $request)
    {
        $this->authorizePermission($request, 'client_control.progress.view');
        $stages = DB::table('client_common_stages')->where('status', 'active')
            ->orderBy('sort_order')->orderBy('id')->get();
        $tasks = DB::table('client_common_stage_tasks')
            ->whereIn('stage_id', $stages->pluck('id')->all())
            ->where('status', 'active')->orderBy('sort_order')->orderBy('id')->get()->groupBy('stage_id');
        return view('client-control.admin.progress.master.index', compact('stages', 'tasks'));
    }

    public function storeStage(Request $request)
    {
        $this->authorizePermission($request, 'client_control.progress.manage');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'suggested_weightage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        DB::transaction(function () use ($data, $request) {
            $order = (int) DB::table('client_common_stages')->max('sort_order') + 1;
            DB::table('client_common_stages')->insert(array_merge($data, [
                'sort_order' => $order, 'status' => 'active',
                'created_by' => $request->user('web')->id, 'updated_by' => $request->user('web')->id,
                'created_at' => now(), 'updated_at' => now(),
            ]));
        });
        return back()->with('success', 'Common stage created.');
    }

    public function updateStage(Request $request, int $stage)
    {
        $this->authorizePermission($request, 'client_control.progress.manage');
        $this->activeStage($stage);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'suggested_weightage' => ['required', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        DB::table('client_common_stages')->where('id', $stage)->update(array_merge($data, [
            'updated_by' => $request->user('web')->id, 'updated_at' => now(),
        ]));
        return back()->with('success', 'Common stage updated.');
    }

    public function archiveStage(Request $request, int $stage)
    {
        $this->authorizePermission($request, 'client_control.progress.manage');
        $this->activeStage($stage);
        DB::table('client_common_stages')->where('id', $stage)->update([
            'status' => 'archived', 'updated_by' => $request->user('web')->id, 'updated_at' => now(),
        ]);
        return back()->with('success', 'Common stage archived. Existing projects are unchanged.');
    }

    public function storeTask(Request $request, int $stage)
    {
        $this->authorizePermission($request, 'client_control.progress.manage');
        $this->activeStage($stage);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'suggested_weightage' => ['required', 'numeric', 'min:0.0001', 'max:10000'],
        ]);
        DB::transaction(function () use ($request, $stage, $data) {
            DB::table('client_common_stages')->where('id', $stage)->lockForUpdate()->first();
            $order = (int) DB::table('client_common_stage_tasks')->where('stage_id', $stage)->max('sort_order') + 1;
            DB::table('client_common_stage_tasks')->insert(array_merge($data, [
                'stage_id' => $stage, 'sort_order' => $order, 'status' => 'active',
                'created_by' => $request->user('web')->id, 'updated_by' => $request->user('web')->id,
                'created_at' => now(), 'updated_at' => now(),
            ]));
        });
        return back()->with('success', 'Common task created.');
    }

    public function updateTask(Request $request, int $stage, int $task)
    {
        $this->authorizePermission($request, 'client_control.progress.manage');
        $this->activeStage($stage);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'suggested_weightage' => ['required', 'numeric', 'min:0.0001', 'max:10000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        $updated = DB::table('client_common_stage_tasks')->where('id', $task)->where('stage_id', $stage)
            ->where('status', 'active')->update(array_merge($data, [
                'updated_by' => $request->user('web')->id, 'updated_at' => now(),
            ]));
        abort_unless($updated, 404);
        return back()->with('success', 'Common task updated.');
    }

    public function archiveTask(Request $request, int $stage, int $task)
    {
        $this->authorizePermission($request, 'client_control.progress.manage');
        $this->activeStage($stage);
        $updated = DB::table('client_common_stage_tasks')->where('id', $task)->where('stage_id', $stage)
            ->where('status', 'active')->update([
                'status' => 'archived', 'updated_by' => $request->user('web')->id, 'updated_at' => now(),
            ]);
        abort_unless($updated, 404);
        return back()->with('success', 'Common task archived. Existing projects are unchanged.');
    }

    public function preview(Request $request, int $project)
    {
        $this->authorizePermission($request, 'client_control.progress.view');
        $projectRecord = DB::table('projects')->where('id', $project)->first();
        abort_unless($projectRecord, 404);
        $stages = DB::table('client_common_stages')->where('status', 'active')
            ->orderBy('sort_order')->orderBy('id')->get();
        $existing = DB::table('client_work_stages')->where('project_id', $project)
            ->where('status', 'active')->get();
        $existingNames = $existing->pluck('stage_name')->map(fn ($v) => mb_strtolower(trim($v)))->all();
        $assignedWeight = (float) $existing->sum('weightage');
        return view('client-control.admin.progress.master.preview', compact(
            'projectRecord', 'stages', 'existingNames', 'assignedWeight'
        ));
    }

    public function import(Request $request, int $project)
    {
        $this->authorizePermission($request, 'client_control.progress.manage');
        $data = $request->validate([
            'stage_ids' => ['required', 'array', 'min:1'],
            'stage_ids.*' => ['required', 'integer', 'distinct', 'exists:client_common_stages,id'],
            'confirm_import' => ['accepted'],
        ]);
        DB::transaction(function () use ($request, $project, $data) {
            $projectRecord = DB::table('projects')->where('id', $project)->lockForUpdate()->first();
            abort_unless($projectRecord, 404);
            $existing = DB::table('client_work_stages')->where('project_id', $project)
                ->where('status', 'active')->get();
            $names = $existing->pluck('stage_name')->map(fn ($v) => mb_strtolower(trim($v)))->all();
            $codes = $existing->pluck('stage_code')->filter()->all();
            $total = (float) $existing->sum('weightage');
            $masters = DB::table('client_common_stages')->whereIn('id', $data['stage_ids'])
                ->where('status', 'active')->orderBy('sort_order')->orderBy('id')->get();
            if ($masters->count() !== count($data['stage_ids'])) {
                throw ValidationException::withMessages(['stage_ids' => 'Some selected stages are no longer active.']);
            }
            foreach ($masters as $master) {
                if (in_array(mb_strtolower(trim($master->name)), $names, true)
                    || in_array('CM-'.$master->id, $codes, true)) {
                    throw ValidationException::withMessages(['stage_ids' => 'Duplicate stage: '.$master->name.'. Select only new stages.']);
                }
                $names[] = mb_strtolower(trim($master->name));
                $codes[] = 'CM-'.$master->id;
                $total += (float) $master->suggested_weightage;
            }
            if ($total > 100.00001) {
                throw ValidationException::withMessages(['stage_ids' => 'Import would exceed 100% active stage weightage. Adjust the master weights or select fewer stages.']);
            }
            $order = (int) DB::table('client_work_stages')->where('project_id', $project)->max('sort_order');
            foreach ($masters as $master) {
                $stageId = DB::table('client_work_stages')->insertGetId([
                    'project_id' => $project, 'stage_code' => 'CM-'.$master->id,
                    'stage_name' => $master->name, 'description' => $master->description,
                    'weightage' => $master->suggested_weightage, 'sort_order' => ++$order,
                    'status' => 'active', 'created_by' => $request->user('web')->id,
                    'updated_by' => $request->user('web')->id, 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('client_work_progress_history')->insert([
                    'project_id' => $project, 'stage_id' => $stageId, 'task_id' => null,
                    'action' => 'stage.imported_from_common', 'old_values' => null,
                    'new_values' => json_encode(['common_stage_id' => $master->id, 'name' => $master->name, 'weightage' => $master->suggested_weightage]),
                    'changed_by' => $request->user('web')->id, 'created_at' => now(),
                ]);
                $tasks = DB::table('client_common_stage_tasks')->where('stage_id', $master->id)
                    ->where('status', 'active')->orderBy('sort_order')->orderBy('id')->get();
                $taskOrder = 0;
                foreach ($tasks as $task) {
                    $taskId = DB::table('client_work_stage_tasks')->insertGetId([
                        'stage_id' => $stageId, 'task_code' => 'CMT-'.$task->id,
                        'task_name' => $task->name, 'description' => $task->description,
                        'weightage' => $task->suggested_weightage, 'completion_percentage' => 0,
                        'sort_order' => ++$taskOrder, 'status' => 'active',
                        'created_by' => $request->user('web')->id, 'updated_by' => $request->user('web')->id,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    DB::table('client_work_progress_history')->insert([
                        'project_id' => $project, 'stage_id' => $stageId, 'task_id' => $taskId,
                        'action' => 'task.imported_from_common', 'old_values' => null,
                        'new_values' => json_encode(['common_task_id' => $task->id, 'name' => $task->name]),
                        'changed_by' => $request->user('web')->id, 'created_at' => now(),
                    ]);
                }
            }
        });
        return redirect()->route('client-control.admin.progress.show', $project)
            ->with('success', 'Selected common stages and tasks imported. Existing project progress was preserved.');
    }
}
