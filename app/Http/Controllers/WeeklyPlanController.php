<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklyPlan;
use App\Models\Project;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\User;
use App\Helpers\AuditHelper;

class WeeklyPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = WeeklyPlan::with([
            'project',
            'activity',
            'user'
        ])->latest();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('week_start_date')) {
            $query->whereDate('week_start_date', '>=', $request->week_start_date);
        }

        if ($request->filled('week_end_date')) {
            $query->whereDate('week_end_date', '<=', $request->week_end_date);
        }

        $weeklyPlans = $query->paginate(10);

        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        return view('weekly-plans.index', compact(
            'weeklyPlans',
            'projects'
        ));
    }

    public function create()
    {
        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        $activityDivisions = ActivityDivision::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $activities = Activity::where('is_active', 1)
            ->orderBy('activity_name')
            ->get();

        $engineers = User::whereHas('role', function ($q) {
            $q->where('name', 'Engineer');
        })->orderBy('name')->get();

        return view('weekly-plans.create', compact(
            'projects',
            'activityDivisions',
            'activities',
            'engineers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'activity_id' => 'required|exists:activities,id',
            'user_id' => 'nullable|exists:users,id',
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'planned_labour' => 'nullable|integer|min:0',
            'materials_required' => 'nullable|string',
            'machinery_required' => 'nullable|string',
            'risks_constraints' => 'nullable|string',
            'status' => 'required|string|max:50',
            'remarks' => 'nullable|string',
        ]);

        $weeklyPlan = WeeklyPlan::create([
            'project_id' => $request->project_id,
            'activity_id' => $request->activity_id,
            'user_id' => $request->user_id,
            'week_start_date' => $request->week_start_date,
            'week_end_date' => $request->week_end_date,
            'planned_quantity' => $request->planned_quantity,
            'unit' => $request->unit,
            'planned_labour' => $request->planned_labour ?? 0,
            'materials_required' => $request->materials_required,
            'machinery_required' => $request->machinery_required,
            'risks_constraints' => $request->risks_constraints,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Weekly Plans',
    'Created',
    'WeeklyPlan',
    $weeklyPlan->id,
    'Weekly plan created',
    null,
    $weeklyPlan->only([
        'id',
        'project_id',
        'activity_id',
        'user_id',
        'week_start_date',
        'week_end_date',
        'planned_quantity',
        'unit',
        'planned_labour',
        'status',
        'remarks'
    ])
);

        return redirect()
            ->route('weekly-plans.index')
            ->with('success', 'Weekly plan created successfully.');
    }

    public function show(WeeklyPlan $weeklyPlan)
    {
        $weeklyPlan->load([
            'project',
            'activity',
            'user'
        ]);

        return view('weekly-plans.show', compact('weeklyPlan'));
    }

    public function edit(WeeklyPlan $weeklyPlan)
    {
        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        $activityDivisions = ActivityDivision::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $activities = Activity::where('is_active', 1)
            ->orderBy('activity_name')
            ->get();

        $engineers = User::whereHas('role', function ($q) {
            $q->where('name', 'Engineer');
        })->orderBy('name')->get();

        return view('weekly-plans.edit', compact(
            'weeklyPlan',
            'projects',
            'activityDivisions',
            'activities',
            'engineers'
        ));
    }

    public function update(Request $request, WeeklyPlan $weeklyPlan)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'activity_id' => 'required|exists:activities,id',
            'user_id' => 'nullable|exists:users,id',
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'planned_labour' => 'nullable|integer|min:0',
            'materials_required' => 'nullable|string',
            'machinery_required' => 'nullable|string',
            'risks_constraints' => 'nullable|string',
            'status' => 'required|in:Planned,In Progress,Completed,Delayed',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $weeklyPlan->only([
    'project_id',
    'activity_id',
    'user_id',
    'week_start_date',
    'week_end_date',
    'planned_quantity',
    'unit',
    'planned_labour',
    'materials_required',
    'machinery_required',
    'risks_constraints',
    'status',
    'remarks'
]);

        $weeklyPlan->update([
            'project_id' => $request->project_id,
            'activity_id' => $request->activity_id,
            'user_id' => $request->user_id,
            'week_start_date' => $request->week_start_date,
            'week_end_date' => $request->week_end_date,
            'planned_quantity' => $request->planned_quantity,
            'unit' => $request->unit,
            'planned_labour' => $request->planned_labour ?? 0,
            'materials_required' => $request->materials_required,
            'machinery_required' => $request->machinery_required,
            'risks_constraints' => $request->risks_constraints,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        $newValues = $weeklyPlan->only([
    'project_id',
    'activity_id',
    'user_id',
    'week_start_date',
    'week_end_date',
    'planned_quantity',
    'unit',
    'planned_labour',
    'materials_required',
    'machinery_required',
    'risks_constraints',
    'status',
    'remarks'
]);

$action = 'Updated';
$description = 'Weekly plan updated';

$oldStatus = $oldValues['status'] ?? null;
$newStatus = $newValues['status'] ?? null;

if ($oldStatus !== $newStatus) {
    $action = $newStatus;
    $description =
        'Weekly plan status changed from ' .
        $oldStatus .
        ' to ' .
        $newStatus;
}

AuditHelper::log(
    'Weekly Plans',
    $action,
    'WeeklyPlan',
    $weeklyPlan->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('weekly-plans.index')
            ->with('success', 'Weekly plan updated successfully.');
    }

    public function progressDashboard()
    {
        $weeklyPlans = WeeklyPlan::with([
            'project',
            'activity',
            'user'
        ])->get();

        return view('weekly-plans.progress-dashboard', compact('weeklyPlans'));
    }
}