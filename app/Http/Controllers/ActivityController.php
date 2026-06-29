<?php

namespace App\Http\Controllers;

use App\Framework\Traits\MasterAuditTrait;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\UnitMaster;
use App\Models\WorkStage;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
        use MasterAuditTrait;
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
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        return view('activities.index', compact(
            'activities',
            'activityDivisions'
        ));
    }

    public function create()
    {
        return view('activities.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateActivity($request);

        $activity = Activity::create($validated);

        $this->auditCreated(
    'Activities',
    'Activity',
    $activity->id,
    $activity->activity_name,
    $this->auditValues($activity)
);
        return redirect()
            ->route('activities.index', [
                'activity_division_id' => $activity->activity_division_id,
            ])
            ->with('success', 'Activity created successfully.');
    }

    public function show(Activity $activity)
    {
        $activity->load('division');

        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity)
    {
        return view('activities.edit', array_merge(
            ['activity' => $activity],
            $this->formData()
        ));
    }

    public function update(Request $request, Activity $activity)
    {
        $validated = $this->validateActivity($request);

        $oldValues = $this->auditValues($activity);

        $activity->update($validated);

       $this->auditUpdated(
    'Activities',
    'Activity',
    $activity->id,
    $activity->activity_name,
    $oldValues,
    $this->auditValues($activity->fresh())
);

        return redirect()
            ->route('activities.index', [
                'activity_division_id' => $activity->activity_division_id,
            ])
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(Activity $activity)
    {
        $oldValues = $this->auditValues($activity);

        $activity->update([
            'is_active' => !$activity->is_active,
        ]);

        $activity->refresh();

        $this->auditStatusChanged(
    'Activities',
    'Activity',
    $activity->id,
    $activity->activity_name,
    $activity->is_active,
    $oldValues,
    $this->auditValues($activity)
);

        return redirect()
            ->route('activities.index', [
                'activity_division_id' => $activity->activity_division_id,
            ])
            ->with('success', 'Activity status updated successfully.');
    }

    private function formData(): array
    {
        return [
            'activityDivisions' => ActivityDivision::where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),

            'workStages' => WorkStage::where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),

            'units' => UnitMaster::where('is_active', true)
                ->orderBy('unit_name')
                ->get(),
        ];
    }

    private function validateActivity(Request $request): array
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

        return $validated;
    }

    private function auditValues(Activity $activity): array
    {
        return $activity->only([
            'id',
            'activity_division_id',
            'activity_name',
            'unit',
            'work_stage',
            'is_active',
            'remarks',
        ]);
    }
}