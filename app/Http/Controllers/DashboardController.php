<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function admin()
    {
        $today = today();

        $totalProjects = \App\Models\Project::count();
        $activeProjects = \App\Models\Project::where('status', 'Active')->count();
        $totalUsers = \App\Models\User::count();
        $totalDprs = \App\Models\Dpr::count();

        $todayDprs = \App\Models\Dpr::whereDate('dpr_date', $today)->count();
        $pendingDprs = \App\Models\Dpr::where('status', 'Pending')->count();
        $approvedDprs = \App\Models\Dpr::where('status', 'Approved')->count();

        $totalAdmins = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->count();
        $totalEngineers = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'Engineer'))->count();
        $totalPmos = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'PMO'))->count();
        $totalCeos = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'CEO'))->count();
        $totalAccountants = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'Accountant'))->count();

        $openSiteIssues = \App\Models\SiteIssue::where('status', 'Open')->count();
        $criticalSiteIssues = \App\Models\SiteIssue::where('priority', 'Critical')
            ->whereIn('status', ['Open', 'In Progress'])
            ->count();

        $managementEscalations = \App\Models\SiteIssue::where('escalated_to_management', 1)
            ->whereIn('status', ['Open', 'In Progress'])
            ->count();

        $pmoEscalations = \App\Models\SiteIssue::where('escalated_to_pmo', 1)
            ->whereIn('status', ['Open', 'In Progress'])
            ->count();

        $tomorrowPlans = \App\Models\TomorrowPlan::whereDate( 'planned_date', \Carbon\Carbon::tomorrow()->toDateString()
)->count();
        $pendingTomorrowPlanApprovals = \App\Models\TomorrowPlan::where('status', 'Submitted')->count();

        $materialShortageItems = \App\Models\MaterialRequirement::whereIn('status', ['Approved', 'approved'])
            ->whereRaw('required_quantity > fulfilled_quantity')
            ->count();

        $recentDprs = \App\Models\Dpr::with('project')
            ->latest()
            ->take(5)
            ->get();

        $recentIssues = \App\Models\SiteIssue::with('project')
            ->latest()
            ->take(5)
            ->get();

        $recentTomorrowPlans = \App\Models\TomorrowPlan::with(['project', 'activity'])
            ->latest()
            ->take(5)
            ->get();

        $delayedProjects = \App\Models\Project::with('dprs')
            ->get()
            ->filter(function ($project) {
                $latestDpr = $project->dprs->sortByDesc('dpr_date')->first();

                if (! $latestDpr) {
                    return false;
                }

                return Carbon::parse($latestDpr->dpr_date)->diffInDays(now()) >= 7;
            })
            ->count();

        $overdueEngineers = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'Engineer'))
            ->with('dprs')
            ->get()
            ->filter(function ($engineer) {
                $latestDpr = $engineer->dprs->sortByDesc('dpr_date')->first();

                if (! $latestDpr) {
                    return true;
                }

                return Carbon::parse($latestDpr->dpr_date)->diffInDays(now()) >= 7;
            })
            ->count();

        return view('dashboards.admin', compact(
            'totalProjects',
            'activeProjects',
            'totalUsers',
            'totalDprs',
            'todayDprs',
            'pendingDprs',
            'approvedDprs',
            'totalAdmins',
            'totalEngineers',
            'totalPmos',
            'totalCeos',
            'totalAccountants',
            'openSiteIssues',
            'criticalSiteIssues',
            'managementEscalations',
            'pmoEscalations',
            'tomorrowPlans',
            'pendingTomorrowPlanApprovals',
            'materialShortageItems',
            'recentDprs',
            'recentIssues',
            'recentTomorrowPlans',
            'delayedProjects',
            'overdueEngineers'
        ));
    }

    public function engineer()
{
    $user = auth()->user();
    $today = today();

    /*
    |--------------------------------------------------------------------------
    | Engineer Assigned Projects
    |--------------------------------------------------------------------------
    */

    $assignedProjects = $user->projects()
        ->orderBy('project_name')
        ->get();

    $projectIds = $assignedProjects->pluck('id');

    /*
    |--------------------------------------------------------------------------
    | Today's DPR
    |--------------------------------------------------------------------------
    */

    $todayDpr = \App\Models\Dpr::with('project')
        ->where('user_id', $user->id)
        ->whereDate('dpr_date', $today)
        ->latest('id')
        ->first();

    $todayDprs = \App\Models\Dpr::where('user_id', $user->id)
        ->whereDate('dpr_date', $today)
        ->count();

    /*
    |--------------------------------------------------------------------------
    | DPR Summary
    |--------------------------------------------------------------------------
    */

    $totalDprs = \App\Models\Dpr::where(
        'user_id',
        $user->id
    )->count();

    $recentDprs = \App\Models\Dpr::with('project')
        ->where('user_id', $user->id)
        ->latest('dpr_date')
        ->take(5)
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Open Site Issues
    |--------------------------------------------------------------------------
    |
    | Restrict the Engineer dashboard to assigned projects.
    |
    */

    $openSiteIssues = \App\Models\SiteIssue::whereIn(
            'project_id',
            $projectIds
        )
        ->whereIn('status', [
            'Open',
            'In Progress',
        ])
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Pending Material Requirements
    |--------------------------------------------------------------------------
    */

    $pendingMaterialRequests =
        \App\Models\MaterialRequirement::whereIn(
            'project_id',
            $projectIds
        )
        ->whereIn('status', [
            'Pending',
            'Submitted',
            'Draft',
        ])
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Today's Labour
    |--------------------------------------------------------------------------
    |
    | Keep the existing proven DPR labour calculation for now.
    |
    */

    $labourToday =
        \App\Models\DprLabour::whereHas(
            'dpr',
            function ($query) use ($user, $today) {
                $query
                    ->where('user_id', $user->id)
                    ->whereDate('dpr_date', $today);
            }
        )
        ->sum('total_count');

    return view(
        'dashboards.engineer',
        compact(
            'assignedProjects',
            'todayDpr',
            'todayDprs',
            'totalDprs',
            'recentDprs',
            'openSiteIssues',
            'pendingMaterialRequests',
            'labourToday'
        )
    );
}

    public function pmo()
    {
        $pendingDprs = \App\Models\Dpr::where('status', 'Pending')->count();
        $approvedDprs = \App\Models\Dpr::where('status', 'Approved')->count();
        $rejectedDprs = \App\Models\Dpr::where('status', 'Rejected')->count();

        return view('dashboards.pmo', compact(
            'pendingDprs',
            'approvedDprs',
            'rejectedDprs'
        ));
    }

    public function ceo()
    {
        $totalProjects = \App\Models\Project::count();
        $totalDprs = \App\Models\Dpr::count();
        $approvedDprs = \App\Models\Dpr::where('status', 'Approved')->count();
        $pendingDprs = \App\Models\Dpr::where('status', 'Pending')->count();

        $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $chartData = [5, 8, 6, 10, 7];

        $engineerStats = \App\Models\Dpr::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return view('dashboards.ceo', compact(
            'totalProjects',
            'totalDprs',
            'approvedDprs',
            'pendingDprs',
            'chartLabels',
            'chartData',
            'engineerStats'
        ));
    }

    public function accountant()
    {
        return view('dashboards.accountant');
    }

    public function engineerProductivity()
    {
        $engineers = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'Engineer'))
            ->with(['dprs.workItems'])
            ->get();

        return view('dashboards.engineer-productivity', compact('engineers'));
    }
}