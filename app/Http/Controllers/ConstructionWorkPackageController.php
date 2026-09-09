<?php

namespace App\Http\Controllers;

use App\Models\ActivityDivision;
use App\Models\ConstructionWorkPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConstructionWorkPackageController extends Controller
{
    /**
     * Display the Construction Work Package Master.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'active');
        $rootId = $request->input('root_id');
        $activityDivisionId = $request->input('activity_division_id');

        /*
        |--------------------------------------------------------------------------
        | Root Work Packages
        |--------------------------------------------------------------------------
        |
        | The index is intentionally hierarchy-based rather than a flat paginated
        | list. There are only a limited number of root groups, and each root can
        | expose its matching child packages in a compact ERP table.
        |
        */

        $rootsQuery = ConstructionWorkPackage::query()
            ->root()
            ->with([
                'activityDivision:id,name',
                'children' => function ($query) use (
                    $search,
                    $status,
                    $activityDivisionId
                ) {
                    if ($search !== '') {
                        $query->where(function ($builder) use ($search) {
                            $builder
                                ->where('code', 'like', '%' . $search . '%')
                                ->orWhere('name', 'like', '%' . $search . '%')
                                ->orWhere('remarks', 'like', '%' . $search . '%');
                        });
                    }

                    $this->applyStatusFilter($query, $status);

                    if (!empty($activityDivisionId)) {
                        $query->where(
                            'activity_division_id',
                            $activityDivisionId
                        );
                    }

                    $query
                        ->with('activityDivision:id,name')
                        ->withCount([
                            'materialTypes as material_types_count',
                            'activeMaterialTypes as active_material_types_count',
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->withCount([
                'children as children_count',
                'activeChildren as active_children_count',
                'materialTypes as material_types_count',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Root Group Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($rootId)) {
            $rootsQuery->whereKey($rootId);
        }

        /*
        |--------------------------------------------------------------------------
        | Root Search
        |--------------------------------------------------------------------------
        |
        | A root remains visible when either:
        | 1. the root itself matches the search, or
        | 2. one of its children matches the search.
        |
        */

        if ($search !== '') {
            $rootsQuery->where(function ($query) use ($search) {
                $query
                    ->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('remarks', 'like', '%' . $search . '%')
                    ->orWhereHas('children', function ($childQuery) use ($search) {
                        $childQuery->where(function ($builder) use ($search) {
                            $builder
                                ->where('code', 'like', '%' . $search . '%')
                                ->orWhere('name', 'like', '%' . $search . '%')
                                ->orWhere('remarks', 'like', '%' . $search . '%');
                        });
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Activity Division Filter
        |--------------------------------------------------------------------------
        |
        | A root may be shown if either the root or any child belongs to the
        | requested Activity Division.
        |
        */

        if (!empty($activityDivisionId)) {
            $rootsQuery->where(function ($query) use ($activityDivisionId) {
                $query
                    ->where('activity_division_id', $activityDivisionId)
                    ->orWhereHas('children', function ($childQuery) use (
                        $activityDivisionId
                    ) {
                        $childQuery->where(
                            'activity_division_id',
                            $activityDivisionId
                        );
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        $this->applyStatusFilter($rootsQuery, $status);

        $roots = $rootsQuery
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Filter Dropdown Data
        |--------------------------------------------------------------------------
        */

        $rootOptions = ConstructionWorkPackage::query()
            ->root()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'is_active',
            ]);

        $activityDivisions = ActivityDivision::query()
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Summary Counts
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' => ConstructionWorkPackage::query()->count(),

            'roots' => ConstructionWorkPackage::query()
                ->root()
                ->count(),

            'children' => ConstructionWorkPackage::query()
                ->child()
                ->count(),

            'active' => ConstructionWorkPackage::query()
                ->active()
                ->count(),

            'inactive' => ConstructionWorkPackage::query()
                ->where('is_active', false)
                ->count(),

            'mapped_children' => ConstructionWorkPackage::query()
                ->child()
                ->whereHas('activeMaterialTypes')
                ->count(),

            'empty_children' => ConstructionWorkPackage::query()
                ->child()
                ->whereDoesntHave('activeMaterialTypes')
                ->count(),
        ];

        return view('construction-work-packages.index', compact(
            'roots',
            'rootOptions',
            'activityDivisions',
            'summary',
            'search',
            'status',
            'rootId',
            'activityDivisionId'
        ));
    }

    /**
     * Show the form for creating a new Work Package.
     */
    public function create(Request $request): View
    {
        $parentId = $request->input('parent_id');

        $parent = null;

        if (!empty($parentId)) {
            $parent = ConstructionWorkPackage::query()
                ->root()
                ->findOrFail($parentId);
        }

        $rootPackages = ConstructionWorkPackage::query()
            ->root()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'activity_division_id',
            ]);

        $activityDivisions = ActivityDivision::query()
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        $suggestedSortOrder = $this->nextSortOrder($parent?->id);

        return view('construction-work-packages.create', compact(
            'parent',
            'rootPackages',
            'activityDivisions',
            'suggestedSortOrder'
        ));
    }

    /**
     * Store a newly created Work Package.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWorkPackage($request);

        $this->validateParentHierarchy(
            parentId: $validated['parent_id'] ?? null
        );

        DB::transaction(function () use ($validated) {
            ConstructionWorkPackage::create([
                'parent_id' => $validated['parent_id'] ?? null,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'activity_division_id' =>
                    $validated['activity_division_id'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        return redirect()
            ->route('construction-work-packages.index')
            ->with(
                'success',
                'Construction Work Package created successfully.'
            );
    }

    /**
     * Display the specified Work Package.
     */
    public function show(
        ConstructionWorkPackage $constructionWorkPackage
    ): View {
        $constructionWorkPackage->load([
            'parent:id,code,name',
            'activityDivision:id,code,name',
            'children' => function ($query) {
                $query
                    ->with('activityDivision:id,code,name')
                    ->withCount([
                        'materialTypes as material_types_count',
                        'activeMaterialTypes as active_material_types_count',
                    ])
                    ->orderBy('sort_order')
                    ->orderBy('name');
            },
            'materialTypes' => function ($query) {
                $query
                    ->with('unit:id,name')
                    ->orderBy('material_group')
                    ->orderBy('sequence')
                    ->orderBy('material_type_name');
            },
        ]);

        $constructionWorkPackage->loadCount([
            'children',
            'activeChildren',
            'materialTypes',
            'activeMaterialTypes',
        ]);

        return view(
            'construction-work-packages.show',
            compact('constructionWorkPackage')
        );
    }

    /**
     * Show the form for editing the specified Work Package.
     */
    public function edit(
        ConstructionWorkPackage $constructionWorkPackage
    ): View {
        $constructionWorkPackage->load([
            'parent:id,code,name,activity_division_id',
            'activityDivision:id,code,name',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Only root packages can be selected as parents
        |--------------------------------------------------------------------------
        |
        | This intentionally keeps the hierarchy at:
        |
        | Root Group
        |   └── Work Package
        |
        | rather than allowing arbitrary recursive nesting.
        |
        */

        $rootPackages = ConstructionWorkPackage::query()
            ->root()
            ->where('id', '!=', $constructionWorkPackage->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'activity_division_id',
                'is_active',
            ]);

        $activityDivisions = ActivityDivision::query()
            ->where('is_active', true)
            ->orWhere(
                'id',
                $constructionWorkPackage->activity_division_id
            )
            ->orderBy('sequence')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'is_active',
            ]);

        return view('construction-work-packages.edit', compact(
            'constructionWorkPackage',
            'rootPackages',
            'activityDivisions'
        ));
    }

    /**
     * Update the specified Work Package.
     */
    public function update(
        Request $request,
        ConstructionWorkPackage $constructionWorkPackage
    ): RedirectResponse {
        $validated = $this->validateWorkPackage(
            $request,
            $constructionWorkPackage
        );

        $parentId = $validated['parent_id'] ?? null;

        $this->validateParentHierarchy(
            parentId: $parentId,
            currentPackage: $constructionWorkPackage
        );

        /*
        |--------------------------------------------------------------------------
        | Protect Root Groups With Children
        |--------------------------------------------------------------------------
        |
        | A root containing children cannot suddenly become a child package.
        | That would create a three-level hierarchy and break the intentionally
        | simple Root → Work Package design.
        |
        */

        if (
            $constructionWorkPackage->parent_id === null
            && $parentId !== null
            && $constructionWorkPackage->children()->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'parent_id' =>
                        'This root group already contains child Work Packages and cannot be converted into a child package.',
                ]);
        }

        DB::transaction(function () use (
            $constructionWorkPackage,
            $validated
        ) {
            $constructionWorkPackage->update([
                'parent_id' => $validated['parent_id'] ?? null,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'activity_division_id' =>
                    $validated['activity_division_id'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        return redirect()
            ->route(
                'construction-work-packages.show',
                $constructionWorkPackage
            )
            ->with(
                'success',
                'Construction Work Package updated successfully.'
            );
    }

    /**
     * Remove the specified Work Package.
     */
    public function destroy(
        ConstructionWorkPackage $constructionWorkPackage
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Protect Root Groups
        |--------------------------------------------------------------------------
        */

        if ($constructionWorkPackage->children()->exists()) {
            return back()->with(
                'error',
                'This Work Package group cannot be deleted because it contains child Work Packages.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Protect Material Mapping History
        |--------------------------------------------------------------------------
        |
        | Even though the pivot FK is configured with cascade delete, silently
        | deleting a Work Package with material mappings is undesirable for a
        | construction ERP master.
        |
        */

        if ($constructionWorkPackage->materialTypes()->exists()) {
            return back()->with(
                'error',
                'This Work Package cannot be deleted because Material Types are mapped to it. Deactivate it instead.'
            );
        }

        DB::transaction(function () use ($constructionWorkPackage) {
            $constructionWorkPackage->delete();
        });

        return redirect()
            ->route('construction-work-packages.index')
            ->with(
                'success',
                'Construction Work Package deleted successfully.'
            );
    }

    /**
     * Validate Work Package input.
     */
    private function validateWorkPackage(
        Request $request,
        ?ConstructionWorkPackage $constructionWorkPackage = null
    ): array {
        $packageId = $constructionWorkPackage?->id;

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                'exists:construction_work_packages,id',
            ],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'construction_work_packages',
                    'code'
                )->ignore($packageId),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'activity_division_id' => [
                'nullable',
                'integer',
                'exists:activity_divisions,id',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['remarks'] = isset($validated['remarks'])
            ? trim($validated['remarks'])
            : null;

        /*
        |--------------------------------------------------------------------------
        | Checkbox Handling
        |--------------------------------------------------------------------------
        */

        $validated['is_active'] = $request->boolean(
            'is_active',
            true
        );

        return $validated;
    }

    /**
     * Ensure the selected parent is a valid root Work Package.
     */
    private function validateParentHierarchy(
        ?int $parentId,
        ?ConstructionWorkPackage $currentPackage = null
    ): void {
        if ($parentId === null) {
            return;
        }

        if (
            $currentPackage !== null
            && $currentPackage->id === $parentId
        ) {
            abort(422, 'A Work Package cannot be its own parent.');
        }

        $parent = ConstructionWorkPackage::query()
            ->findOrFail($parentId);

        if ($parent->parent_id !== null) {
            abort(
                422,
                'Only a root Work Package group can be selected as the parent.'
            );
        }
    }

    /**
     * Apply Active / Inactive / All status filtering.
     */
    private function applyStatusFilter(
        $query,
        ?string $status
    ): void {
        match ($status) {
            'inactive' => $query->where('is_active', false),
            'all' => null,
            default => $query->where('is_active', true),
        };
    }

    /**
     * Suggest the next sort order inside the selected hierarchy level.
     */
    private function nextSortOrder(?int $parentId = null): int
    {
        $maxSortOrder = ConstructionWorkPackage::query()
            ->where('parent_id', $parentId)
            ->max('sort_order');

        if ($maxSortOrder === null) {
            return 10;
        }

        return ((int) floor($maxSortOrder / 10) * 10) + 10;
    }
}