<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MonthlyPlan;
use App\Models\WeeklyPlan;
use App\Models\Dpr;
use App\Models\DprWorkItem;
use App\Models\LabourReport;
use App\Models\MaterialReceived;
use App\Models\MaterialConsumed;

class ProjectDashboardController extends Controller
{
    public function show(Project $project)
    {
        $monthlyPlanned = MonthlyPlan::where('project_id', $project->id)
            ->sum('planned_quantity');

        $weeklyPlanned = WeeklyPlan::where('project_id', $project->id)
            ->sum('planned_quantity');

        $completedQty = DprWorkItem::whereHas('dpr', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->sum('quantity_completed');

        $targetQty = $monthlyPlanned > 0 ? $monthlyPlanned : $weeklyPlanned;

        $progress = 0;

        if ($targetQty > 0) {
            $progress = round(($completedQty / $targetQty) * 100, 2);
        }

        $balanceQty = max($targetQty - $completedQty, 0);

        $remainingPercent = 0;

        if ($targetQty > 0) {
            $remainingPercent = round(($balanceQty / $targetQty) * 100, 2);
        }

        $labourReportsCount = LabourReport::where('project_id', $project->id)
            ->count();

        $totalLabour = LabourReport::where('project_id', $project->id)
            ->sum('total_labour');

        $materialsReceived = MaterialReceived::where('project_id', $project->id)
            ->count();

        $materialsConsumed = MaterialConsumed::where('project_id', $project->id)
            ->count();

        $dprs = Dpr::where('project_id', $project->id)
            ->latest()
            ->take(10)
            ->get();

        $labourReports = LabourReport::where('project_id', $project->id)
            ->with('contractor')
            ->latest()
            ->take(10)
            ->get();

        return view(
            'project-dashboard.show',
            compact(
                'project',
                'monthlyPlanned',
                'weeklyPlanned',
                'completedQty',
                'targetQty',
                'progress',
                'balanceQty',
                'remainingPercent',
                'labourReportsCount',
                'totalLabour',
                'materialsReceived',
                'materialsConsumed',
                'dprs',
                'labourReports'
            )
        );
    }
}