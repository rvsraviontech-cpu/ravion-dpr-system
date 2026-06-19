<?php

namespace App\Http\Controllers;
use App\Helpers\AuditHelper;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $project = Project::create([
            'project_code' => $request->project_code,
            'project_name' => $request->project_name,
            'client_name' => $request->client_name,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'target_completion_date' => $request->target_completion_date,
        ]);

        AuditHelper::log(
    'Projects',
    'Created',
    'Project',
    $project->id,
    'Project created: ' . $project->project_name,
    null,
    $project->only([
        'id',
        'project_code',
        'project_name',
        'client_name',
        'location',
        'start_date',
        'target_completion_date'
    ])
);

        return redirect('/projects')
    ->with('success', 'Project created successfully.');
    }

    public function edit($id)
{
    $project = Project::with('users')->findOrFail($id);

    $engineers = \App\Models\User::whereHas('role', function($q) {
        $q->where('name', 'Engineer');
    })->get();

    return view('projects.edit', compact(
        'project',
        'engineers'
    ));
}

public function update(Request $request, $id)
{
    $project = Project::findOrFail($id);

    $project->update([
        'project_code' => $request->project_code,
        'project_name' => $request->project_name,
        'client_name' => $request->client_name,
        'location' => $request->location,
        'start_date' => $request->start_date,
        'target_completion_date' =>
            $request->target_completion_date,
    ]);

    $oldValues = $project->only([
    'project_code',
    'project_name',
    'client_name',
    'location',
    'start_date',
    'target_completion_date'
]);

$oldEngineerIds = $project->users()
    ->pluck('users.id')
    ->toArray();

    $project->users()->sync(
        $request->engineers ?? []
    );

    $newValues = $project->only([
    'project_code',
    'project_name',
    'client_name',
    'location',
    'start_date',
    'target_completion_date'
]);

$newEngineerIds = $project->users()
    ->pluck('users.id')
    ->toArray();

AuditHelper::log(
    'Projects',
    'Updated',
    'Project',
    $project->id,
    'Project updated: ' . $project->project_name,
    [
        'project' => $oldValues,
        'engineers' => $oldEngineerIds
    ],
    [
        'project' => $newValues,
        'engineers' => $newEngineerIds
    ]
);

    return redirect('/projects')
        ->with('success', 'Project updated successfully.');
}

public function destroy($id)
{
    $project = Project::findOrFail($id);
     AuditHelper::log(
    'Projects',
    'Deleted',
    'Project',
    $project->id,
    'Project deleted: ' . $project->project_name,
    $project->only([
        'id',
        'project_code',
        'project_name',
        'client_name',
        'location',
        'start_date',
        'target_completion_date'
    ]),
    null
);
    $project->delete();

    return redirect('/projects')
        ->with('success', 'Project deleted successfully.');
}
public function progress()
{
    $projects = Project::with([
        'users',
        'dprs.workItems'
    ])->get();

    return view(
        'projects.progress',
        compact('projects')
    );
}
}