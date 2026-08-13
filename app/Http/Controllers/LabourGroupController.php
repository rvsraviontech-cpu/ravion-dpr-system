<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Labour;
use App\Models\LabourGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class LabourGroupController extends Controller
{
    public function index(Request $request): View
    {
        $query = LabourGroup::query()->withCount('labours');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($search): void {
                $builder->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $labourGroups = $query->ordered()->paginate(20)->withQueryString();

        return view('labour-groups.index', compact('labourGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:labour_groups,code'],
            'name' => ['required', 'string', 'max:150', 'unique:labour_groups,name'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $group = LabourGroup::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => true,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        AuditHelper::log('Labour Groups', 'Created', LabourGroup::class, $group->id,
            "Labour Group '{$group->name}' was created.", null, $group->toArray());

        return back()->with('success', 'Labour Group created successfully.');
    }

    public function update(Request $request, LabourGroup $labourGroup): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('labour_groups', 'code')->ignore($labourGroup->id)],
            'name' => ['required', 'string', 'max:150', Rule::unique('labour_groups', 'name')->ignore($labourGroup->id)],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $old = $labourGroup->toArray();
        $labourGroup->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $validated['is_active'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        AuditHelper::log('Labour Groups', 'Updated', LabourGroup::class, $labourGroup->id,
            "Labour Group '{$labourGroup->name}' was updated.", $old, $labourGroup->fresh()->toArray());

        return back()->with('success', 'Labour Group updated successfully.');
    }


    public function assignments(Request $request): View
    {
        $labours = Labour::query()
            ->with(['labourGroup', 'designationRole', 'currentProject'])
            ->when($request->filled('project_id'), fn ($q) => $q->where('current_project_id', $request->integer('project_id')))
            ->ordered()
            ->get();

        return view('labour-groups.assignments', [
            'labours' => $labours,
            'labourGroups' => LabourGroup::query()->active()->ordered()->get(),
            'projects' => \App\Models\Project::query()->active()->orderBy('project_name')->get(),
        ]);
    }

    public function updateAssignments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'groups' => ['nullable', 'array'],
            'groups.*' => ['nullable', 'integer', 'exists:labour_groups,id'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach (($validated['groups'] ?? []) as $labourId => $groupId) {
                Labour::query()->whereKey((int) $labourId)->update([
                    'labour_group_id' => $groupId ? (int) $groupId : null,
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        return back()->with('success', 'Labour Group assignments updated successfully.');
    }

    public function toggleStatus(LabourGroup $labourGroup): RedirectResponse
    {
        $old = $labourGroup->toArray();
        $labourGroup->update(['is_active' => ! $labourGroup->is_active]);

        AuditHelper::log('Labour Groups', $labourGroup->is_active ? 'Activated' : 'Deactivated',
            LabourGroup::class, $labourGroup->id, "Labour Group '{$labourGroup->name}' status changed.",
            $old, $labourGroup->fresh()->toArray());

        return back()->with('success', 'Labour Group status updated successfully.');
    }
}
