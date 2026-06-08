<?php

namespace App\Http\Controllers;

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

        LocationRoomMaster::create([
            'name' => $request->name,
            'room_type' => $request->room_type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

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

        $locationRoomMaster->update([
            'name' => $request->name,
            'room_type' => $request->room_type,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('location-room-masters.index')
            ->with('success', 'Location room master updated successfully.');
    }

    public function toggleStatus(LocationRoomMaster $locationRoomMaster)
    {
        $locationRoomMaster->update([
            'is_active' => !$locationRoomMaster->is_active,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}