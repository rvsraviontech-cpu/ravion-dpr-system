<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklyPlan;
use App\Models\Project;
use App\Models\Activity;
use App\Models\User;

class WeeklyPlanController extends Controller
{
    public function index()
    {
        $weeklyPlans = WeeklyPlan::with([
            'project',
            'activity',
            'user'
        ])->latest()->get();

        return view(
            'weekly-plans.index',
            compact('weeklyPlans')
        );
    }

    public function create()
    {
        $projects = Project::all();

        $activities = Activity::all();

        $engineers = User::where(
            'role',
            'Engineer'
        )->get();

        return view(
            'weekly-plans.create',
            compact(
                'projects',
                'activities',
                'engineers'
            )
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'project_id' => 'required',

            'week_start_date' => 'required',

            'week_end_date' => 'required'

        ]);

        WeeklyPlan::create($request->all());

        return redirect('/weekly-plans')
            ->with(
                'success',
                'Weekly plan created successfully.'
            );

            
    }

    public function progressDashboard()
{
    $weeklyPlans = WeeklyPlan::with([
        'project',
        'activity',
        'user'
    ])->get();

    return view(
        'weekly-plans.progress-dashboard',
        compact('weeklyPlans')
    );
}
}