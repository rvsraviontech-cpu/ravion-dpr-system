<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlyPlan;
use App\Models\Project;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\User;
use App\Helpers\AuditHelper;

class MonthlyPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = MonthlyPlan::with([
            'project',
            'activity',
            'user'
        ])->latest();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('plan_month')) {
            $query->where('plan_month', $request->plan_month);
        }

        if ($request->filled('plan_year')) {
            $query->where('plan_year', $request->plan_year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $monthlyPlans = $query->paginate(10);

        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        return view('monthly-plans.index', compact(
            'monthlyPlans',
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

        return view('monthly-plans.create', compact(
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
            'plan_month' => 'required|integer|min:1|max:12',
            'plan_year' => 'required|integer|min:2024|max:2100',
            'month_start_date' => 'required|date',
            'month_end_date' => 'required|date|after_or_equal:month_start_date',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'planned_labour' => 'nullable|integer|min:0',
            'materials_required' => 'nullable|string',
            'machinery_required' => 'nullable|string',
            'risks_constraints' => 'nullable|string',
            'status' => 'required|in:Planned,In Progress,Completed,Delayed',
            'remarks' => 'nullable|string',
        ]);

        $monthlyPlan = MonthlyPlan::create([
            'project_id' => $request->project_id,
            'activity_id' => $request->activity_id,
            'user_id' => $request->user_id,
            'plan_month' => $request->plan_month,
            'plan_year' => $request->plan_year,
            'month_start_date' => $request->month_start_date,
            'month_end_date' => $request->month_end_date,
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
    'Monthly Plans',
    'Created',
    'MonthlyPlan',
    $monthlyPlan->id,
    'Monthly plan created',
    null,
    $monthlyPlan->only([
        'id',
        'project_id',
        'activity_id',
        'user_id',
        'plan_month',
        'plan_year',
        'month_start_date',
        'month_end_date',
        'planned_quantity',
        'unit',
        'planned_labour',
        'status',
        'remarks'
    ])
);

        return redirect()
            ->route('monthly-plans.index')
            ->with('success', 'Monthly plan created successfully.');
    }

    public function show(MonthlyPlan $monthlyPlan)
    {
        $monthlyPlan->load([
            'project',
            'activity',
            'user'
        ]);

        return view('monthly-plans.show', compact('monthlyPlan'));
    }

    public function edit(MonthlyPlan $monthlyPlan)
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

        return view('monthly-plans.edit', compact(
            'monthlyPlan',
            'projects',
            'activityDivisions',
            'activities',
            'engineers'
        ));
    }

    public function update(Request $request, MonthlyPlan $monthlyPlan)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'activity_id' => 'required|exists:activities,id',
            'user_id' => 'nullable|exists:users,id',
            'plan_month' => 'required|integer|min:1|max:12',
            'plan_year' => 'required|integer|min:2024|max:2100',
            'month_start_date' => 'required|date',
            'month_end_date' => 'required|date|after_or_equal:month_start_date',
            'planned_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'planned_labour' => 'nullable|integer|min:0',
            'materials_required' => 'nullable|string',
            'machinery_required' => 'nullable|string',
            'risks_constraints' => 'nullable|string',
            'status' => 'required|in:Planned,In Progress,Completed,Delayed',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $monthlyPlan->only([
    'project_id',
    'activity_id',
    'user_id',
    'plan_month',
    'plan_year',
    'month_start_date',
    'month_end_date',
    'planned_quantity',
    'unit',
    'planned_labour',
    'materials_required',
    'machinery_required',
    'risks_constraints',
    'status',
    'remarks'
]);

        $monthlyPlan->update([
            'project_id' => $request->project_id,
            'activity_id' => $request->activity_id,
            'user_id' => $request->user_id,
            'plan_month' => $request->plan_month,
            'plan_year' => $request->plan_year,
            'month_start_date' => $request->month_start_date,
            'month_end_date' => $request->month_end_date,
            'planned_quantity' => $request->planned_quantity,
            'unit' => $request->unit,
            'planned_labour' => $request->planned_labour ?? 0,
            'materials_required' => $request->materials_required,
            'machinery_required' => $request->machinery_required,
            'risks_constraints' => $request->risks_constraints,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        $newValues = $monthlyPlan->only([
    'project_id',
    'activity_id',
    'user_id',
    'plan_month',
    'plan_year',
    'month_start_date',
    'month_end_date',
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
$description = 'Monthly plan updated';

$oldStatus = $oldValues['status'] ?? null;
$newStatus = $newValues['status'] ?? null;

if ($oldStatus !== $newStatus) {
    $action = $newStatus;
    $description =
        'Monthly plan status changed from ' .
        $oldStatus .
        ' to ' .
        $newStatus;
}

AuditHelper::log(
    'Monthly Plans',
    $action,
    'MonthlyPlan',
    $monthlyPlan->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('monthly-plans.index')
            ->with('success', 'Monthly plan updated successfully.');
    }

    public function progressDashboard()
{
    $monthlyPlans = MonthlyPlan::with([
        'project',
        'activity',
        'user'
    ])->get();

    return view(
        'monthly-plans.progress-dashboard',
        compact('monthlyPlans')
    );
}
}