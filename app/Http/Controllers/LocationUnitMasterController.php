<?php

namespace App\Http\Controllers;

use App\Models\LocationUnitMaster;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class LocationUnitMasterController extends Controller
{
    public function index()
    {
        $units = LocationUnitMaster::orderBy('name')->paginate(20);

        return view('location-unit-masters.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_unit_masters,name',
            'type' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $locationUnitMaster = LocationUnitMaster::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Location Unit Masters',
    'Created',
    'LocationUnitMaster',
    $locationUnitMaster->id,
    'Location unit master created: ' . $locationUnitMaster->name,
    null,
    $locationUnitMaster->only([
        'id',
        'name',
        'type',
        'is_active',
        'remarks'
    ])
);

        return redirect()
            ->route('location-unit-masters.index')
            ->with('success', 'Location unit master added successfully.');
    }

    public function edit(LocationUnitMaster $locationUnitMaster)
    {
        return view('location-unit-masters.edit', compact('locationUnitMaster'));
    }

    public function update(Request $request, LocationUnitMaster $locationUnitMaster)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_unit_masters,name,' . $locationUnitMaster->id,
            'type' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $locationUnitMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

        $locationUnitMaster->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        $newValues = $locationUnitMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

$action = 'Updated';
$description = 'Location unit master updated: ' . $locationUnitMaster->name;

if (($oldValues['is_active'] ?? null) != ($newValues['is_active'] ?? null)) {
    $action = $newValues['is_active'] ? 'Activated' : 'Deactivated';

    $description = $newValues['is_active']
        ? 'Location unit master activated: ' . $locationUnitMaster->name
        : 'Location unit master deactivated: ' . $locationUnitMaster->name;
}

AuditHelper::log(
    'Location Unit Masters',
    $action,
    'LocationUnitMaster',
    $locationUnitMaster->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('location-unit-masters.index')
            ->with('success', 'Location unit master updated successfully.');
    }

    public function toggleStatus(LocationUnitMaster $locationUnitMaster)
    {

    $oldValues = $locationUnitMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);
        $locationUnitMaster->update([
            'is_active' => !$locationUnitMaster->is_active,
        ]);

        $newValues = $locationUnitMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

AuditHelper::log(
    'Location Unit Masters',
    $newValues['is_active'] ? 'Activated' : 'Deactivated',
    'LocationUnitMaster',
    $locationUnitMaster->id,
    $newValues['is_active']
        ? 'Location unit master activated: ' . $locationUnitMaster->name
        : 'Location unit master deactivated: ' . $locationUnitMaster->name,
    $oldValues,
    $newValues
);

        return back()->with('success', 'Status updated successfully.');
    }
}