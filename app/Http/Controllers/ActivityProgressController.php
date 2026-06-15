<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Activity;
use App\Models\MonthlyPlan;
use App\Models\DprWorkItem;

class ActivityProgressController extends Controller
{
    public function index(Project $project)
    {
        $plannedActivities = MonthlyPlan::where('project_id', $project->id)
            ->select('activity_id')
            ->distinct()
            ->pluck('activity_id')
            ->toArray();

        $completedActivities = DprWorkItem::whereHas('dpr', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->select('activity_id')
            ->distinct()
            ->pluck('activity_id')
            ->toArray();

        $activityIds = array_unique(
            array_merge($plannedActivities, $completedActivities)
        );

        $activities = Activity::whereIn('id', $activityIds)->get();

        $activityProgress = [];

        foreach ($activities as $activity) {
            $plannedQty = MonthlyPlan::where('project_id', $project->id)
                ->where('activity_id', $activity->id)
                ->sum('planned_quantity');

            $completedQty = DprWorkItem::where('activity_id', $activity->id)
                ->whereHas('dpr', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->sum('quantity_completed');

            $balanceQty = max($plannedQty - $completedQty, 0);

            $progress = 0;

            if ($plannedQty > 0) {
                $progress = round(($completedQty / $plannedQty) * 100, 2);
            }

            $type = 'Planned';

            if ($plannedQty == 0 && $completedQty > 0) {
                $type = 'Unplanned DPR';
            }

            if ($plannedQty > 0 && $completedQty == 0) {
                $type = 'Planned Not Started';
            }

            if ($plannedQty > 0 && $completedQty > 0) {
                $type = 'In Progress';
            }

            $activityProgress[] = [
                'activity' => $activity,
                'planned' => $plannedQty,
                'completed' => $completedQty,
                'balance' => $balanceQty,
                'progress' => min($progress, 100),
                'actual_progress' => $progress,
                'type' => $type,
            ];
        }

        return view(
            'activity-progress.index',
            compact('project', 'activityProgress')
        );
    }
}