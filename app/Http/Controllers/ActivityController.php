<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Helpers\AuditHelper;

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
        $activity = Activity::create([
            'activity_name' => $request->activity_name,
            'unit' => $request->unit,
            'work_stage' => $request->work_stage,
            'is_active' => true,
        ]);

        AuditHelper::log(
    'Activities',
    'Created',
    'Activity',
    $activity->id,
    'Activity created: ' . $activity->activity_name,
    null,
    $activity->only([
        'id',
        'activity_name',
        'unit',
        'work_stage',
        'is_active'
    ])
);

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

    $oldValues = $activity->only([
    'activity_name',
    'unit',
    'work_stage',
    'is_active'
]);

$activity->update($request->only([
    'activity_name',
    'unit',
    'work_stage',
    'is_active'
]));

$newValues = $activity->only([
    'activity_name',
    'unit',
    'work_stage',
    'is_active'
]);

AuditHelper::log(
    'Activities',
    'Updated',
    'Activity',
    $activity->id,
    'Activity updated: ' . $activity->activity_name,
    $oldValues,
    $newValues
);

    return redirect('/activities')
        ->with('success', 'Activity updated successfully.');
}

public function destroy($id)
{
    $activity = Activity::findOrFail($id);

    AuditHelper::log(
    'Activities',
    'Deleted',
    'Activity',
    $activity->id,
    'Activity deleted: ' . $activity->activity_name,
    $activity->only([
        'id',
        'activity_name',
        'unit',
        'work_stage',
        'is_active'
    ]),
    null
);

    $activity->delete();

    return redirect('/activities')
        ->with('success', 'Activity deleted successfully.');
}
}