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

class ProjectHealthDashboardController extends Controller
{
    public function index()
    {
        $projects = Project::all();

        $projectHealth = [];

        foreach ($projects as $project) {
            $monthlyPlanned = MonthlyPlan::where('project_id', $project->id)
                ->sum('planned_quantity');

            $weeklyPlanned = WeeklyPlan::where('project_id', $project->id)
                ->sum('planned_quantity');

            $targetQty = $monthlyPlanned > 0 ? $monthlyPlanned : $weeklyPlanned;

            $completedQty = DprWorkItem::whereHas('dpr', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })->sum('quantity_completed');

            $progress = 0;

            if ($targetQty > 0) {
                $progress = round(($completedQty / $targetQty) * 100, 2);
            }

            $approvedDprs = Dpr::where('project_id', $project->id)
                ->where('status', 'Approved')
                ->count();

            $rejectedDprs = Dpr::where('project_id', $project->id)
                ->where('status', 'Rejected')
                ->count();

            $labourReports = LabourReport::where('project_id', $project->id)
                ->count();

            $materialsReceived = MaterialReceived::where('project_id', $project->id)
                ->count();

            $materialsConsumed = MaterialConsumed::where('project_id', $project->id)
                ->count();

            $health = 'Green';
            $remarks = 'Project is healthy';

            if ($progress < 25 || $rejectedDprs > 0) {
                $health = 'Red';
                $remarks = 'Needs immediate attention';
            } elseif ($progress < 75) {
                $health = 'Amber';
                $remarks = 'Monitor closely';
            }

            $projectHealth[] = [
                'project' => $project,
                'target' => $targetQty,
                'completed' => $completedQty,
                'progress' => min($progress, 100),
                'approved_dprs' => $approvedDprs,
                'rejected_dprs' => $rejectedDprs,
                'labour_reports' => $labourReports,
                'materials_received' => $materialsReceived,
                'materials_consumed' => $materialsConsumed,
                'health' => $health,
                'remarks' => $remarks,
            ];
        }

        $summary = [
            'totalProjects' => count($projectHealth),
            'greenProjects' => collect($projectHealth)->where('health', 'Green')->count(),
            'amberProjects' => collect($projectHealth)->where('health', 'Amber')->count(),
            'redProjects' => collect($projectHealth)->where('health', 'Red')->count(),
        ];

        return view(
            'project-health-dashboard.index',
            compact('projectHealth', 'summary')
        );
    }
}