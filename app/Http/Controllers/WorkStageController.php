<?php

namespace App\Http\Controllers;

use App\Models\WorkStage;
use Illuminate\Http\Request;
use App\Helpers\AuditHelper;

class WorkStageController extends Controller
{
    public function index(Request $request)
    {
        $workStages = WorkStage::query()
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

        return view('work-stages.index', compact('workStages'));
    }

    public function create()
    {
        return view('work-stages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:work_stages,code',
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $workStage = WorkStage::create($validated);

        AuditHelper::log(
            'Work Stages',
            'Created',
            'WorkStage',
            $workStage->id,
            'Work stage created: ' . $workStage->name,
            null,
            $workStage->toArray()
        );

        return redirect()
            ->route('work-stages.index')
            ->with('success', 'Work stage created successfully.');
    }

    public function edit(WorkStage $workStage)
    {
        return view('work-stages.edit', compact('workStage'));
    }

    public function update(Request $request, WorkStage $workStage)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:work_stages,code,' . $workStage->id,
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $workStage->toArray();

        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $workStage->update($validated);

        AuditHelper::log(
            'Work Stages',
            'Updated',
            'WorkStage',
            $workStage->id,
            'Work stage updated: ' . $workStage->name,
            $oldValues,
            $workStage->fresh()->toArray()
        );

        return redirect()
            ->route('work-stages.index')
            ->with('success', 'Work stage updated successfully.');
    }

    public function destroy(WorkStage $workStage)
    {
        $oldValues = $workStage->toArray();

        $workStage->update([
            'is_active' => !$workStage->is_active,
        ]);

        AuditHelper::log(
            'Work Stages',
            $workStage->is_active ? 'Activated' : 'Deactivated',
            'WorkStage',
            $workStage->id,
            ($workStage->is_active ? 'Work stage activated: ' : 'Work stage deactivated: ') . $workStage->name,
            $oldValues,
            $workStage->fresh()->toArray()
        );

        return redirect()
            ->route('work-stages.index')
            ->with('success', 'Work stage status updated successfully.');
    }
}