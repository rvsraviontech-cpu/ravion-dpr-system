<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Helpers\AuditHelper;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activityDivisions = ActivityDivision::where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        $activities = Activity::with('division')
            ->when($request->activity_division_id, function ($query) use ($request) {
                $query->where('activity_division_id', $request->activity_division_id);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('activity_name', 'like', '%' . $request->search . '%')
                        ->orWhere('work_stage', 'like', '%' . $request->search . '%')
                        ->orWhere('unit', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy('activity_division_id')
            ->orderBy('work_stage')
            ->orderBy('activity_name')
            ->get();

        return view('activities.index', compact(
            'activities',
            'activityDivisions'
        ));
    }

    public function create()
    {
        $activityDivisions = ActivityDivision::where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        $workStages = $this->workStages();
        $units = $this->units();

        return view('activities.create', compact(
            'activityDivisions',
            'workStages',
            'units'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity_division_id' => 'required|exists:activity_divisions,id',
            'activity_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'work_stage' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'remarks' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $activity = Activity::create($validated);

        AuditHelper::log(
            'Activities',
            'Created',
            'Activity',
            $activity->id,
            'Activity created: ' . $activity->activity_name,
            null,
            $activity->only([
                'id',
                'activity_division_id',
                'activity_name',
                'unit',
                'work_stage',
                'is_active',
                'remarks'
            ])
        );

        return redirect()
            ->route('activities.index', [
                'activity_division_id' => $activity->activity_division_id
            ])
            ->with('success', 'Activity created successfully.');
    }

    public function edit($id)
    {
        $activity = Activity::findOrFail($id);

        $activityDivisions = ActivityDivision::where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        $workStages = $this->workStages();
        $units = $this->units();

        return view('activities.edit', compact(
            'activity',
            'activityDivisions',
            'workStages',
            'units'
        ));
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);

        $validated = $request->validate([
            'activity_division_id' => 'required|exists:activity_divisions,id',
            'activity_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'work_stage' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'remarks' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $oldValues = $activity->only([
            'activity_division_id',
            'activity_name',
            'unit',
            'work_stage',
            'is_active',
            'remarks'
        ]);

        $activity->update($validated);

        $newValues = $activity->only([
            'activity_division_id',
            'activity_name',
            'unit',
            'work_stage',
            'is_active',
            'remarks'
        ]);

        AuditHelper::log(
            'Activities',
            'Updated',
            'Activity',
            $activity->id,
            'Activity updated: ' . $activity->activity_name,
            $oldValues,
            $newValues
        );

        return redirect()
            ->route('activities.index', [
                'activity_division_id' => $activity->activity_division_id
            ])
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);

        $oldValues = $activity->only([
            'id',
            'activity_division_id',
            'activity_name',
            'unit',
            'work_stage',
            'is_active',
            'remarks'
        ]);

        $activity->update([
            'is_active' => !$activity->is_active,
        ]);

        AuditHelper::log(
            'Activities',
            $activity->is_active ? 'Activated' : 'Deactivated',
            'Activity',
            $activity->id,
            ($activity->is_active ? 'Activity activated: ' : 'Activity deactivated: ') . $activity->activity_name,
            $oldValues,
            $activity->fresh()->only([
                'id',
                'activity_division_id',
                'activity_name',
                'unit',
                'work_stage',
                'is_active',
                'remarks'
            ])
        );

        return redirect()
            ->route('activities.index', [
                'activity_division_id' => $activity->activity_division_id
            ])
            ->with('success', 'Activity status updated successfully.');
    }

    private function workStages(): array
    {
        return [
            'Pre-Construction & Site Mobilization',
            'Survey, Layout & Marking',
            'Earthwork & Excavation',
            'PCC & Bed Preparation',
            'Foundation Works',
            'Plinth & Backfilling',
            'RCC Columns',
            'Beams, Slab & Staircase',
            'PT Slab / PT Beam Works',
            'Masonry Works',
            'Lintel, Chajja & Misc RCC',
            'Waterproofing',
            'Internal Plastering',
            'External Plastering',
            'Electrical First Fix',
            'Plumbing First Fix',
            'HVAC / Low Voltage / Automation',
            'Flooring & Wall Tiling',
            'Doors, Windows, Grills & Railings',
            'Painting & Surface Finishing',
            'False Ceiling',
            'Interior Base Works',
            'Sanitary & CP Fittings',
            'Final Electrical Fixtures',
            'External Development',
            'Testing, Snagging & Rectification',
            'Final Handover',
        ];
    }

    private function units(): array
    {
        return [
            'Nos',
            'Sqft',
            'Sqm',
            'Cum',
            'Cft',
            'Rft',
            'Meter',
            'Running Meter',
            'Kg',
            'Ton',
            'Bag',
            'Box',
            'Litre',
            'Hours',
            'Days',
            'LS',
        ];
    }
}