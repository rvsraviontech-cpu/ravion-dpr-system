<?php
namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use App\Models\ClientUser;
use App\Models\ClientProjectGrant;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ClientAdminController extends Controller
{
    private function audit(Request $request, string $action, string $type, int $id, ?int $projectId = null, array $metadata = []): void
    {
        DB::table('client_audit_events')->insert([
            'project_id' => $projectId, 'actor_type' => 'ERP User', 'actor_id' => $request->user('web')->id,
            'action' => $action, 'subject_type' => $type, 'subject_id' => $id,
            'metadata' => json_encode($metadata), 'ip_address' => $request->ip(), 'created_at' => now(),
        ]);
    }

    public function index()
    {
        $clients = ClientUser::with(['grants.project'])->orderBy('name')->paginate(20);
        $projects = Project::query()->select('id','project_code','project_name')->orderBy('project_name')->get();
        $roles = ClientProjectGrant::ROLES;
        return view('client-control.admin.index', compact('clients','projects','roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'], 'email' => ['required','email','max:255'],
            'mobile' => ['nullable','string','max:30'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $email = mb_strtolower(trim($data['email']));
        if (ClientUser::where('email', $email)->exists()) return back()->withErrors(['email'=>'Email already exists.'])->withInput();
        DB::transaction(function () use ($request,$data,$email) {
            $client = ClientUser::create([
                'name'=>$data['name'], 'email'=>$email, 'mobile'=>$data['mobile'] ?? null,
                'password'=>$data['password'], 'status'=>'Pending', 'must_change_password'=>true,
            ]);
            $this->audit($request,'client.created','ClientUser',$client->id);
        });
        return back()->with('success','Client created as Pending. Activate separately.');
    }

    private function requireManageAccess(Request $request): void
    {
        $user = $request->user('web');
        abort_unless($user && DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permission_role.role_id', $user->role_id)
            ->where('permissions.name', 'client_control.manage_access')
            ->where('permissions.is_active', 1)->exists(), 403);
    }

    public function updateDetails(Request $request, ClientUser $clientUser)
    {
        $this->requireManageAccess($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
        ]);
        $email = mb_strtolower(trim($data['email']));
        $data['name'] = trim($data['name']);
        $data['email'] = $email;
        if (ClientUser::where('email', $email)->whereKeyNot($clientUser->id)->exists()) {
            return back()->withErrors(['email' => 'This email is already assigned to another client.'])->withInput();
        }
        DB::transaction(function () use ($request, $clientUser, $data) {
            $before = $clientUser->only(['name', 'email', 'mobile']);
            $clientUser->update([
                'name' => $data['name'], 'email' => $data['email'],
                'mobile' => $data['mobile'] ?? null,
            ]);
            $after = $clientUser->fresh()->only(['name', 'email', 'mobile']);
            $this->audit($request, 'client.details_updated', 'ClientUser', $clientUser->id,
                null, ['before' => $before, 'after' => $after]);
        });
        return back()->with('success', 'Client details updated.');
    }

    public function resetPassword(Request $request, ClientUser $clientUser)
    {
        $this->requireManageAccess($request);
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        DB::transaction(function () use ($request, $clientUser, $data) {
            // Explicit hashing avoids dependence on the model's password cast.
            DB::table('client_users')->where('id', $clientUser->id)->update([
                'password' => Hash::make($data['password']),
                'must_change_password' => true,
                'updated_at' => now(),
            ]);
            $this->audit($request, 'client.password_reset', 'ClientUser', $clientUser->id,
                null, ['must_change_password' => true]);
        });
        return back()->with('success', 'Temporary password set. Client must change it at next login.');
    }

    public function activate(Request $request, ClientUser $clientUser)
    {
        DB::transaction(function () use ($request,$clientUser) {
            $clientUser->update(['status'=>'Active','deactivated_at'=>null]);
            $this->audit($request,'client.activated','ClientUser',$clientUser->id);
        });
        return back()->with('success','Client activated. Project grant still required.');
    }

    public function deactivate(Request $request, ClientUser $clientUser)
    {
        DB::transaction(function () use ($request,$clientUser) {
            $clientUser->update(['status'=>'Inactive','deactivated_at'=>now()]);
            $this->audit($request,'client.deactivated','ClientUser',$clientUser->id);
        });
        return back()->with('success','Client deactivated.');
    }

    public function grant(Request $request, ClientUser $clientUser)
    {
        $data = $request->validate([
            'project_id'=>['required','integer','exists:projects,id'],
            'role'=>['required',Rule::in(ClientProjectGrant::ROLES)],
            'starts_at'=>['nullable','date'], 'expires_at'=>['required','date','after:now', 'after:starts_at'],
        ]);
        DB::transaction(function () use ($request,$clientUser,$data) {
            // New grants require an explicit second approval; do not silently approve.
            $grant = ClientProjectGrant::create([
                'client_user_id'=>$clientUser->id,'project_id'=>$data['project_id'],'role'=>$data['role'],
                'status'=>'Pending','requested_by'=>$request->user('web')->id,'starts_at'=>$data['starts_at'] ?? null,'expires_at'=>$data['expires_at'],
            ]);
            $this->audit($request,'grant.requested','ClientProjectGrant',$grant->id,$grant->project_id,['role'=>$grant->role]);
        });
        return back()->with('success','Grant created as Pending. Approval required.');
    }

    public function approve(Request $request, ClientProjectGrant $grant)
    {
        DB::transaction(function () use ($request,$grant) {
            $grant = ClientProjectGrant::whereKey($grant->id)->lockForUpdate()->firstOrFail();
            $approver = $request->user('web');
            abort_unless($approver, 403);
            // Enforce approval permission independently of route/UI visibility.
            $mayApprove = DB::table('permission_role')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $approver->role_id)
                ->where('permissions.name', 'client_control.grants.approve')
                ->where('permissions.is_active', 1)
                ->exists();
            abort_unless($mayApprove, 403, 'Independent grant approval permission required.');
            // Legacy pending grants without an identified requester cannot be approved.
            abort_unless($grant->requested_by !== null, 422, 'Requester missing; create a new grant.');
            abort_if((int) $grant->requested_by === (int) $approver->id, 403, 'You cannot approve your own grant request.');
            abort_unless($grant->status === 'Pending' && !$grant->revoked_at && $grant->expires_at && $grant->expires_at->isFuture(), 422, 'Grant is not eligible for approval.');
            abort_unless($grant->clientUser->status === 'Active' && !$grant->clientUser->deactivated_at, 422, 'Activate the client first.');
            $overlap = ClientProjectGrant::where('client_user_id',$grant->client_user_id)->where('project_id',$grant->project_id)
                ->where('status','Approved')->whereNull('revoked_at')
                ->where(fn($q)=>$q->whereNull('expires_at')->orWhere('expires_at','>',now()))->exists();
            abort_if($overlap,422,'An active approved grant already exists for this project. Revoke it first.');
            $grant->update(['status'=>'Approved','approved_by'=>$request->user('web')->id,'approved_at'=>now()]);
            $this->audit($request,'grant.approved','ClientProjectGrant',$grant->id,$grant->project_id,['role'=>$grant->role]);
        });
        return back()->with('success','Grant approved.');
    }

    public function revoke(Request $request, ClientProjectGrant $grant)
    {
        $data = $request->validate(['reason'=>['required','string','max:2000']]);
        DB::transaction(function () use ($request,$grant,$data) {
            $grant = ClientProjectGrant::whereKey($grant->id)->lockForUpdate()->firstOrFail();
            abort_if($grant->revoked_at,422,'Already revoked.');
            $grant->update(['status'=>'Revoked','revoked_at'=>now(),'revocation_reason'=>$data['reason']]);
            $this->audit($request,'grant.revoked','ClientProjectGrant',$grant->id,$grant->project_id,['reason'=>$data['reason']]);
        });
        return back()->with('success','Grant revoked.');
    }
}
