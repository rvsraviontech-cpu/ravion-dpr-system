<?php

namespace App\Http\Controllers;

use App\Framework\Controllers\BaseMasterController;
use App\Framework\Traits\MasterAuditTrait;
use App\Models\WorkStage;
use Illuminate\Http\Request;

class WorkStageController extends BaseMasterController
{
    use MasterAuditTrait;

    protected string $model = WorkStage::class;

    protected string $view = 'work-stages';

    protected string $module = 'Work Stages';

    protected string $entity = 'WorkStage';

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
        $validated = $this->validateWorkStage($request);

        $workStage = WorkStage::create($validated);

        $this->auditCreated(
            $this->module,
            $this->entity,
            $workStage->id,
            $workStage->name,
            $this->auditValues($workStage)
        );

        return redirect()
            ->route('work-stages.index')
            ->with('success', 'Work stage created successfully.');
    }

    public function update(Request $request, WorkStage $workStage)
    {
        $validated = $this->validateWorkStage($request, $workStage);

        $oldValues = $this->auditValues($workStage);

        $workStage->update($validated);

        $this->auditUpdated(
            $this->module,
            $this->entity,
            $workStage->id,
            $workStage->name,
            $oldValues,
            $this->auditValues($workStage->fresh())
        );

        return redirect()
            ->route('work-stages.index')
            ->with('success', 'Work stage updated successfully.');
    }

    public function destroy(WorkStage $workStage)
    {
        $oldValues = $this->auditValues($workStage);

        $workStage->update([
            'is_active' => !$workStage->is_active,
        ]);

        $workStage->refresh();

        $this->auditStatusChanged(
            $this->module,
            $this->entity,
            $workStage->id,
            $workStage->name,
            $workStage->is_active,
            $oldValues,
            $this->auditValues($workStage)
        );

        return redirect()
            ->route('work-stages.index')
            ->with('success', 'Work stage status updated successfully.');
    }

    private function validateWorkStage(Request $request, ?WorkStage $workStage = null): array
    {
        $workStageId = $workStage?->id;

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:work_stages,code,' . $workStageId,
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        return $validated;
    }

    private function auditValues(WorkStage $workStage): array
    {
        return $workStage->only([
            'id',
            'code',
            'name',
            'sequence',
            'is_active',
            'remarks',
        ]);
    }
}