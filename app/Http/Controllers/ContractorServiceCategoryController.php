<?php

namespace App\Http\Controllers;

use App\Framework\Traits\MasterAuditTrait;
use App\Models\ActivityDivision;
use App\Models\ContractorServiceCategory;
use Illuminate\Http\Request;

class ContractorServiceCategoryController extends Controller
{
    use MasterAuditTrait;

    public function index(Request $request)
    {
        $serviceCategories = ContractorServiceCategory::with('division')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('code', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('remarks', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->activity_division_id, function ($query) use ($request) {
                $query->where('activity_division_id', $request->activity_division_id);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy('activity_division_id')
            ->orderBy('name')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view('contractor-service-categories.index', array_merge(
            compact('serviceCategories'),
            $this->formData()
        ));
    }

    public function create()
    {
        return view('contractor-service-categories.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateServiceCategory($request);

        $category = ContractorServiceCategory::create($validated);

        $this->auditCreated(
            'Contractor Service Categories',
            'ContractorServiceCategory',
            $category->id,
            $category->name,
            $this->auditValues($category)
        );

        return redirect()
            ->route('contractor-service-categories.index')
            ->with('success', 'Contractor service category created successfully.');
    }

    public function show(ContractorServiceCategory $contractorServiceCategory)
    {
        $contractorServiceCategory->load('division');

        return view('contractor-service-categories.show', compact('contractorServiceCategory'));
    }

    public function edit(ContractorServiceCategory $contractorServiceCategory)
    {
        return view('contractor-service-categories.edit', array_merge(
            compact('contractorServiceCategory'),
            $this->formData()
        ));
    }

    public function update(Request $request, ContractorServiceCategory $contractorServiceCategory)
    {
        $validated = $this->validateServiceCategory($request, $contractorServiceCategory);

        $oldValues = $this->auditValues($contractorServiceCategory);

        $contractorServiceCategory->update($validated);
        $contractorServiceCategory->refresh();

        $this->auditUpdated(
            'Contractor Service Categories',
            'ContractorServiceCategory',
            $contractorServiceCategory->id,
            $contractorServiceCategory->name,
            $oldValues,
            $this->auditValues($contractorServiceCategory)
        );

        return redirect()
            ->route('contractor-service-categories.index')
            ->with('success', 'Contractor service category updated successfully.');
    }

    public function destroy(ContractorServiceCategory $contractorServiceCategory)
    {
        $oldValues = $this->auditValues($contractorServiceCategory);

        $contractorServiceCategory->update([
            'is_active' => !$contractorServiceCategory->is_active,
        ]);

        $contractorServiceCategory->refresh();

        $this->auditStatusChanged(
            'Contractor Service Categories',
            'ContractorServiceCategory',
            $contractorServiceCategory->id,
            $contractorServiceCategory->name,
            $contractorServiceCategory->is_active,
            $oldValues,
            $this->auditValues($contractorServiceCategory)
        );

        return redirect()
            ->route('contractor-service-categories.index')
            ->with('success', 'Contractor service category status updated successfully.');
    }

    private function formData(): array
    {
        return [
            'activityDivisions' => ActivityDivision::where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function validateServiceCategory(Request $request, ?ContractorServiceCategory $category = null): array
    {
        $categoryId = $category?->id;

        $validated = $request->validate([
            'activity_division_id' => 'nullable|exists:activity_divisions,id',
            'code' => 'nullable|string|max:50|unique:contractor_service_categories,code,' . $categoryId,
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'remarks' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        return $validated;
    }

    private function auditValues(ContractorServiceCategory $category): array
    {
        return $category->only([
            'id',
            'activity_division_id',
            'code',
            'name',
            'is_active',
            'remarks',
        ]);
    }
}