<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientControl\ClientProjectAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientProjectController extends Controller
{
    public function index(ClientProjectAccess $access)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client, 401);

        $projectIds = $access->grants($client)->select('project_id')->distinct()->pluck('project_id');

        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->select('id', 'project_code', 'project_name')
            ->orderBy('project_name')
            ->get();

        return view('client-control.projects', compact('projects'));
    }

    public function show(Project $project, ClientProjectAccess $access)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client, 401);
        abort_unless($access->allowed($client, $project->id), 403);

        // Read ONLY the latest published, immutable client-safe snapshot.
        $publishedProgress = DB::table('client_work_progress_snapshots')
            ->where('project_id', $project->id)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderByDesc('revision')
            ->first();
        return view('client-control.home', [
            'project' => $project->only('id', 'project_code', 'project_name'),
            'publishedProgress' => $publishedProgress,
        ]);
    }
}
