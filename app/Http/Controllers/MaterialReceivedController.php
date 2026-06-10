<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\MaterialReceived;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\MaterialCategory;

class MaterialReceivedController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialReceived::with([
            'project',
            'engineer',
            'block',
            'floor',
            'unit',
            'contractor',
        ]);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('received_date')) {
            $query->whereDate('received_date', $request->received_date);
        }

        $materialReceiveds = $query->latest()->paginate(20)->withQueryString();

        $projects = Project::orderBy('project_name')->get();

        $totalReceivedToday = MaterialReceived::whereDate('received_date', today())
            ->sum('quantity_received');

        $draftCount = MaterialReceived::where('status', 'Draft')->count();
        $submittedCount = MaterialReceived::where('status', 'Submitted')->count();
        $approvedCount = MaterialReceived::where('status', 'Approved')->count();

        return view('material-received.index', compact(
            'materialReceiveds',
            'projects',
            'totalReceivedToday',
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

        $projectBlocks = ProjectBlock::where('is_active', true)
            ->orderBy('name')
            ->get();

        $projectFloors = ProjectFloor::where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        $projectUnits = ProjectUnit::where('is_active', true)
            ->orderBy('name')
            ->get();

        $contractors = Contractor::where('status', 1)
            ->orderBy('contractor_name')
            ->get();

        $materialCategories = MaterialCategory::where('is_active', true)
            ->orderBy('category_name')
            ->get();

        $materials = Material::where('is_active', true)
             ->orderBy('material_name')
             ->get();

        return view('material-received.create', compact(
    'projects',
    'projectBlocks',
    'projectFloors',
    'projectUnits',
    'contractors',
    'materialCategories',
    'materials'
));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'material_id' => 'required|exists:materials,id',
            'material_category_id' => 'required|exists:material_categories,id',
            'quantity_received' => 'required|numeric|min:0',
            'received_date' => 'required|date',
        ]);

        MaterialReceived::create([
            'project_id' => $request->project_id,
            'user_id' => auth()->id(),

            'project_block_id' => $request->project_block_id,
            'project_floor_id' => $request->project_floor_id,
            'project_unit_id' => $request->project_unit_id,

            'material_category_id' => $request->material_category_id,
            'material_id' => $request->material_id,

            'storage_location' => $request->storage_location,
            'material_category' => $request->material_category,
            'material_name' => optional(\App\Models\Material::find($request->material_id))->material_name,
            'specification' => $request->specification,
            'brand' => $request->brand,

            'quantity_received' => $request->quantity_received ?? 0,
            'unit' => $request->unit,

            'vendor_name' => $request->vendor_name,
            'supplied_by_contractor' => $request->has('supplied_by_contractor'),
            'contractor_id' => $request->contractor_id,

            'vehicle_number' => $request->vehicle_number,
            'driver_name' => $request->driver_name,
            'challan_number' => $request->challan_number,
            'bill_number' => $request->bill_number,

            'received_date' => $request->received_date,
            'received_time' => now()->format('H:i:s'),

            'material_condition' => $request->material_condition ?? 'Pending verification',

            'accepted_quantity' => $request->accepted_quantity ?? 0,
            'short_quantity' => $request->short_quantity ?? 0,
            'damaged_quantity' => $request->damaged_quantity ?? 0,
            'rejected_quantity' => $request->rejected_quantity ?? 0,

            'remarks' => $request->remarks,
            'status' => 'Draft',
        ]);

        return redirect()
            ->route('material-received.index')
            ->with('success', 'Material received entry added successfully.');
    }

    public function submit(MaterialReceived $materialReceived)
    {
        if ($materialReceived->status !== 'Draft') {
            return back()->with('error', 'Only draft material entries can be submitted.');
        }

        $materialReceived->update([
            'status' => 'Submitted',
        ]);

        return redirect()
            ->route('material-received.index')
            ->with('success', 'Material received entry submitted successfully.');
    }

    public function approve(MaterialReceived $materialReceived)
    {
        if ($materialReceived->status !== 'Submitted') {
            return back()->with('error', 'Only submitted material entries can be approved.');
        }

        $materialReceived->update([
            'status' => 'Approved',
            'pmo_verification_status' => 'Approved',
        ]);

        return redirect()
            ->route('material-received.index')
            ->with('success', 'Material received entry approved successfully.');
    }

    public function show(MaterialReceived $materialReceived)
{
    $materialReceived->load([
        'project',
        'engineer',
        'block',
        'floor',
        'unit',
        'contractor',
        'materialCategory',
        'material',
    ]);

    return view('material-received.show', compact('materialReceived'));
}

public function edit(MaterialReceived $materialReceived)
{
    if ($materialReceived->status !== 'Draft') {
        abort(403, 'Only draft material received entries can be edited.');
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

    $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();
    $projectFloors = ProjectFloor::where('is_active', true)->orderBy('sequence')->orderBy('name')->get();
    $projectUnits = ProjectUnit::where('is_active', true)->orderBy('name')->get();

    $contractors = Contractor::where('status', 1)
        ->orderBy('contractor_name')
        ->get();

    $materialCategories = \App\Models\MaterialCategory::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    $materials = \App\Models\Material::where('is_active', true)
        ->orderBy('material_name')
        ->get();

    return view('material-received.edit', compact(
        'materialReceived',
        'projects',
        'projectBlocks',
        'projectFloors',
        'projectUnits',
        'contractors',
        'materialCategories',
        'materials'
    ));
}

public function update(Request $request, MaterialReceived $materialReceived)
{
    if ($materialReceived->status !== 'Draft') {
        abort(403, 'Only draft material received entries can be updated.');
    }

    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'material_category_id' => 'required|exists:material_categories,id',
        'material_id' => 'required|exists:materials,id',
        'quantity_received' => 'required|numeric|min:0',
        'received_date' => 'required|date',
    ]);

    $material = \App\Models\Material::find($request->material_id);

    $materialReceived->update([
        'project_id' => $request->project_id,

        'project_block_id' => $request->project_block_id,
        'project_floor_id' => $request->project_floor_id,
        'project_unit_id' => $request->project_unit_id,

        'storage_location' => $request->storage_location,

        'material_category_id' => $request->material_category_id,
        'material_id' => $request->material_id,

        'material_category' => optional($material?->category)->category_name,
        'material_name' => $material?->material_name,
        'specification' => $material?->specification,
        'brand' => $material?->brand,

        'quantity_received' => $request->quantity_received ?? 0,
        'unit' => $request->unit,

        'vendor_name' => $request->vendor_name,
        'supplied_by_contractor' => $request->has('supplied_by_contractor'),
        'contractor_id' => $request->contractor_id,

        'vehicle_number' => $request->vehicle_number,
        'driver_name' => $request->driver_name,
        'challan_number' => $request->challan_number,
        'bill_number' => $request->bill_number,

        'received_date' => $request->received_date,

        'material_condition' => $request->material_condition ?? 'Pending verification',

        'accepted_quantity' => $request->accepted_quantity ?? 0,
        'short_quantity' => $request->short_quantity ?? 0,
        'damaged_quantity' => $request->damaged_quantity ?? 0,
        'rejected_quantity' => $request->rejected_quantity ?? 0,

        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('material-received.index')
        ->with('success', 'Material received entry updated successfully.');
}
}