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
                    ->orWhere('unit_code', 'like', "%{$search}%")
                    ->orWhere('symbol', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }

        $units = $query
            ->orderBy('unit_type')
            ->orderBy('unit_name')
            ->paginate(20)
            ->withQueryString();

        $unitTypes = $this->unitTypes();

        return view('unit-masters.index', compact(
            'units',
            'unitTypes'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_name' => 'required|string|max:255|unique:unit_masters,unit_name',
            'unit_code' => 'nullable|string|max:255|unique:unit_masters,unit_code',
            'symbol' => 'nullable|string|max:50',
            'unit_type' => 'nullable|string|max:100',
            'decimal_allowed' => 'nullable|boolean',
            'remarks' => 'nullable|string',
        ]);

        $validated['is_active'] = true;
        $validated['decimal_allowed'] = $request->has('decimal_allowed') ? 1 : 0;

        $unit = UnitMaster::create($validated);

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
        $unitTypes = $this->unitTypes();

        return view('unit-masters.edit', compact(
            'unitMaster',
            'unitTypes'
        ));
    }

    public function update(Request $request, UnitMaster $unitMaster)
    {
        $validated = $request->validate([
            'unit_name' => 'required|string|max:255|unique:unit_masters,unit_name,' . $unitMaster->id,
            'unit_code' => 'nullable|string|max:255|unique:unit_masters,unit_code,' . $unitMaster->id,
            'symbol' => 'nullable|string|max:50',
            'unit_type' => 'nullable|string|max:100',
            'decimal_allowed' => 'nullable|boolean',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);

        $oldValues = $unitMaster->toArray();

        $validated['decimal_allowed'] = $request->has('decimal_allowed') ? 1 : 0;

        $unitMaster->update($validated);

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

    private function unitTypes(): array
    {
        return [
            'Area',
            'Volume',
            'Length',
            'Weight',
            'Count',
            'Packaging',
            'Liquid',
            'Time',
            'Lump Sum',
            'Other',
        ];
    }
}