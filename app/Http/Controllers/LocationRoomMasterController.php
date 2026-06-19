<?php

namespace App\Http\Controllers;
use App\Helpers\AuditHelper;

use App\Models\LocationRoomMaster;
use Illuminate\Http\Request;

class LocationRoomMasterController extends Controller
{
    public function index()
    {
        $rooms = LocationRoomMaster::orderBy('room_type')
            ->orderBy('name')
            ->paginate(20);

        return view('location-room-masters.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_room_masters,name',
            'room_type' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $locationRoomMaster = LocationRoomMaster::create([
            'name' => $request->name,
            'room_type' => $request->room_type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
    'Location Room Masters',
    'Created',
    'LocationRoomMaster',
    $locationRoomMaster->id,
    'Location room master created: ' . $locationRoomMaster->name,
    null,
    $locationRoomMaster->only([
        'id',
        'name',
        'room_type',
        'is_active',
        'remarks'
    ])
);

        return redirect()
            ->route('location-room-masters.index')
            ->with('success', 'Location room master added successfully.');
    }

    public function edit(LocationRoomMaster $locationRoomMaster)
    {
        return view('location-room-masters.edit', compact('locationRoomMaster'));
    }

    public function update(Request $request, LocationRoomMaster $locationRoomMaster)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_room_masters,name,' . $locationRoomMaster->id,
            'room_type' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $locationRoomMaster->only([
    'name',
    'room_type',
    'is_active',
    'remarks'
]);

        $locationRoomMaster->update([
            'name' => $request->name,
            'room_type' => $request->room_type,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        $newValues = $locationRoomMaster->only([
    'name',
    'room_type',
    'is_active',
    'remarks'
]);

$action = 'Updated';
$description = 'Location room master updated: ' . $locationRoomMaster->name;

if (($oldValues['is_active'] ?? null) != ($newValues['is_active'] ?? null)) {
    $action = $newValues['is_active'] ? 'Activated' : 'Deactivated';

    $description = $newValues['is_active']
        ? 'Location room master activated: ' . $locationRoomMaster->name
        : 'Location room master deactivated: ' . $locationRoomMaster->name;
}

AuditHelper::log(
    'Location Room Masters',
    $action,
    'LocationRoomMaster',
    $locationRoomMaster->id,
    $description,
    $oldValues,
    $newValues
);

        return redirect()
            ->route('location-room-masters.index')
            ->with('success', 'Location room master updated successfully.');
    }

    public function toggleStatus(LocationRoomMaster $locationRoomMaster)
    {

    $oldValues = $locationRoomMaster->only([
    'name',
    'room_type',
    'is_active',
    'remarks'
]);
        $locationRoomMaster->update([
            'is_active' => !$locationRoomMaster->is_active,
        ]);

        $newValues = $locationRoomMaster->only([
    'name',
    'room_type',
    'is_active',
    'remarks'
]);

AuditHelper::log(
    'Location Room Masters',
    $newValues['is_active'] ? 'Activated' : 'Deactivated',
    'LocationRoomMaster',
    $locationRoomMaster->id,
    $newValues['is_active']
        ? 'Location room master activated: ' . $locationRoomMaster->name
        : 'Location room master deactivated: ' . $locationRoomMaster->name,
    $oldValues,
    $newValues
);

        return back()->with('success', 'Status updated successfully.');
    }
}