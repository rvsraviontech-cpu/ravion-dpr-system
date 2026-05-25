<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::all();

        return view('activities.index', compact('activities'));
    }

    public function create()
    {
        return view('activities.create');
    }

    public function store(Request $request)
    {
        Activity::create([
            'activity_name' => $request->activity_name,
            'unit' => $request->unit,
            'work_stage' => $request->work_stage,
            'is_active' => true,
        ]);

        return redirect('/activities')
    ->with('success', 'Activity created successfully.');
    }

    public function edit($id)
{
    $activity = Activity::findOrFail($id);

    return view('activities.edit', compact('activity'));
}

public function update(Request $request, $id)
{
    $activity = Activity::findOrFail($id);

    $activity->update($request->all());

    return redirect('/activities')
        ->with('success', 'Activity updated successfully.');
}

public function destroy($id)
{
    $activity = Activity::findOrFail($id);

    $activity->delete();

    return redirect('/activities')
        ->with('success', 'Activity deleted successfully.');
}
}