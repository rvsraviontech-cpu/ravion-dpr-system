<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientControl\ClientProjectAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientQualityPortalController extends Controller
{
    private function authorizeProject(Project $project, ClientProjectAccess $access): void
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $access->allowed($client, $project->id), 403);
    }

    private function published(int $projectId)
    {
        return DB::table('client_quality_test_results as r')
            ->join('client_quality_project_tests as pt', 'pt.id', '=', 'r.project_test_id')
            ->join('client_quality_test_masters as m', 'm.id', '=', 'pt.test_master_id')
            ->where('pt.project_id', $projectId)
            ->where('r.status', 'Approved')
            ->whereNotNull('r.published_at')
            ->whereNull('r.withdrawn_at')
            ->select('r.id', 'r.attempt_no', 'r.tested_on', 'r.result', 'r.observations',
                'r.published_at', 'pt.location_label', 'pt.stage_label',
                'm.code as test_code', 'm.name as test_name', 'm.category');
    }

    public function index(Project $project, ClientProjectAccess $access)
    {
        $this->authorizeProject($project, $access);
        $results = $this->published($project->id)->orderByDesc('r.published_at')->paginate(15);
        return view('client-control.quality.index', compact('project', 'results'));
    }

    public function show(Project $project, int $result, ClientProjectAccess $access)
    {
        $this->authorizeProject($project, $access);
        $report = $this->published($project->id)->where('r.id', $result)->first();
        abort_unless($report, 404);
        $evidence = DB::table('client_quality_result_evidence')
            ->where('result_id', $report->id)
            ->select('id', 'original_name', 'mime_type', 'size_bytes')
            ->orderBy('id')->get();
        return view('client-control.quality.show', compact('project', 'report', 'evidence'));
    }

    public function evidence(Project $project, int $result, int $evidence, ClientProjectAccess $access)
    {
        $this->authorizeProject($project, $access);
        abort_unless($this->published($project->id)->where('r.id', $result)->exists(), 404);
        $file = DB::table('client_quality_result_evidence')
            ->where('result_id', $result)->where('id', $evidence)->first();
        abort_unless($file, 404);
        // QA evidence is stored privately. Never redirect to a public storage URL.
        abort_unless(in_array($file->disk, ['local'], true), 404);
        abort_unless(Storage::disk('local')->exists($file->path), 404);
        abort_unless(in_array($file->mime_type, ['application/pdf','image/jpeg','image/png','image/webp'], true), 404);
        return response()->file(Storage::disk('local')->path($file->path), [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="quality-evidence-'. $file->id .'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }
}
