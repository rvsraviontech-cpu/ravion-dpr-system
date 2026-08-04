<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\SkillCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SkillCategoryController extends Controller
{
    /**
     * Display the skill category master list.
     */
    public function index(Request $request): View
    {
        $query = SkillCategory::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $skillCategories = $query
            ->ordered()
            ->paginate(config('rds.pagination.per_page', 15))
            ->withQueryString();

        return view(
            'skill-categories.index',
            compact('skillCategories')
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('skill-categories.create');
    }

    /**
     * Store a newly created skill category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSkillCategory($request);

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        $validated['is_system'] = false;

        $skillCategory = SkillCategory::create($validated);

        AuditHelper::log(
            'Skill Category Master',
            'Created',
            SkillCategory::class,
            $skillCategory->id,
            "Skill category '{$skillCategory->name}' was created.",
            null,
            $skillCategory->fresh()->toArray()
        );

        return redirect()
            ->route('skill-categories.index')
            ->with(
                'success',
                'Skill category created successfully.'
            );
    }

    /**
     * Display the specified skill category.
     */
    public function show(
        SkillCategory $skillCategory
    ): View {
        return view(
            'skill-categories.show',
            compact('skillCategory')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(
        SkillCategory $skillCategory
    ): View|RedirectResponse {
        if ($skillCategory->is_system) {
            return redirect()
                ->route('skill-categories.show', $skillCategory)
                ->with(
                    'error',
                    'System skill category records are read-only.'
                );
        }

        return view(
            'skill-categories.edit',
            compact('skillCategory')
        );
    }

    /**
     * Update the specified skill category.
     */
    public function update(
        Request $request,
        SkillCategory $skillCategory
    ): RedirectResponse {
        if ($skillCategory->is_system) {
            return redirect()
                ->route('skill-categories.show', $skillCategory)
                ->with(
                    'error',
                    'System skill category records cannot be updated.'
                );
        }

        $oldValues = $skillCategory->toArray();

        $validated = $this->validateSkillCategory(
            $request,
            $skillCategory
        );

        $validated = $this->prepareValidatedData(
            $request,
            $validated
        );

        unset($validated['is_system']);

        $skillCategory->update($validated);

        $skillCategory->refresh();

        AuditHelper::log(
            'Skill Category Master',
            'Updated',
            SkillCategory::class,
            $skillCategory->id,
            "Skill category '{$skillCategory->name}' was updated.",
            $oldValues,
            $skillCategory->toArray()
        );

        return redirect()
            ->route('skill-categories.show', $skillCategory)
            ->with(
                'success',
                'Skill category updated successfully.'
            );
    }

    /**
     * Activate or deactivate a skill category.
     */
    public function toggleStatus(
        SkillCategory $skillCategory
    ): RedirectResponse {
        if (
            $skillCategory->is_active
            && ! $skillCategory->canBeDeactivated()
        ) {
            return back()->with(
                'error',
                'System skill category records cannot be deactivated.'
            );
        }

        $oldValues = $skillCategory->toArray();

        $skillCategory->update([
            'is_active' => ! $skillCategory->is_active,
        ]);

        $skillCategory->refresh();

        $action = $skillCategory->is_active
            ? 'Activated'
            : 'Deactivated';

        AuditHelper::log(
            'Skill Category Master',
            $action,
            SkillCategory::class,
            $skillCategory->id,
            "Skill category '{$skillCategory->name}' was {$action}.",
            $oldValues,
            $skillCategory->toArray()
        );

        return back()->with(
            'success',
            $skillCategory->is_active
                ? 'Skill category activated successfully.'
                : 'Skill category deactivated successfully.'
        );
    }

    /**
     * Validate skill category input.
     */
    private function validateSkillCategory(
        Request $request,
        ?SkillCategory $skillCategory = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('skill_categories', 'code')
                    ->ignore($skillCategory?->id),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);
    }

    /**
     * Normalize and prepare validated data before persistence.
     */
    private function prepareValidatedData(
        Request $request,
        array $validated
    ): array {
        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['name'] = trim($validated['name']);

        $validated['remarks'] = $this->nullableTrim(
            $validated['remarks'] ?? null
        );

        $validated['sort_order'] = (int) $validated['sort_order'];

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * Trim nullable strings and convert empty values to null.
     */
    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}