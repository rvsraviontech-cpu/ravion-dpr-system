<?php

namespace App\Http\Controllers;

use App\Models\LocationUnitMaster;
use Illuminate\Http\Request;

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

        LocationUnitMaster::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

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

        $locationUnitMaster->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('location-unit-masters.index')
            ->with('success', 'Location unit master updated successfully.');
    }

    public function toggleStatus(LocationUnitMaster $locationUnitMaster)
    {
        $locationUnitMaster->update([
            'is_active' => !$locationUnitMaster->is_active,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}