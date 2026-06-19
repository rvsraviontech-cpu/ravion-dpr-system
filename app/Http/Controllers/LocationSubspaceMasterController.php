<?php

namespace App\Http\Controllers;

use App\Models\LocationSubspaceMaster;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class LocationSubspaceMasterController extends Controller
{
    public function index()
    {
        $subspaces = LocationSubspaceMaster::orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        return view('location-subspace-masters.index', compact('subspaces'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_subspace_masters,name',
            'type' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $locationSubspaceMaster = LocationSubspaceMaster::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Location Subspace Masters',
    'Created',
    'LocationSubspaceMaster',
    $locationSubspaceMaster->id,
    'Location subspace master created: ' . $locationSubspaceMaster->name,
    null,
    $locationSubspaceMaster->only([
        'id',
        'name',
        'type',
        'is_active',
        'remarks'
    ])
);

        return redirect()
            ->route('location-subspace-masters.index')
            ->with('success', 'Location sub-space master added successfully.');
    }

    public function edit(LocationSubspaceMaster $locationSubspaceMaster)
    {
        return view('location-subspace-masters.edit', compact('locationSubspaceMaster'));
    }

    public function update(Request $request, LocationSubspaceMaster $locationSubspaceMaster)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_subspace_masters,name,' . $locationSubspaceMaster->id,
            'type' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);
        $oldValues = $locationSubspaceMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

        $locationSubspaceMaster->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        $newValues = $locationSubspaceMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

$action = 'Updated';
$description = 'Location subspace master updated: ' . $locationSubspaceMaster->name;

if (($oldValues['is_active'] ?? null) != ($newValues['is_active'] ?? null)) {
    $action = $newValues['is_active'] ? 'Activated' : 'Deactivated';

    $description = $newValues['is_active']
        ? 'Location subspace master activated: ' . $locationSubspaceMaster->name
        : 'Location subspace master deactivated: ' . $locationSubspaceMaster->name;
}

AuditHelper::log(
    'Location Subspace Masters',
    $action,
    'LocationSubspaceMaster',
    $locationSubspaceMaster->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('location-subspace-masters.index')
            ->with('success', 'Location sub-space master updated successfully.');
    }

    public function toggleStatus(LocationSubspaceMaster $locationSubspaceMaster)
    {
        $oldValues = $locationSubspaceMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);
        $locationSubspaceMaster->update([
            'is_active' => !$locationSubspaceMaster->is_active,
        ]);

        $newValues = $locationSubspaceMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

AuditHelper::log(
    'Location Subspace Masters',
    $newValues['is_active'] ? 'Activated' : 'Deactivated',
    'LocationSubspaceMaster',
    $locationSubspaceMaster->id,
    $newValues['is_active']
        ? 'Location subspace master activated: ' . $locationSubspaceMaster->name
        : 'Location subspace master deactivated: ' . $locationSubspaceMaster->name,
    $oldValues,
    $newValues
);

        return back()->with('success', 'Status updated successfully.');
    }
}