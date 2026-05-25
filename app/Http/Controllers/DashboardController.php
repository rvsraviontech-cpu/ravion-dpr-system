<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
{
    $totalProjects = \App\Models\Project::count();

    $totalUsers = \App\Models\User::count();

    $totalDprs = \App\Models\Dpr::count();

    $pendingDprs = \App\Models\Dpr::where('status', 'Pending')->count();

    $recentDprs = \App\Models\Dpr::with('project')
        ->latest()
        ->take(5)
        ->get();

        $totalAdmins = \App\Models\User::whereHas('role', function($q) {
    $q->where('name', 'Admin');
})->count();

$totalEngineers = \App\Models\User::whereHas('role', function($q) {
    $q->where('name', 'Engineer');
})->count();

$totalPmos = \App\Models\User::whereHas('role', function($q) {
    $q->where('name', 'PMO');
})->count();

$totalCeos = \App\Models\User::whereHas('role', function($q) {
    $q->where('name', 'CEO');
})->count();

$totalAccountants = \App\Models\User::whereHas('role', function($q) {
    $q->where('name', 'Accountant');
})->count();

$delayedProjects = \App\Models\Project::with('dprs')
    ->get()
    ->filter(function($project){

        $latestDpr =
            $project->dprs
                ->sortByDesc('dpr_date')
                ->first();

        if(!$latestDpr)
        {
            return false;
        }

        return \Carbon\Carbon::parse(
            $latestDpr->dpr_date
        )->diffInDays(now()) >= 7;

    })->count();

    $overdueEngineers = \App\Models\User::whereHas(
    'role',
    function($q){
        $q->where('name', 'Engineer');
    }
)->with('dprs')
->get()
->filter(function($engineer){

    $latestDpr =
        $engineer->dprs
            ->sortByDesc('dpr_date')
            ->first();

    if(!$latestDpr)
    {
        return true;
    }

    return \Carbon\Carbon::parse(
        $latestDpr->dpr_date
    )->diffInDays(now()) >= 7;

})->count();

$recentActivities = \App\Models\Dpr::with(
    'project',
    'user'
)
->latest()
->take(10)
->get();

    return view('dashboards.admin', compact(
        'totalProjects',
        'totalUsers',
        'totalDprs',
        'pendingDprs',
        'recentDprs',
        'totalAdmins',
        'totalEngineers',
        'totalPmos',
        'totalCeos',
        'totalAccountants',
        'delayedProjects',
        'overdueEngineers',
        'recentActivities'
    ));
}

    public function engineer()
{
    $user = auth()->user();

    $totalDprs = \App\Models\Dpr::where('user_id', $user->id)->count();

    $todayDprs = \App\Models\Dpr::where('user_id', $user->id)
        ->whereDate('dpr_date', today())
        ->count();

    $recentDprs = \App\Models\Dpr::where('user_id', $user->id)
        ->latest()
        ->take(5)
        ->get();

    return view('dashboards.engineer', compact(
        'totalDprs',
        'todayDprs',
        'recentDprs'
    ));
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
    $engineers = \App\Models\User::whereHas(
        'role',
        function($q){
            $q->where('name', 'Engineer');
        }
    )->with([
        'dprs.workItems'
    ])->get();

    return view(
        'dashboards.engineer-productivity',
        compact('engineers')
    );
}
}