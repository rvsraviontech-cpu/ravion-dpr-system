<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

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
        $projects = Project::with(['assignedPmo', 'users'])
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
        return view('projects.create', array_merge(
            $this->formData(),
            [
                'suggestedProjectCode' => $this->generateProjectCode(),
            ]
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProject($request);

        $validated['division_code'] = 'RH';

        $project = Project::create($validated);

        $project->users()->sync($request->engineers ?? []);

        AuditHelper::log(
            'Projects',
            'Created',
            'Project',
            $project->id,
            'Project created: ' . $project->project_name,
            null,
            [
                'project' => $project->toArray(),
                'engineers' => $project->users()->pluck('users.id')->toArray(),
            ]
        );

        return redirect('/projects')
            ->with('success', 'Project created successfully.');
    }

    public function edit($id)
    {
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
        $project = Project::with('users')->findOrFail($id);

        $oldValues = [
            'project' => $project->toArray(),
            'engineers' => $project->users()->pluck('users.id')->toArray(),
        ];

        $validated = $this->validateProject($request, $project->id);

        $validated['division_code'] = 'RH';

        $project->update($validated);

        $project->users()->sync($request->engineers ?? []);

        $newValues = [
            'project' => $project->fresh()->toArray(),
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
        $project = Project::findOrFail($id);

        AuditHelper::log(
            'Projects',
            'Deleted',
            'Project',
            $project->id,
            'Project deleted: ' . $project->project_name,
            $project->toArray(),
            null
        );

        $project->delete();

        return redirect('/projects')
            ->with('success', 'Project deleted successfully.');
    }

    public function progress()
    {
        $projects = Project::with(['users', 'dprs.workItems'])->get();

        return view('projects.progress', compact('projects'));
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
        return $request->validate([
            'project_code' => [
                'required',
                'string',
                'max:100',
                'unique:projects,project_code,' . $projectId,
            ],

            'project_name' => 'required|string|max:255',
            'division_code' => 'nullable|string|max:20',

            'client_name' => 'nullable|string|max:255',
            'client_mobile' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:255',
            'client_address' => 'nullable|string',

            'location' => 'nullable|string|max:255',
            'google_map_link' => 'nullable|string',

            'project_type' => 'nullable|string|max:150',
            'structure_type' => 'nullable|string|max:150',

            'contract_value' => 'nullable|numeric|min:0',
            'assigned_pmo_id' => 'nullable|exists:users,id',

            'start_date' => 'nullable|date',
            'target_completion_date' => 'nullable|date|after_or_equal:start_date',

            'status' => 'required|string|max:100',
            'odoo_analytic_account_code' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
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