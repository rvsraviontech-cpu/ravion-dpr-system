<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WeeklyPlan;
use App\Models\MonthlyPlan;
use App\Models\Dpr;
use App\Models\DprWorkItem;
use App\Models\MaterialReceived;
use App\Models\MaterialConsumed;
use App\Models\MaterialVerification;
use App\Models\ActivityMapping;

class ProjectProgressDashboardController extends Controller
{
    public function index()
    {
        $projects = Project::all();

        $totalMonthlyPlanned = MonthlyPlan::sum('planned_quantity');
        $totalWeeklyPlanned = WeeklyPlan::sum('planned_quantity');
        $totalCompleted = DprWorkItem::sum('quantity_completed');

        $overallProgress = 0;

        if ($totalMonthlyPlanned > 0) {
            $overallProgress = round(($totalCompleted / $totalMonthlyPlanned) * 100, 2);
        }

        $summary = [
            'projects' => $projects->count(),
            'weeklyPlans' => WeeklyPlan::count(),
            'monthlyPlans' => MonthlyPlan::count(),
            'dprs' => Dpr::count(),
            'activityMappings' => ActivityMapping::count(),
            'materialReceived' => MaterialReceived::count(),
            'materialConsumed' => MaterialConsumed::count(),

            'totalMonthlyPlanned' => $totalMonthlyPlanned,
            'totalWeeklyPlanned' => $totalWeeklyPlanned,
            'totalCompleted' => $totalCompleted,
            'overallProgress' => min($overallProgress, 100),

            'approvedDprs' => Dpr::where('status', 'Approved')->count(),
            'rejectedDprs' => Dpr::where('status', 'Rejected')->count(),
            'verifiedMaterials' => MaterialVerification::where('verification_status', 'Verified')->count(),
            'pendingActivityMapping' => DprWorkItem::whereNull('activity_mapping_id')->count(),
        ];

        $projectProgress = [];

        foreach ($projects as $project) {
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

            $projectProgress[] = [
                'project' => $project,
                'monthly_planned' => $monthlyPlanned,
                'weekly_planned' => $weeklyPlanned,
                'completed' => $completedQty,
                'target' => $targetQty,
                'progress' => min($progress, 100),
                'actual_progress' => $progress,
            ];
        }

        $chartProjects = [];
        $chartProgress = [];

        foreach ($projectProgress as $row) {
            $chartProjects[] = $row['project']->project_name;
            $chartProgress[] = $row['progress'];
        }

        $materialChart = [
            MaterialReceived::count(),
            MaterialConsumed::count(),
        ];

        return view(
            'project-progress-dashboard.index',
            compact(
                'summary',
                'projectProgress',
                'chartProjects',
                'chartProgress',
                'materialChart'
            )
        );
    }
}