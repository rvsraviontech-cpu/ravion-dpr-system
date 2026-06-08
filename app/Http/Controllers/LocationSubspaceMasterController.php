<?php

namespace App\Http\Controllers;

use App\Models\LocationSubspaceMaster;
use Illuminate\Http\Request;

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

        LocationSubspaceMaster::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

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

        $locationSubspaceMaster->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        return redirect()
            ->route('location-subspace-masters.index')
            ->with('success', 'Location sub-space master updated successfully.');
    }

    public function toggleStatus(LocationSubspaceMaster $locationSubspaceMaster)
    {
        $locationSubspaceMaster->update([
            'is_active' => !$locationSubspaceMaster->is_active,
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}