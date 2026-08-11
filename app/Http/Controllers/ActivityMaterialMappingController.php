<?php

namespace App\Http\Controllers;

use App\Framework\Traits\MasterAuditTrait;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\ActivityMaterialMapping;
use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityMaterialMappingController extends Controller
{
    use MasterAuditTrait;

    /**
     * Display Activity–Material mappings.
     */
    public function index(Request $request): View
    {
        $query = ActivityMaterialMapping::query()
            ->with([
                'activity.division',
                'material.category',
                'material.brandMaster',
                'creator',
            ]);

        if ($request->filled('activity_division_id')) {
            $query->whereHas(
                'activity',
                fn (Builder $builder) => $builder->where(
                    'activity_division_id',
                    $request->integer('activity_division_id')
                )
            );
        }

        if ($request->filled('activity_id')) {
            $query->where(
                'activity_id',
                $request->integer('activity_id')
            );
        }

        if ($request->filled('material_category_id')) {
            $query->whereHas(
                'material',
                fn (Builder $builder) => $builder->where(
                    'material_category_id',
                    $request->integer('material_category_id')
                )
            );
        }

        if ($request->filled('material_id')) {
            $query->where(
                'material_id',
                $request->integer('material_id')
            );
        }

        if (
            $request->has('status')
            && $request->status !== null
            && $request->status !== ''
        ) {
            $query->where(
                'is_active',
                $request->boolean('status')
            );
        }

        if (
            $request->has('default')
            && $request->default !== null
            && $request->default !== ''
        ) {
            $query->where(
                'is_default',
                $request->boolean('default')
            );
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('remarks', 'like', "%{$search}%")
                    ->orWhereHas(
                        'activity',
                        fn (Builder $activityQuery) => $activityQuery
                            ->where('activity_name', 'like', "%{$search}%")
                            ->orWhere('work_stage', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'material',
                        fn (Builder $materialQuery) => $materialQuery
                            ->where('material_name', 'like', "%{$search}%")
                            ->orWhere('material_code', 'like', "%{$search}%")
                            ->orWhere('specification', 'like', "%{$search}%")
                    );
            });
        }

        $activityMaterialMappings = $query
            ->orderByDesc('is_active')
            ->orderByDesc('is_default')
            ->orderBy('activity_id')
            ->orderBy('material_id')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view(
            'activity-material-mappings.index',
            array_merge(
                compact('activityMaterialMappings'),
                $this->formData()
            )
        );
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view(
            'activity-material-mappings.create',
            $this->formData()
        );
    }

    /**
     * Store a new Activity–Material mapping.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMapping($request);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['created_by'] = auth()->id();

        $mapping = ActivityMaterialMapping::create($validated);

        $mapping->load([
            'activity',
            'material',
        ]);

        $this->auditCreated(
            'Activity Material Mappings',
            'ActivityMaterialMapping',
            $mapping->id,
            $this->mappingName($mapping),
            $this->auditValues($mapping)
        );

        return redirect()
            ->route('activity-material-mappings.index')
            ->with(
                'success',
                'Activity material mapping created successfully.'
            );
    }

    /**
     * Show the mapping details.
     */
    public function show(
        ActivityMaterialMapping $activityMaterialMapping
    ): View {
        $activityMaterialMapping->load([
            'activity.division',
            'material.category',
            'material.brandMaster',
            'creator',
        ]);

        return view(
            'activity-material-mappings.show',
            compact('activityMaterialMapping')
        );
    }

    /**
     * Show the edit form.
     */
    public function edit(
        ActivityMaterialMapping $activityMaterialMapping
    ): View {
        return view(
            'activity-material-mappings.edit',
            array_merge(
                compact('activityMaterialMapping'),
                $this->formData()
            )
        );
    }

    /**
     * Update an Activity–Material mapping.
     */
    public function update(
        Request $request,
        ActivityMaterialMapping $activityMaterialMapping
    ): RedirectResponse {
        $validated = $this->validateMapping(
            $request,
            $activityMaterialMapping
        );

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $this->auditValues($activityMaterialMapping);

        $activityMaterialMapping->update($validated);
        $activityMaterialMapping->refresh();
        $activityMaterialMapping->load([
            'activity',
            'material',
        ]);

        if (
            ($oldValues['is_active'] ?? null)
            !== $activityMaterialMapping->is_active
        ) {
            $this->auditStatusChanged(
                'Activity Material Mappings',
                'ActivityMaterialMapping',
                $activityMaterialMapping->id,
                $this->mappingName($activityMaterialMapping),
                $activityMaterialMapping->is_active,
                $oldValues,
                $this->auditValues($activityMaterialMapping)
            );
        } else {
            $this->auditUpdated(
                'Activity Material Mappings',
                'ActivityMaterialMapping',
                $activityMaterialMapping->id,
                $this->mappingName($activityMaterialMapping),
                $oldValues,
                $this->auditValues($activityMaterialMapping)
            );
        }

        return redirect()
            ->route('activity-material-mappings.index')
            ->with(
                'success',
                'Activity material mapping updated successfully.'
            );
    }

    /**
     * Toggle mapping status.
     */
    public function destroy(
        ActivityMaterialMapping $activityMaterialMapping
    ): RedirectResponse {
        $activityMaterialMapping->load([
            'activity',
            'material',
        ]);

        $oldValues = $this->auditValues($activityMaterialMapping);

        $activityMaterialMapping->update([
            'is_active' => ! $activityMaterialMapping->is_active,
        ]);

        $activityMaterialMapping->refresh();

        $this->auditStatusChanged(
            'Activity Material Mappings',
            'ActivityMaterialMapping',
            $activityMaterialMapping->id,
            $this->mappingName($activityMaterialMapping),
            $activityMaterialMapping->is_active,
            $oldValues,
            $this->auditValues($activityMaterialMapping)
        );

        return redirect()
            ->route('activity-material-mappings.index')
            ->with(
                'success',
                'Activity material mapping status updated successfully.'
            );
    }

    /**
     * Shared form and filter data.
     */
    private function formData(): array
    {
        return [
            'activityDivisions' => ActivityDivision::query()
                ->where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),

            'activities' => Activity::query()
                ->with('division')
                ->where('is_active', true)
                ->orderBy('activity_division_id')
                ->orderBy('work_stage')
                ->orderBy('activity_name')
                ->get(),

            'materialCategories' => MaterialCategory::query()
                ->where('is_active', true)
                ->orderBy('category_name')
                ->get(),

            'materials' => Material::query()
                ->with([
                    'category',
                    'brandMaster',
                ])
                ->where('is_active', true)
                ->orderBy('material_category_id')
                ->orderBy('material_name')
                ->get(),
        ];
    }

    /**
     * Validate mapping data.
     */
    private function validateMapping(
        Request $request,
        ?ActivityMaterialMapping $activityMaterialMapping = null
    ): array {
        return $request->validate([
            'activity_id' => [
                'required',
                'integer',
                'exists:activities,id',
            ],

            'material_id' => [
                'required',
                'integer',
                'exists:materials,id',

                Rule::unique(
                    'activity_material_mappings',
                    'material_id'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'activity_id',
                            $request->integer('activity_id')
                        )
                    )
                    ->ignore($activityMaterialMapping?->id),
            ],

            'is_default' => [
                'nullable',
                'boolean',
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
        ], [
            'material_id.unique' =>
                'This material is already mapped to the selected activity.',
        ]);
    }

    /**
     * Values used in audit logs.
     */
    private function auditValues(
        ActivityMaterialMapping $mapping
    ): array {
        return [
            'id' => $mapping->id,
            'activity_id' => $mapping->activity_id,
            'activity_name' => $mapping->activity?->activity_name,
            'material_id' => $mapping->material_id,
            'material_name' => $mapping->material?->material_name,
            'is_default' => $mapping->is_default,
            'is_active' => $mapping->is_active,
            'remarks' => $mapping->remarks,
            'created_by' => $mapping->created_by,
        ];
    }

    /**
     * Human-readable audit label.
     */
    private function mappingName(
        ActivityMaterialMapping $mapping
    ): string {
        $activityName = $mapping->activity?->activity_name
            ?? 'Activity #' . $mapping->activity_id;

        $materialName = $mapping->material?->material_name
            ?? 'Material #' . $mapping->material_id;

        return $activityName . ' → ' . $materialName;
    }
}