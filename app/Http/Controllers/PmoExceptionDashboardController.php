<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Dpr;
use App\Models\DprWorkItem;
use App\Models\MonthlyPlan;

class PmoExceptionDashboardController extends Controller
{
    public function index()
    {
        $rejectedDprs = Dpr::where('status', 'Rejected')
            ->with('project', 'user')
            ->latest()
            ->get();

        $pendingActivityMappings = DprWorkItem::whereNull('activity_mapping_id')
            ->with('dpr.project', 'activity')
            ->get();

        $unplannedActivities = [];

        $plannedButNotStarted = [];

        $projects = Project::all();

        foreach ($projects as $project) {
            $plannedActivityIds = MonthlyPlan::where('project_id', $project->id)
                ->pluck('activity_id')
                ->toArray();

            $completedActivityIds = DprWorkItem::whereHas('dpr', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->pluck('activity_id')
                ->toArray();

            $unplannedIds = array_diff(
                array_unique($completedActivityIds),
                array_unique($plannedActivityIds)
            );

            foreach ($unplannedIds as $activityId) {
                $completedQty = DprWorkItem::where('activity_id', $activityId)
                    ->whereHas('dpr', function ($query) use ($project) {
                        $query->where('project_id', $project->id);
                    })
                    ->sum('quantity_completed');

                $workItem = DprWorkItem::where('activity_id', $activityId)
                    ->with('activity')
                    ->first();

                $unplannedActivities[] = [
                    'project' => $project,
                    'activity' => optional($workItem)->activity,
                    'completed_qty' => $completedQty,
                ];
            }

            $notStartedIds = array_diff(
                array_unique($plannedActivityIds),
                array_unique($completedActivityIds)
            );

            foreach ($notStartedIds as $activityId) {
                $plannedQty = MonthlyPlan::where('project_id', $project->id)
                    ->where('activity_id', $activityId)
                    ->sum('planned_quantity');

                $plan = MonthlyPlan::where('project_id', $project->id)
                    ->where('activity_id', $activityId)
                    ->with('activity')
                    ->first();

                $plannedButNotStarted[] = [
                    'project' => $project,
                    'activity' => optional($plan)->activity,
                    'planned_qty' => $plannedQty,
                ];
            }
        }

        $summary = [
            'rejectedDprs' => $rejectedDprs->count(),
            'pendingActivityMappings' => $pendingActivityMappings->count(),
            'unplannedActivities' => count($unplannedActivities),
            'plannedButNotStarted' => count($plannedButNotStarted),
        ];

        return view(
            'pmo-exception-dashboard.index',
            compact(
                'summary',
                'rejectedDprs',
                'pendingActivityMappings',
                'unplannedActivities',
                'plannedButNotStarted'
            )
        );
    }
}