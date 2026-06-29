<?php

namespace App\Http\Controllers;

use App\Framework\Controllers\BaseMasterController;
use App\Models\ActivityDivision;
use Illuminate\Http\Request;

class ActivityDivisionController extends BaseMasterController
{
    protected string $model = ActivityDivision::class;

    protected string $view = 'activity-divisions';

    protected string $module = 'Activity Divisions';

    protected string $entity = 'ActivityDivision';

    protected string $nameField = 'name';

    protected array $searchColumns = [
        'code',
        'name',
        'remarks',
    ];

    protected array $filters = [
        'status' => 'is_active',
    ];

    protected array $orderBy = [
        'sequence' => 'asc',
        'name' => 'asc',
    ];

        public function store(Request $request)
    {
        $validated = $this->validateDivision($request);

        $division = ActivityDivision::create($validated);

        $this->auditCreated(
            $this->module,
            $this->entity,
            $division->id,
            $division->name,
            $this->auditValues($division)
        );

        return redirect()
            ->route('activity-divisions.index')
            ->with('success', 'Activity division created successfully.');
    }

    public function update(Request $request, ActivityDivision $activityDivision)
    {
        $validated = $this->validateDivision($request, $activityDivision);

        $oldValues = $this->auditValues($activityDivision);

        $activityDivision->update($validated);

        $this->auditUpdated(
            $this->module,
            $this->entity,
            $activityDivision->id,
            $activityDivision->name,
            $oldValues,
            $this->auditValues($activityDivision->fresh())
        );

        return redirect()
            ->route('activity-divisions.index')
            ->with('success', 'Activity division updated successfully.');
    }

    public function destroy(ActivityDivision $activityDivision)
    {
        $oldValues = $this->auditValues($activityDivision);

        $activityDivision->update([
            'is_active' => !$activityDivision->is_active,
        ]);

        $activityDivision->refresh();

        $this->auditStatusChanged(
            $this->module,
            $this->entity,
            $activityDivision->id,
            $activityDivision->name,
            $activityDivision->is_active,
            $oldValues,
            $this->auditValues($activityDivision)
        );

        return redirect()
            ->route('activity-divisions.index')
            ->with('success', 'Activity division status updated successfully.');
    }

    private function validateDivision(Request $request, ?ActivityDivision $activityDivision = null): array
    {
        $divisionId = $activityDivision?->id;

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:activity_divisions,code,' . $divisionId,
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        return $validated;
    }

    private function auditValues(ActivityDivision $division): array
    {
        return $division->only([
            'id',
            'code',
            'name',
            'sequence',
            'is_active',
            'remarks',
        ]);
    }
}