<?php

namespace App\Http\Controllers;

use App\Imports\ActivityMappingsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActivityMappingController extends Controller
{
    public function index(Request $request)
{
    $query = \App\Models\ActivityMapping::with([
        'division',
        'activity'
    ]);

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('rh_cost_code', 'like', "%{$search}%")
              ->orWhere('activity_name', 'like', "%{$search}%")
              ->orWhere('unit', 'like', "%{$search}%")
              ->orWhere('odoo_type', 'like', "%{$search}%");
        });
    }

    if ($request->filled('division_id')) {
        $query->where('activity_division_id', $request->division_id);
    }

    if ($request->filled('status')) {
        $query->where('is_active', $request->status);
    }

    $activityMappings = $query
        ->orderBy('rh_cost_code')
        ->paginate(25)
        ->withQueryString();

    $divisions = \App\Models\ActivityDivision::where('is_active', true)
        ->orderBy('sequence')
        ->get();

    return view('activity-mappings.index', compact(
        'activityMappings',
        'divisions'
    ));
}

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
    ]);

    try {
        $import = new ActivityMappingsImport;

        Excel::import(
            $import,
            $request->file('file')
        );

        return back()->with(
            'success',
            $import->importedCount . ' activity mappings imported successfully.'
        );

    } catch (\Throwable $e) {

        return back()->withErrors([
            'import_error' => $e->getMessage()
        ]);
    }
}

public function edit(\App\Models\ActivityMapping $activityMapping)
{
    $divisions = \App\Models\ActivityDivision::where('is_active', true)
        ->orderBy('sequence')
        ->get();

    $activities = \App\Models\Activity::where('is_active', true)
        ->orderBy('activity_name')
        ->get();

    return view('activity-mappings.edit', compact(
        'activityMapping',
        'divisions',
        'activities'
    ));
}

public function update(
    Request $request,
    \App\Models\ActivityMapping $activityMapping
) {
    $request->validate([
        'activity_division_id' => 'nullable|exists:activity_divisions,id',
        'activity_id' => 'nullable|exists:activities,id',
        'rh_cost_code' => 'required|string|max:255',
        'activity_name' => 'required|string|max:255',
        'unit' => 'nullable|string|max:255',
        'odoo_type_code' => 'nullable|string|max:255',
        'odoo_type' => 'nullable|string|max:255',
        'material_group' => 'nullable|string|max:255',
        'contractor_type' => 'nullable|string|max:255',
        'inventory_expense_bucket' => 'nullable|string|max:255',
        'procurement_mode' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);

    $activityMapping->update($request->only([
        'activity_division_id',
        'activity_id',
        'rh_cost_code',
        'activity_name',
        'unit',
        'odoo_type_code',
        'odoo_type',
        'material_group',
        'contractor_type',
        'inventory_expense_bucket',
        'procurement_mode',
        'is_active',
        'remarks',
    ]));

    return redirect()
        ->route('activity-mappings.index')
        ->with('success', 'Activity mapping updated successfully.');
}
public function create()
{
    $divisions = \App\Models\ActivityDivision::where('is_active', true)
        ->orderBy('sequence')
        ->get();

    $activities = \App\Models\Activity::where('is_active', true)
        ->orderBy('activity_name')
        ->get();

    return view('activity-mappings.create', compact(
        'divisions',
        'activities'
    ));
}

public function store(Request $request)
{
    $request->validate([
        'activity_division_id' => 'nullable|exists:activity_divisions,id',
        'activity_id' => 'nullable|exists:activities,id',
        'rh_cost_code' => 'required|string|max:255|unique:activity_mappings,rh_cost_code',
        'activity_name' => 'required|string|max:255',
        'unit' => 'nullable|string|max:255',
        'odoo_type_code' => 'nullable|string|max:255',
        'odoo_type' => 'nullable|string|max:255',
        'material_group' => 'nullable|string|max:255',
        'contractor_type' => 'nullable|string|max:255',
        'inventory_expense_bucket' => 'nullable|string|max:255',
        'procurement_mode' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);

    $activity = \App\Models\Activity::firstOrCreate(
        [
            'activity_name' => $request->activity_name,
        ],
        [
            'unit' => $request->unit ?: 'Nos',
            'work_stage' => 'General',
            'is_active' => true,
        ]
    );

    \App\Models\ActivityMapping::create([
        'activity_division_id' => $request->activity_division_id,
        'activity_id' => $activity->id,
        'division_code' => 'RH',
        'rh_cost_code' => $request->rh_cost_code,
        'activity_name' => $request->activity_name,
        'unit' => $request->unit ?: 'Nos',
        'odoo_type_code' => $request->odoo_type_code,
        'odoo_type' => $request->odoo_type,
        'material_group' => $request->material_group,
        'contractor_type' => $request->contractor_type,
        'inventory_expense_bucket' => $request->inventory_expense_bucket,
        'procurement_mode' => $request->procurement_mode,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('activity-mappings.index')
        ->with('success', 'Activity mapping created successfully.');
}

}
