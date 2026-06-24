<?php

namespace App\Http\Controllers;

use App\Models\ActivityDivision;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class ActivityDivisionController extends Controller
{
    public function index(Request $request)
    {
        $activityDivisions = ActivityDivision::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        return view('activity-divisions.index', compact('activityDivisions'));
    }

    public function create()
    {
        return view('activity-divisions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:activity_divisions,code',
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $division = ActivityDivision::create($validated);

        AuditHelper::log(
            'Activity Divisions',
            'Created',
            'ActivityDivision',
            $division->id,
            'Activity division created: ' . $division->name,
            null,
            $division->toArray()
        );

        return redirect()
            ->route('activity-divisions.index')
            ->with('success', 'Activity division created successfully.');
    }

    public function edit(ActivityDivision $activityDivision)
    {
        return view('activity-divisions.edit', compact('activityDivision'));
    }

    public function update(Request $request, ActivityDivision $activityDivision)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:activity_divisions,code,' . $activityDivision->id,
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $activityDivision->toArray();

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $activityDivision->update($validated);

        AuditHelper::log(
            'Activity Divisions',
            'Updated',
            'ActivityDivision',
            $activityDivision->id,
            'Activity division updated: ' . $activityDivision->name,
            $oldValues,
            $activityDivision->fresh()->toArray()
        );

        return redirect()
            ->route('activity-divisions.index')
            ->with('success', 'Activity division updated successfully.');
    }

    public function destroy(ActivityDivision $activityDivision)
    {
        $oldValues = $activityDivision->toArray();

        $activityDivision->update([
            'is_active' => !$activityDivision->is_active,
        ]);

        AuditHelper::log(
            'Activity Divisions',
            $activityDivision->is_active ? 'Activated' : 'Deactivated',
            'ActivityDivision',
            $activityDivision->id,
            ($activityDivision->is_active ? 'Activity division activated: ' : 'Activity division deactivated: ') . $activityDivision->name,
            $oldValues,
            $activityDivision->fresh()->toArray()
        );

        return redirect()
            ->route('activity-divisions.index')
            ->with('success', 'Activity division status updated successfully.');
    }
}