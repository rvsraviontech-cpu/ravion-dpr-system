<?php

namespace App\Http\Controllers;

use App\Models\LocationFloorMaster;
use Illuminate\Http\Request;

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

        LocationFloorMaster::create([
            'name' => $request->name,
            'sequence' => $request->sequence ?? 0,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

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

        $locationFloorMaster->update([
            'name' => $request->name,
            'sequence' => $request->sequence ?? 0,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('location-floor-masters.index')
            ->with('success', 'Location floor master updated successfully.');
    }

    public function toggleStatus(LocationFloorMaster $locationFloorMaster)
    {
        $locationFloorMaster->update([
            'is_active' => !$locationFloorMaster->is_active,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}