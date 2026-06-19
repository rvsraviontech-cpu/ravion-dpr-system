<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequirement;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\MaterialCategory;
use App\Models\Material;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class MaterialRequirementController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialRequirement::with([
            'project',
            'block',
            'materialCategory',
            'material',
            'creator',
            'approver',
        ])->latest();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requirements = $query->paginate(10);

        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();

        return view('material-requirements.index', compact(
            'requirements',
            'projects'
        ));
    }

    public function create()
    {
        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();
        $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();
        $materialCategories = MaterialCategory::where('is_active', true)->orderBy('category_name')->get();
        $materials = Material::where('is_active', true)->orderBy('material_name')->get();

        return view('material-requirements.create', compact(
            'projects',
            'projectBlocks',
            'materialCategories',
            'materials'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_block_id' => 'nullable|exists:project_blocks,id',
            'material_category_id' => 'required|exists:material_categories,id',
            'material_id' => 'required|exists:materials,id',
            'required_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'required_date' => 'nullable|date',
            'priority' => 'required|string|max:50',
            'remarks' => 'nullable|string',
        ]);

        $materialRequirement = MaterialRequirement::create([
            'project_id' => $request->project_id,
            'project_block_id' => $request->project_block_id,
            'material_category_id' => $request->material_category_id,
            'material_id' => $request->material_id,
            'required_quantity' => $request->required_quantity,
            'unit' => $request->unit,
            'required_date' => $request->required_date,
            'priority' => $request->priority,
            'status' => 'Draft',
            'remarks' => $request->remarks,
            'created_by' => auth()->id(),
            'fulfilled_quantity' => 0,
        ]);

        AuditHelper::log(
    'Material Requirements',
    'Created',
    'MaterialRequirement',
    $materialRequirement->id,
    'Material requirement created',
    null,
    $materialRequirement->only([
        'id',
        'project_id',
        'project_block_id',
        'material_category_id',
        'material_id',
        'required_quantity',
        'unit',
        'required_date',
        'priority',
        'status',
        'created_by'
    ])
);

        return redirect()
            ->route('material-requirements.index')
            ->with('success', 'Material requirement created successfully.');
    }

    public function show(MaterialRequirement $materialRequirement)
    {
        $materialRequirement->load([
            'project',
            'block',
            'materialCategory',
            'material',
            'creator',
            'approver',
        ]);

        return view('material-requirements.show', compact('materialRequirement'));
    }

    public function edit(MaterialRequirement $materialRequirement)
    {
        if ($materialRequirement->status !== 'Draft') {
            return redirect()
                ->route('material-requirements.index')
                ->with('error', 'Only draft material requirements can be edited.');
        }

        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();
        $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();
        $materialCategories = MaterialCategory::where('is_active', true)->orderBy('category_name')->get();
        $materials = Material::where('is_active', true)->orderBy('material_name')->get();

        return view('material-requirements.edit', compact(
            'materialRequirement',
            'projects',
            'projectBlocks',
            'materialCategories',
            'materials'
        ));
    }

    public function update(Request $request, MaterialRequirement $materialRequirement)
    {
        if ($materialRequirement->status !== 'Draft') {
            return redirect()
                ->route('material-requirements.index')
                ->with('error', 'Only draft material requirements can be updated.');
        }

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'project_block_id' => 'nullable|exists:project_blocks,id',
            'material_category_id' => 'required|exists:material_categories,id',
            'material_id' => 'required|exists:materials,id',
            'required_quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'required_date' => 'nullable|date',
            'priority' => 'required|string|max:50',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $materialRequirement->only([
    'project_id',
    'project_block_id',
    'material_category_id',
    'material_id',
    'required_quantity',
    'unit',
    'required_date',
    'priority',
    'remarks',
    'status'
]);

        $materialRequirement->update([
            'project_id' => $request->project_id,
            'project_block_id' => $request->project_block_id,
            'material_category_id' => $request->material_category_id,
            'material_id' => $request->material_id,
            'required_quantity' => $request->required_quantity,
            'unit' => $request->unit,
            'required_date' => $request->required_date,
            'priority' => $request->priority,
            'remarks' => $request->remarks,
        ]);

        $newValues = $materialRequirement->only([
    'project_id',
    'project_block_id',
    'material_category_id',
    'material_id',
    'required_quantity',
    'unit',
    'required_date',
    'priority',
    'remarks',
    'status'
]);

AuditHelper::log(
    'Material Requirements',
    'Updated',
    'MaterialRequirement',
    $materialRequirement->id,
    'Material requirement updated',
    $oldValues,
    $newValues
);

        return redirect()
            ->route('material-requirements.index')
            ->with('success', 'Material requirement updated successfully.');
    }

    public function submit(MaterialRequirement $materialRequirement)
    {
        if ($materialRequirement->status !== 'Draft') {
            return redirect()
                ->route('material-requirements.index')
                ->with('error', 'Only draft requirements can be submitted.');
        }

        $materialRequirement->update([
            'status' => 'Submitted',
        ]);

        AuditHelper::log(
    'Material Requirements',
    'Submitted',
    'MaterialRequirement',
    $materialRequirement->id,
    'Material requirement submitted for approval',
    ['status' => 'Draft'],
    ['status' => 'Submitted']
);

        return redirect()
            ->route('material-requirements.index')
            ->with('success', 'Material requirement submitted successfully.');
    }

    public function approve(MaterialRequirement $materialRequirement)
    {
        if ($materialRequirement->status !== 'Submitted') {
            return redirect()
                ->route('material-requirements.index')
                ->with('error', 'Only submitted requirements can be approved.');
        }

        if (! in_array(auth()->user()->role->name, ['Admin', 'PMO', 'DGM'])) {
            abort(403);
        }

        $materialRequirement->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditHelper::log(
    'Material Requirements',
    'Approved',
    'MaterialRequirement',
    $materialRequirement->id,
    'Material requirement approved',
    ['status' => 'Submitted'],
    [
        'status' => 'Approved',
        'approved_by' => auth()->id(),
        'approved_at' => now()->toDateTimeString()
    ]
);

        return redirect()
            ->route('material-requirements.index')
            ->with('success', 'Material requirement approved successfully.');
    }
}