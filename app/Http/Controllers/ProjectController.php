<?php

namespace App\Http\Controllers;

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
        Project::create([
            'project_code' => $request->project_code,
            'project_name' => $request->project_name,
            'client_name' => $request->client_name,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'target_completion_date' => $request->target_completion_date,
        ]);

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

    $project->users()->sync(
        $request->engineers ?? []
    );

    return redirect('/projects')
        ->with('success', 'Project updated successfully.');
}

public function destroy($id)
{
    $project = Project::findOrFail($id);

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