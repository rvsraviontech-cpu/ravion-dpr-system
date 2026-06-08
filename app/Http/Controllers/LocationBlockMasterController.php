<?php

namespace App\Http\Controllers;

use App\Models\LocationBlockMaster;
use Illuminate\Http\Request;

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

        LocationBlockMaster::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

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

    $locationBlockMaster->update([
        'name' => $request->name,
        'type' => $request->type,
        'is_active' => $request->is_active,
        'remarks' => $request->remarks,
    ]);

    return redirect()
        ->route('location-block-masters.index')
        ->with('success', 'Location block master updated successfully.');
}

public function toggleStatus(LocationBlockMaster $locationBlockMaster)
{
    $locationBlockMaster->update([
        'is_active' => !$locationBlockMaster->is_active,
    ]);

    return back()->with('success', 'Status updated successfully.');
}
}