<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\Contractor;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialConsumed;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ProjectUnit;
use Illuminate\Http\Request;

class MaterialConsumedController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialConsumed::with([
            'project',
            'activityDivision',
            'activity',
            'materialCategory',
            'material',
            'contractor',
            'engineer'
        ]);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('consumed_date')) {
            $query->whereDate('consumed_date', $request->consumed_date);
        }

        $materialConsumeds = $query->latest()->paginate(20);

        $projects = Project::orderBy('project_name')->get();

        $totalConsumedToday = MaterialConsumed::whereDate(
            'consumed_date',
            today()
        )->sum('quantity_consumed');

        $draftCount = MaterialConsumed::where('status', 'Draft')->count();
        $submittedCount = MaterialConsumed::where('status', 'Submitted')->count();
        $approvedCount = MaterialConsumed::where('status', 'Approved')->count();

        return view('material-consumed.index', compact(
            'materialConsumeds',
            'projects',
            'totalConsumedToday',
            'draftCount',
            'submittedCount',
            'approvedCount'
        ));
    }

    public function create()
    {
        $user = auth()->user();

        if (in_array($user->role->name, ['Admin', 'PMO', 'DGM'])) {
            $projects = Project::where('status', 'Active')
                ->orderBy('project_name')
                ->get();
        } else {
            $projects = $user->projects()
                ->where('status', 'Active')
                ->orderBy('project_name')
                ->get();
        }

        $projectBlocks = ProjectBlock::where('is_active', true)->get();
        $projectFloors = ProjectFloor::where('is_active', true)->get();
        $projectUnits = ProjectUnit::where('is_active', true)->get();
        $projectRooms = ProjectRoom::where('is_active', true)->get();
        $projectSubspaces = ProjectSubspace::where('is_active', true)->get();

        $activityDivisions = ActivityDivision::where('is_active', true)
            ->orderBy('name')
            ->get();

        $activities = Activity::where('is_active', true)
            ->orderBy('activity_name')
            ->get();

        $materialCategories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        $materials = Material::where('is_active', true)
            ->orderBy('material_name')
            ->get();

        $contractors = Contractor::where('status', 1)
            ->orderBy('contractor_name')
            ->get();

        return view('material-consumed.create', compact(
            'projects',
            'projectBlocks',
            'projectFloors',
            'projectUnits',
            'projectRooms',
            'projectSubspaces',
            'activityDivisions',
            'activities',
            'materialCategories',
            'materials',
            'contractors'
        ));
    }

    public function store(Request $request)
{
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'material_category_id' => 'required|exists:material_categories,id',
        'material_id' => 'required|exists:materials,id',
        'quantity_consumed' => 'required|numeric|min:0',
        'consumed_date' => 'required|date',
    ]);

    $material = Material::find($request->material_id);

    MaterialConsumed::create([
        'project_id' => $request->project_id,
        'user_id' => auth()->id(),

        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'project_room_id' => $request->project_room_id,
        'project_subspace_id' => $request->project_subspace_id,

        'activity_division_id' => $request->activity_division_id,
        'activity_id' => $request->activity_id,

        'material_category_id' => $request->material_category_id,
        'material_id' => $request->material_id,

        'contractor_id' => $request->contractor_id,

        'quantity_consumed' => $request->quantity_consumed,
        'unit' => $request->unit,

        'related_work_output_quantity' => $request->related_work_output_quantity ?? 0,

        'wastage_quantity' => $request->wastage_quantity ?? 0,
        'wastage_reason' => $request->wastage_reason,

        'consumed_date' => $request->consumed_date,
        'consumed_time' => now()->format('H:i:s'),

        'remarks' => $request->remarks,

        'status' => 'Draft',
    ]);

    return redirect()
        ->route('material-consumed.index')
        ->with('success', 'Material consumption entry added successfully.');
}
public function show(MaterialConsumed $materialConsumed)
{
    $materialConsumed->load([
        'project',
        'engineer',
        'block',
        'floor',
        'unit',
        'room',
        'subspace',
        'activityDivision',
        'activity',
        'materialCategory',
        'material',
        'contractor',
    ]);

    return view(
        'material-consumed.show',
        compact('materialConsumed')
    );
}

public function edit(MaterialConsumed $materialConsumed)
{
    if ($materialConsumed->status !== 'Draft') {
        abort(403, 'Only draft material consumption entries can be edited.');
    }

    $user = auth()->user();

    if (in_array($user->role->name, ['Admin', 'PMO', 'DGM'])) {
        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();
    } else {
        $projects = $user->projects()
            ->where('status', 'Active')
            ->orderBy('project_name')
            ->get();
    }

    $projectBlocks = ProjectBlock::where('is_active', true)->get();
    $projectFloors = ProjectFloor::where('is_active', true)->get();
    $projectUnits = ProjectUnit::where('is_active', true)->get();
    $projectRooms = ProjectRoom::where('is_active', true)->get();
    $projectSubspaces = ProjectSubspace::where('is_active', true)->get();

    $activityDivisions = ActivityDivision::where('is_active', true)
        ->orderBy('name')
        ->get();

    $activities = Activity::where('is_active', true)
        ->orderBy('activity_name')
        ->get();

    $materialCategories = MaterialCategory::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    $materials = Material::where('is_active', true)
        ->orderBy('material_name')
        ->get();

    $contractors = Contractor::where('status', 1)
        ->orderBy('contractor_name')
        ->get();

    return view('material-consumed.edit', compact(
        'materialConsumed',
        'projects',
        'projectBlocks',
        'projectFloors',
        'projectUnits',
        'projectRooms',
        'projectSubspaces',
        'activityDivisions',
        'activities',
        'materialCategories',
        'materials',
        'contractors'
    ));
}

public function update(Request $request, MaterialConsumed $materialConsumed)
{
    if ($materialConsumed->status !== 'Draft') {
        abort(403, 'Only draft material consumption entries can be updated.');
    }

    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'material_category_id' => 'required|exists:material_categories,id',
        'material_id' => 'required|exists:materials,id',
        'quantity_consumed' => 'required|numeric|min:0',
        'consumed_date' => 'required|date',
    ]);

    $materialConsumed->update([
        'project_id' => $request->project_id,

        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,
        'project_room_id' => $request->project_room_id,
        'project_subspace_id' => $request->project_subspace_id,

        'activity_division_id' => $request->activity_division_id,
        'activity_id' => $request->activity_id,

        'material_category_id' => $request->material_category_id,
        'material_id' => $request->material_id,

        'contractor_id' => $request->contractor_id,

        'quantity_consumed' => $request->quantity_consumed,
        'unit' => $request->unit,

        'related_work_output_quantity' => $request->related_work_output_quantity ?? 0,
        'wastage_quantity' => $request->wastage_quantity ?? 0,
        'wastage_reason' => $request->wastage_reason,

        'consumed_date' => $request->consumed_date,
        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('material-consumed.index')
        ->with('success', 'Material consumption entry updated successfully.');
}

public function submit(MaterialConsumed $materialConsumed)
{
    if ($materialConsumed->status !== 'Draft') {
        return back()->with(
            'error',
            'Only draft entries can be submitted.'
        );
    }

    $materialConsumed->update([
        'status' => 'Submitted'
    ]);

    return back()->with(
        'success',
        'Material consumption entry submitted successfully.'
    );
}

public function approve(MaterialConsumed $materialConsumed)
{
    if ($materialConsumed->status !== 'Submitted') {
        return back()->with(
            'error',
            'Only submitted entries can be approved.'
        );
    }

    $materialConsumed->update([
        'status' => 'Approved'
    ]);

    return back()->with(
        'success',
        'Material consumption entry approved successfully.'
    );
}
}