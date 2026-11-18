<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientControl\ClientProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ClientVaultPortalController extends Controller
{
    private function client(Request $request)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client, 401);
        return $client;
    }

    private function visibilityQuery($query, $client, int $projectId, ClientProjectAccess $access)
    {
        $roles = $access->grants($client, $projectId)->pluck('role')->all();
        $visible = array_values(array_unique(array_merge(['All'], $roles)));
        if (in_array('Owner/Decision Maker', $roles, true)) {
            $visible[] = 'Owner'; // Legacy vault visibility alias.
        }
        return $query->whereIn('d.visibility_role', $visible);
    }

    private function publishedQuery(int $projectId)
    {
        return DB::table('client_vault_documents as d')
            ->join('client_vault_document_versions as v', function ($join) {
                $join->on('v.id', '=', 'd.published_version_id')
                     ->on('v.document_id', '=', 'd.id');
            })
            ->join('client_vault_categories as c', 'c.id', '=', 'd.category_id')
            ->where('d.project_id', $projectId)
            ->where('d.status', 'Published')
            ->whereNull('d.withdrawn_at')
            ->whereNotNull('d.published_version_id')
            ->where('v.status', 'Published')
            ->whereNotNull('v.published_at');
    }

    public function index(Request $request, Project $project, ClientProjectAccess $access)
    {
        $client = $this->client($request);
        $access->assertAllowed($client, (int) $project->id);

        $query = $this->visibilityQuery($this->publishedQuery((int) $project->id), $client, (int) $project->id, $access);
        $documents = $query->select('d.id', 'd.document_number', 'd.title', 'd.description', 'd.visibility_role',
                'c.name as category_name', 'v.revision', 'v.original_filename', 'v.file_size', 'v.mime_type', 'v.published_at')
            ->orderBy('c.sort_order')->orderBy('d.title')->get();

        return view('client-control.vault', [
            'project' => $project->only('id', 'project_code', 'project_name'),
            'documents' => $documents,
        ]);
    }

    /**
     * Resolve only the currently published revision, after checking the client's
     * current project grant and visibility. Never use a user-supplied file path.
     */
    private function authorizedPublishedFile(Request $request, Project $project, int $document, ClientProjectAccess $access)
    {
        $client = $this->client($request);
        $access->assertAllowed($client, (int) $project->id);

        $row = $this->visibilityQuery(
            $this->publishedQuery((int) $project->id),
            $client,
            (int) $project->id,
            $access
        )->where('d.id', $document)
         ->select('d.id', 'v.id as version_id', 'v.disk', 'v.storage_path',
                  'v.original_filename', 'v.mime_type')
         ->first();

        abort_unless($row, 404);
        abort_unless(
            $row->disk === 'local'
            && is_string($row->storage_path)
            && Storage::disk('local')->exists($row->storage_path),
            404
        );

        return [$client, $row];
    }

    private function auditFileAccess(Request $request, Project $project, $client, $row, string $action): void
    {
        DB::table('client_audit_events')->insert([
            'project_id' => $project->id,
            'actor_type' => 'client',
            'actor_id' => $client->id,
            'action' => $action,
            'subject_type' => 'client_vault_document',
            'subject_id' => $row->id,
            'metadata' => json_encode(['version_id' => $row->version_id]),
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }

    public function preview(Request $request, Project $project, int $document, ClientProjectAccess $access)
    {
        [$client, $row] = $this->authorizedPublishedFile($request, $project, $document, $access);

        // Never render office files, HTML, SVG, or other active content inline.
        $inlineTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ];
        abort_unless(in_array(strtolower((string) $row->mime_type), $inlineTypes, true), 415);

        $this->auditFileAccess($request, $project, $client, $row, 'vault.client_preview');

        $safeName = str_replace(["\\r", "\\n", '"', "\\\\"], '_', basename($row->original_filename));
        return response()->file(Storage::disk('local')->path($row->storage_path), [
            'Content-Type' => $row->mime_type,
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function download(Request $request, Project $project, int $document, ClientProjectAccess $access)
    {
        [$client, $row] = $this->authorizedPublishedFile($request, $project, $document, $access);
        $this->auditFileAccess($request, $project, $client, $row, 'vault.client_download');

        return Storage::disk('local')->download($row->storage_path, $row->original_filename, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
