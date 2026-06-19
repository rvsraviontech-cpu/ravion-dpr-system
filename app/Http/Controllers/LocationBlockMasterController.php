<?php

namespace App\Http\Controllers;

use App\Models\LocationBlockMaster;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class LocationBlockMasterController extends Controller
{
    public function index()
    {
        $blocks = LocationBlockMaster::orderBy('name')->paginate(20);

        return view('location-block-masters.index', compact('blocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_block_masters,name',
            'type' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $locationBlockMaster = LocationBlockMaster::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Location Block Masters',
    'Created',
    'LocationBlockMaster',
    $locationBlockMaster->id,
    'Location block master created: ' . $locationBlockMaster->name,
    null,
    $locationBlockMaster->only([
        'id',
        'name',
        'type',
        'is_active',
        'remarks'
    ])
);

        return redirect()
            ->route('location-block-masters.index')
            ->with('success', 'Location block master added successfully.');
    }

    public function edit(LocationBlockMaster $locationBlockMaster)
{
    return view('location-block-masters.edit', compact('locationBlockMaster'));
}

public function update(Request $request, LocationBlockMaster $locationBlockMaster)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:location_block_masters,name,' . $locationBlockMaster->id,
        'type' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
        'remarks' => 'nullable|string',
    ]);

    $oldValues = $locationBlockMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

    $locationBlockMaster->update([
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    $newValues = $locationBlockMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

$action = 'Updated';
$description = 'Location block master updated: ' . $locationBlockMaster->name;

if (($oldValues['is_active'] ?? null) != ($newValues['is_active'] ?? null)) {
    $action = $newValues['is_active'] ? 'Activated' : 'Deactivated';

    $description = $newValues['is_active']
        ? 'Location block master activated: ' . $locationBlockMaster->name
        : 'Location block master deactivated: ' . $locationBlockMaster->name;
}

AuditHelper::log(
    'Location Block Masters',
    $action,
    'LocationBlockMaster',
    $locationBlockMaster->id,
    $description,
    $oldValues,
    $newValues
);

    return redirect()
        ->route('location-block-masters.index')
        ->with('success', 'Location block master updated successfully.');
}

public function toggleStatus(LocationBlockMaster $locationBlockMaster)
{

$oldValues = $locationBlockMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);
    $locationBlockMaster->update([
        'is_active' => !$locationBlockMaster->is_active,
    ]);

    $newValues = $locationBlockMaster->only([
    'name',
    'type',
    'is_active',
    'remarks'
]);

AuditHelper::log(
    'Location Block Masters',
    $newValues['is_active'] ? 'Activated' : 'Deactivated',
    'LocationBlockMaster',
    $locationBlockMaster->id,
    $newValues['is_active']
        ? 'Location block master activated: ' . $locationBlockMaster->name
        : 'Location block master deactivated: ' . $locationBlockMaster->name,
    $oldValues,
    $newValues
);

    return back()->with('success', 'Status updated successfully.');
}
}