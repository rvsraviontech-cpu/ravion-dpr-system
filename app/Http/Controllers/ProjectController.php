<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    private array $projectStatuses = [
        'Not Started',
        'Active',
        'On Hold',
        'Delayed',
        'Under Snagging',
        'Completed',
        'Handed Over',
        'Closed',
    ];

    private array $projectTypes = [
        'Standalone Residential Building',
        'Independent House',
        'Duplex House',
        'Triplex House',
        'Premium Villa',
        'Luxury Villa',
        'Apartment',
        'Group Housing',
        'Mixed-Use Building',
        'Commercial Building',
        'Office Building',
        'Retail Building',
        'Bank / Institutional Building',
        'Large-Size EPC Project',
    ];

    private array $structureTypes = [
        'Conventional RCC',
        'PT Slab',
        'PT Beam',
        'Flat Slab',
        'Flat Slab with Drop Panel',
        'Beam-Slab Structure',
        'Basement Structure',
        'Retaining Wall Structure',
        'Shear Wall Structure',
        'Steel Structure',
        'Composite Structure',
        'PEB Structure',
    ];

    public function index(Request $request)
    {
        $this->requirePermission('projects.view');

        $projects = Project::with(['assignedPmo', 'users'])
            ->when(! $request->user()->hasAllProjectAccess(), fn ($q) => $q->whereHas('users', fn ($u) => $u->where('users.id', $request->user()->id)))
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('project_code', 'like', '%' . $request->search . '%')
                        ->orWhere('project_name', 'like', '%' . $request->search . '%')
                        ->orWhere('client_name', 'like', '%' . $request->search . '%')
                        ->orWhere('location', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->get();

        $projectStatuses = $this->projectStatuses;

        return view('projects.index', compact('projects', 'projectStatuses'));
    }

    public function create()
    {
        $this->requirePermission('projects.manage');
        return view('projects.create', array_merge(
            $this->formData(),
            [
                'suggestedProjectCode' => $this->generateProjectCode(),
            ]
        ));
    }

    public function store(Request $request)
    {
        $this->requirePermission('projects.manage');
        $validated = $this->validateProject($request);

        $validated['division_code'] = 'RH';

        $project = DB::transaction(function () use ($validated, $request) {
            $project = Project::create($validated);
            $project->users()->sync($request->input('engineers', []));
            return $project;
        });

        AuditHelper::log(
            'Projects',
            'Created',
            'Project',
            $project->id,
            'Project created: ' . $project->project_name,
            null,
            [
                'project' => $this->auditProject($project),
                'engineers' => $project->users()->pluck('users.id')->toArray(),
            ]
        );

        return redirect('/projects')
            ->with('success', 'Project created successfully.');
    }

    public function edit($id)
    {
        $this->requirePermission('projects.manage');
        $this->requireProjectAccess((int) $id);
        $project = Project::with('users')->findOrFail($id);

        return view('projects.edit', array_merge(
            ['project' => $project],
            $this->formData(),
            [
                'suggestedProjectCode' => $project->project_code,
            ]
        ));
    }

    public function update(Request $request, $id)
    {
        $this->requirePermission('projects.manage');
        $this->requireProjectAccess((int) $id);
        $project = Project::with('users')->findOrFail($id);

        $oldValues = [
            'project' => $this->auditProject($project),
            'engineers' => $project->users()->pluck('users.id')->toArray(),
        ];

        $validated = $this->validateProject($request, $project->id);

        $validated['division_code'] = 'RH';

        DB::transaction(function () use ($project, $validated, $request) {
            $project->update($validated);
            $project->users()->sync($request->input('engineers', []));
        });

        $newValues = [
            'project' => $this->auditProject($project->fresh()),
            'engineers' => $project->users()->pluck('users.id')->toArray(),
        ];

        AuditHelper::log(
            'Projects',
            'Updated',
            'Project',
            $project->id,
            'Project updated: ' . $project->project_name,
            $oldValues,
            $newValues
        );

        return redirect('/projects')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $this->requirePermission('projects.manage');
        // Hard deletion is deliberately disabled: existing FK cascades can remove
        // structure and historical operational data.
        abort(403, 'Project deletion is disabled. Use an appropriate project status instead.');
    }

    public function progress()
    {
        $this->requirePermission('projects.view');
        $user = request()->user();
        $projects = Project::with(['users', 'dprs.workItems'])
            ->when(! $user->hasAllProjectAccess(), fn ($q) => $q->whereHas('users', fn ($u) => $u->where('users.id', $user->id)))
            ->get();

        return view('projects.progress', compact('projects'));
    }

    private function requirePermission(string $name): void
    {
        abort_unless(auth()->user()?->hasPermission($name), 403);
    }

    private function requireProjectAccess(int $projectId): void
    {
        abort_unless(auth()->user()?->hasProjectAccess($projectId), 403);
    }

    private function canManageCommercial(Request $request): bool
    {
        return $request->user()->hasPermission('projects.manage')
            && in_array($request->user()->role?->name, ['Admin', 'CEO', 'PMO', 'DGM'], true);
    }

    private function canManageOdoo(Request $request): bool
    {
        return $request->user()->hasPermission('projects.manage')
            && in_array($request->user()->role?->name, ['Admin', 'CEO'], true);
    }

    private function auditProject(Project $project): array
    {
        // Do not place commercial secrets in general-purpose audit payloads.
        return collect($project->toArray())->except(['contract_value', 'odoo_analytic_account_code'])->all();
    }

    private function formData(): array
    {
        $engineers = User::whereHas('role', function ($q) {
            $q->where('name', 'Engineer');
        })->orderBy('name')->get();

        $pmoUsers = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['PMO', 'DGM']);
        })->orderBy('name')->get();

        return [
            'engineers' => $engineers,
            'pmoUsers' => $pmoUsers,
            'projectStatuses' => $this->projectStatuses,
            'projectTypes' => $this->projectTypes,
            'structureTypes' => $this->structureTypes,
        ];
    }

    private function validateProject(Request $request, $projectId = null): array
    {
        // A forged payload must never change commercial data or bypass roles.
        abort_if(! $this->canManageCommercial($request) && $request->exists('contract_value'), 403);
        abort_if(! $this->canManageOdoo($request) && $request->exists('odoo_analytic_account_code'), 403);

        $rules = [
            'project_code' => ['required', 'string', 'max:100', Rule::unique('projects', 'project_code')->ignore($projectId)],
            'project_name' => 'required|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'client_mobile' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:255',
            'client_address' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'google_map_link' => 'nullable|string',
            'project_type' => 'nullable|string|max:150',
            'structure_type' => 'nullable|string|max:150',
            'assigned_pmo_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role_id', DB::table('roles')->whereIn('name', ['PMO', 'DGM'])->select('id')))],
            'start_date' => 'nullable|date',
            'target_completion_date' => 'nullable|date|after_or_equal:start_date',
            'status' => ['required', Rule::in($this->projectStatuses)],
            'remarks' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'engineers' => 'sometimes|array',
            'engineers.*' => ['integer', 'distinct', Rule::exists('users', 'id')->where(fn ($q) => $q->whereIn('role_id', DB::table('roles')->where('name', 'Engineer')->select('id')))],
        ];
        if ($this->canManageCommercial($request)) {
            $rules['contract_value'] = 'nullable|numeric|min:0';
        }
        if ($this->canManageOdoo($request)) {
            $rules['odoo_analytic_account_code'] = 'nullable|string|max:150';
        }
        $validated = $request->validate($rules);
        unset($validated['engineers']);
        return $validated;
    }

    private function generateProjectCode(): string
{
    $year = now()->format('Y');
    $prefix = 'RH-' . $year . '-';

    $lastProject = Project::where('project_code', 'like', $prefix . '%')
        ->orderByDesc('project_code')
        ->first();

    if (!$lastProject) {
        return $prefix . '001';
    }

    $lastNumber = (int) str_replace($prefix, '', $lastProject->project_code);
    $nextNumber = $lastNumber + 1;

    return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
}
public function show($id)
{
    return redirect()->route('projects.edit', $id);
}
}