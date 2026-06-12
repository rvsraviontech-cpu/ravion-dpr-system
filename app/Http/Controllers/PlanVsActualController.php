<?php

namespace App\Http\Controllers;

use App\Models\TomorrowPlan;
use App\Models\Project;
use App\Models\DprWorkItem;
use Illuminate\Http\Request;

class PlanVsActualController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        $query = TomorrowPlan::with([
            'project',
            'block',
            'floor',
            'unit',
            'room',
            'subspace',
            'activity',
        ])->where('status', 'Approved');

        if ($request->filled('from_date')) {
            $query->whereDate('planned_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('planned_date', '<=', $request->to_date);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $plans = $query->orderBy('planned_date')->get();

        $reportRows = [];

        foreach ($plans as $plan) {

            $actualQuery = DprWorkItem::where('activity_id', $plan->activity_id)
                ->where('project_block_id', $plan->project_block_id)
                ->where('project_floor_id', $plan->project_floor_id)
                ->where('project_unit_id', $plan->project_unit_id)
                ->where('project_room_id', $plan->project_room_id)
                ->where('project_subspace_id', $plan->project_subspace_id)
                ->whereHas('dpr', function ($q) use ($plan) {
                    $q->whereDate('dpr_date', $plan->planned_date);
                });

            if ($plan->project_id) {
                $actualQuery->whereHas('dpr', function ($q) use ($plan) {
                    $q->where('project_id', $plan->project_id);
                });
            }

            $actualQty = $actualQuery->sum('quantity_completed');

            $plannedQty = $plan->planned_quantity ?? 0;
            $varianceQty = $actualQty - $plannedQty;

            $achievementPercent = $plannedQty > 0
                ? round(($actualQty / $plannedQty) * 100, 2)
                : 0;

            if ($actualQty == 0) {
                $status = 'Not Started';
            } elseif ($actualQty < $plannedQty) {
                $status = 'Behind';
            } elseif ($actualQty == $plannedQty) {
                $status = 'On Track';
            } else {
                $status = 'Ahead';
            }

            $reportRows[] = [
                'plan' => $plan,
                'planned_qty' => $plannedQty,
                'actual_qty' => $actualQty,
                'variance_qty' => $varianceQty,
                'achievement_percent' => $achievementPercent,
                'status' => $status,
            ];
        }

        return view('plan-vs-actual.index', compact(
            'projects',
            'reportRows'
        ));
    }
}