<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Release A2: controlled, internal-only publication registry. */
class ClientPublicationController extends Controller
{
    private const ROLES = ['All', 'Owner', 'Technical Viewer', 'Finance Viewer', 'Read-only Viewer'];

    private function permission(Request $request, string $name): void
    {
        $user = $request->user('web');
        abort_unless($user && DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $user->role_id)
            ->where('permissions.name', $name)
            ->where('permissions.is_active', 1)->exists(), 403);
    }

    private function audit(Request $request, string $action, int $id, int $projectId, array $meta = []): void
    {
        DB::table('client_audit_events')->insert([
            'project_id' => $projectId,
            'actor_type' => 'ERP User', 'actor_id' => $request->user('web')->id,
            'action' => $action, 'subject_type' => 'ClientPublication', 'subject_id' => $id,
            'metadata' => json_encode($meta), 'ip_address' => $request->ip(), 'created_at' => now(),
        ]);
    }

    private function version(int $id, string $revision, string $status, Request $request, ?string $reason = null): void
    {
        $last = DB::table('client_publication_versions')->where('client_publication_id', $id)->max('version');
        DB::table('client_publication_versions')->insert([
            'client_publication_id' => $id, 'version' => ((int) $last) + 1,
            'source_revision' => $revision, 'status' => $status, 'reason' => $reason,
            'acted_by' => $request->user('web')->id, 'acted_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $this->permission($request, 'client_control.view');
        $publications = DB::table('client_publications as cp')
            ->join('projects as p', 'p.id', '=', 'cp.project_id')
            ->select('cp.*', 'p.project_name', 'p.project_code')
            ->orderByDesc('cp.id')->paginate(25);
        return view('client-control.admin.publications.index', compact('publications'));
    }

    public function store(Request $request)
    {
        $this->permission($request, 'client_control.publish');
        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'source_module' => ['required', 'string', 'max:100'],
            'source_record_id' => ['required', 'integer', 'min:1'],
            'source_revision' => ['required', 'string', 'max:100'],
            'visibility_role' => ['required', Rule::in(self::ROLES)],
        ]);
        // This registry intentionally does NOT assert source verification. No source is
        // publishable until its adapter and department-specific signoffs are implemented.
        return back()->withErrors(['source_module' => 'Source verification adapter is not configured. No publication was created.']);
    }

    public function approve(Request $request, int $publication)
    {
        $this->permission($request, 'client_control.approve');
        abort(422, 'Approval is disabled until source verification and departmental signoffs are implemented.');
    }

    public function publish(Request $request, int $publication)
    {
        $this->permission($request, 'client_control.publish');
        abort(422, 'Publishing is disabled until source verification and departmental signoffs are implemented.');
    }

    public function withdraw(Request $request, int $publication)
    {
        $this->permission($request, 'client_control.withdraw');
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($request, $publication, $data) {
            $row = DB::table('client_publications')->where('id', $publication)->lockForUpdate()->first();
            abort_unless($row, 404);
            abort_if($row->withdrawal_status === 'Withdrawn', 422, 'Already withdrawn.');
            DB::table('client_publications')->where('id', $publication)->update([
                'withdrawal_status' => 'Withdrawn', 'updated_at' => now(),
            ]);
            $this->version($publication, (string) $row->source_revision, 'Withdrawn', $request, $data['reason']);
            $this->audit($request, 'publication.withdrawn', $publication, (int) $row->project_id, ['reason' => $data['reason']]);
        });
        return back()->with('success', 'Publication withdrawn and audited.');
    }
}
