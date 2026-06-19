<?php

namespace App\Http\Controllers;

use App\Models\LocationFloorMaster;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class LocationFloorMasterController extends Controller
{
    public function index()
    {
        $floors = LocationFloorMaster::orderBy('sequence')
            ->orderBy('name')
            ->paginate(20);

        return view('location-floor-masters.index', compact('floors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_floor_masters,name',
            'sequence' => 'nullable|integer',
            'remarks' => 'nullable|string',
        ]);

        $locationFloorMaster = LocationFloorMaster::create([
            'name' => $request->name,
            'sequence' => $request->sequence ?? 0,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Location Floor Masters',
    'Created',
    'LocationFloorMaster',
    $locationFloorMaster->id,
    'Location floor master created: ' . $locationFloorMaster->name,
    null,
    $locationFloorMaster->only([
        'id',
        'name',
        'sequence',
        'is_active',
        'remarks'
    ])
);

        return redirect()
            ->route('location-floor-masters.index')
            ->with('success', 'Location floor master added successfully.');
    }

    public function edit(LocationFloorMaster $locationFloorMaster)
    {
        return view('location-floor-masters.edit', compact('locationFloorMaster'));
    }

    public function update(Request $request, LocationFloorMaster $locationFloorMaster)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_floor_masters,name,' . $locationFloorMaster->id,
            'sequence' => 'nullable|integer',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $locationFloorMaster->only([
    'name',
    'sequence',
    'is_active',
    'remarks'
]);

        $locationFloorMaster->update([
            'name' => $request->name,
            'sequence' => $request->sequence ?? 0,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        $newValues = $locationFloorMaster->only([
    'name',
    'sequence',
    'is_active',
    'remarks'
]);

$action = 'Updated';
$description = 'Location floor master updated: ' . $locationFloorMaster->name;

if (($oldValues['is_active'] ?? null) != ($newValues['is_active'] ?? null)) {
    $action = $newValues['is_active'] ? 'Activated' : 'Deactivated';

    $description = $newValues['is_active']
        ? 'Location floor master activated: ' . $locationFloorMaster->name
        : 'Location floor master deactivated: ' . $locationFloorMaster->name;
}

AuditHelper::log(
    'Location Floor Masters',
    $action,
    'LocationFloorMaster',
    $locationFloorMaster->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('location-floor-masters.index')
            ->with('success', 'Location floor master updated successfully.');
    }

    public function toggleStatus(LocationFloorMaster $locationFloorMaster)
    {
        $oldValues = $locationFloorMaster->only([
    'name',
    'sequence',
    'is_active',
    'remarks'
]);
        $locationFloorMaster->update([
            'is_active' => !$locationFloorMaster->is_active,
        ]);

        $newValues = $locationFloorMaster->only([
    'name',
    'sequence',
    'is_active',
    'remarks'
]);

AuditHelper::log(
    'Location Floor Masters',
    $newValues['is_active'] ? 'Activated' : 'Deactivated',
    'LocationFloorMaster',
    $locationFloorMaster->id,
    $newValues['is_active']
        ? 'Location floor master activated: ' . $locationFloorMaster->name
        : 'Location floor master deactivated: ' . $locationFloorMaster->name,
    $oldValues,
    $newValues
);

        return back()->with('success', 'Status updated successfully.');
    }
}