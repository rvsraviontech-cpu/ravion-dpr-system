<?php

namespace App\Http\Controllers;

use App\Models\UnitMaster;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class UnitMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitMaster::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('unit_name', 'like', "%{$search}%")
                  ->orWhere('unit_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $units = $query
            ->orderBy('unit_name')
            ->paginate(20)
            ->withQueryString();

        return view('unit-masters.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255|unique:unit_masters,unit_name',
            'unit_code' => 'nullable|string|max:255|unique:unit_masters,unit_code',
            'remarks' => 'nullable|string',
        ]);

        $unit = UnitMaster::create([
            'unit_name' => $request->unit_name,
            'unit_code' => $request->unit_code,
            'is_active' => true,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
            'Unit Masters',
            'Created',
            'UnitMaster',
            $unit->id,
            'Unit created: ' . $unit->unit_name,
            null,
            $unit->toArray()
        );

        return redirect()
            ->route('unit-masters.index')
            ->with('success', 'Unit added successfully.');
    }

    public function edit(UnitMaster $unitMaster)
    {
        return view('unit-masters.edit', compact('unitMaster'));
    }

    public function update(Request $request, UnitMaster $unitMaster)
    {
        $request->validate([
            'unit_name' => 'required|string|max:255|unique:unit_masters,unit_name,' . $unitMaster->id,
            'unit_code' => 'nullable|string|max:255|unique:unit_masters,unit_code,' . $unitMaster->id,
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $unitMaster->toArray();

        $unitMaster->update([
            'unit_name' => $request->unit_name,
            'unit_code' => $request->unit_code,
            'is_active' => $request->is_active,
            'remarks' => $request->remarks,
        ]);

        AuditHelper::log(
            'Unit Masters',
            'Updated',
            'UnitMaster',
            $unitMaster->id,
            'Unit updated: ' . $unitMaster->unit_name,
            $oldValues,
            $unitMaster->fresh()->toArray()
        );

        return redirect()
            ->route('unit-masters.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function toggleStatus(UnitMaster $unitMaster)
    {
        $oldValues = $unitMaster->toArray();

        $unitMaster->update([
            'is_active' => !$unitMaster->is_active,
        ]);

        AuditHelper::log(
            'Unit Masters',
            $unitMaster->is_active ? 'Activated' : 'Deactivated',
            'UnitMaster',
            $unitMaster->id,
            $unitMaster->is_active
                ? 'Unit activated: ' . $unitMaster->unit_name
                : 'Unit deactivated: ' . $unitMaster->unit_name,
            $oldValues,
            $unitMaster->fresh()->toArray()
        );

        return back()->with('success', 'Unit status updated successfully.');
    }
}