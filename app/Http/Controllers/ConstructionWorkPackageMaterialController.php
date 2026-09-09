<?php

namespace App\Http\Controllers;

use App\Framework\Traits\MasterAuditTrait;
use App\Models\ConstructionWorkPackage;
use App\Models\ConstructionWorkPackageMaterial;
use App\Models\MaterialType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConstructionWorkPackageMaterialController extends Controller
{
    use MasterAuditTrait;

    /**
     * Display the Work Package ↔ Material Type mapping workspace.
     */
    public function index(Request $request): View
    {
        $rootId = $request->integer('root_id') ?: null;
        $workPackageId = $request->integer('work_package_id') ?: null;
        $materialGroup = trim(
            $request->string('material_group')->toString()
        );
        $search = trim(
            $request->string('search')->toString()
        );
        $status = $request->string('status')->toString();

        if (! in_array($status, ['active', 'inactive', 'all'], true)) {
            $status = 'active';
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Work Package
        |--------------------------------------------------------------------------
        */

        $selectedWorkPackage = null;

        if ($workPackageId) {
            $selectedWorkPackage = ConstructionWorkPackage::query()
                ->with([
                    'parent:id,code,name',
                    'activityDivision:id,code,name',
                ])
                ->child()
                ->findOrFail($workPackageId);

            /*
             * Keep the group filter synchronized with the selected child.
             */
            $rootId = $selectedWorkPackage->parent_id;
        }

        /*
        |--------------------------------------------------------------------------
        | Mapping Query
        |--------------------------------------------------------------------------
        */

        $mappings = null;

        if ($selectedWorkPackage) {
            $query = ConstructionWorkPackageMaterial::query()
                ->with([
                    'materialType.unit',
                ])
                ->where(
                    'construction_work_package_id',
                    $selectedWorkPackage->id
                );

            if ($materialGroup !== '') {
                $query->whereHas(
                    'materialType',
                    fn (Builder $builder) => $builder->where(
                        'material_group',
                        $materialGroup
                    )
                );
            }

            if ($search !== '') {
                $query->whereHas(
                    'materialType',
                    function (Builder $builder) use ($search) {
                        $builder->where(function (Builder $materialQuery) use ($search) {
                            $materialQuery
                                ->where(
                                    'material_type_name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'material_type_code',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'material_group',
                                    'like',
                                    '%' . $search . '%'
                                );
                        });
                    }
                );
            }

            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            $mappings = $query
                ->orderByDesc('is_preferred')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(
                    config('rds.pagination.per_page', 25)
                )
                ->withQueryString();
        }

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_mappings' =>
                ConstructionWorkPackageMaterial::query()->count(),

            'active_mappings' =>
                ConstructionWorkPackageMaterial::query()
                    ->where('is_active', true)
                    ->count(),

            'preferred_mappings' =>
                ConstructionWorkPackageMaterial::query()
                    ->where('is_preferred', true)
                    ->count(),

            'mapped_material_types' =>
                ConstructionWorkPackageMaterial::query()
                    ->distinct()
                    ->count('material_type_id'),

            'selected_total' => $selectedWorkPackage
                ? ConstructionWorkPackageMaterial::query()
                    ->where(
                        'construction_work_package_id',
                        $selectedWorkPackage->id
                    )
                    ->count()
                : 0,

            'selected_active' => $selectedWorkPackage
                ? ConstructionWorkPackageMaterial::query()
                    ->where(
                        'construction_work_package_id',
                        $selectedWorkPackage->id
                    )
                    ->where('is_active', true)
                    ->count()
                : 0,
        ];

        return view(
            'construction-work-package-materials.index',
            array_merge(
                [
                    'mappings' => $mappings,
                    'selectedWorkPackage' => $selectedWorkPackage,
                    'rootId' => $rootId,
                    'workPackageId' => $workPackageId,
                    'materialGroup' => $materialGroup,
                    'search' => $search,
                    'status' => $status,
                    'summary' => $summary,
                ],
                $this->formData($rootId, $workPackageId)
            )
        );
    }

    /**
     * Add one or more Material Types to a Work Package.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'construction_work_package_id' => [
                'required',
                'integer',
                'exists:construction_work_packages,id',
            ],

            'material_type_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'material_type_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:material_types,id',
            ],

            'make_first_preferred' => [
                'nullable',
                'boolean',
            ],
        ]);

        $workPackage = ConstructionWorkPackage::query()
            ->child()
            ->findOrFail(
                $validated['construction_work_package_id']
            );

        $materialTypeIds = collect(
            $validated['material_type_ids']
        )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $createdCount = 0;
        $reactivatedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $workPackage,
            $materialTypeIds,
            $request,
            &$createdCount,
            &$reactivatedCount,
            &$skippedCount
        ): void {
            $nextSortOrder = (
                ConstructionWorkPackageMaterial::query()
                    ->where(
                        'construction_work_package_id',
                        $workPackage->id
                    )
                    ->max('sort_order') ?? 0
            );

            foreach ($materialTypeIds as $index => $materialTypeId) {
                $mapping = ConstructionWorkPackageMaterial::query()
                    ->where(
                        'construction_work_package_id',
                        $workPackage->id
                    )
                    ->where(
                        'material_type_id',
                        $materialTypeId
                    )
                    ->first();

                if ($mapping) {
                    if (! $mapping->is_active) {
                        $oldValues = $this->auditValues($mapping);

                        $mapping->update([
                            'is_active' => true,
                        ]);

                        $mapping->load([
                            'workPackage',
                            'materialType',
                        ]);

                        $this->auditStatusChanged(
                            'Construction Work Package Materials',
                            'ConstructionWorkPackageMaterial',
                            $mapping->id,
                            $this->mappingName($mapping),
                            true,
                            $oldValues,
                            $this->auditValues($mapping)
                        );

                        $reactivatedCount++;
                    } else {
                        $skippedCount++;
                    }

                    continue;
                }

                $nextSortOrder += 10;

                $mapping = ConstructionWorkPackageMaterial::create([
                    'construction_work_package_id' =>
                        $workPackage->id,

                    'material_type_id' =>
                        $materialTypeId,

                    'is_preferred' => false,

                    'sort_order' =>
                        $nextSortOrder,

                    'is_active' => true,
                ]);

                /*
                 * Optional convenience:
                 * the first newly-created material may be made preferred.
                 *
                 * The setPreferredMapping() method guarantees that the
                 * Material Type has only one preferred Work Package.
                 */
                if (
                    $request->boolean('make_first_preferred')
                    && $index === 0
                ) {
                    $this->setPreferredMapping($mapping);

                    $mapping->refresh();
                }

                $mapping->load([
                    'workPackage',
                    'materialType',
                ]);

                $this->auditCreated(
                    'Construction Work Package Materials',
                    'ConstructionWorkPackageMaterial',
                    $mapping->id,
                    $this->mappingName($mapping),
                    $this->auditValues($mapping)
                );

                $createdCount++;
            }
        });

        $messageParts = [];

        if ($createdCount > 0) {
            $messageParts[] =
                $createdCount . ' material mapping(s) added';
        }

        if ($reactivatedCount > 0) {
            $messageParts[] =
                $reactivatedCount . ' mapping(s) reactivated';
        }

        if ($skippedCount > 0) {
            $messageParts[] =
                $skippedCount . ' existing mapping(s) unchanged';
        }

        $message = $messageParts
            ? implode(', ', $messageParts) . '.'
            : 'No mapping changes were required.';

        return redirect()
            ->route(
                'construction-work-package-materials.index',
                [
                    'root_id' => $workPackage->parent_id,
                    'work_package_id' => $workPackage->id,
                ]
            )
            ->with('success', $message);
    }

    /**
     * Update mapping sort order.
     */
    public function update(
        Request $request,
        ConstructionWorkPackageMaterial $mapping
    ): RedirectResponse {
        $validated = $request->validate([
            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
        ]);

        $mapping->load([
            'workPackage',
            'materialType',
        ]);

        $oldValues = $this->auditValues($mapping);

        $mapping->update([
            'sort_order' => $validated['sort_order'],
        ]);

        $mapping->refresh()->load([
            'workPackage',
            'materialType',
        ]);

        $this->auditUpdated(
            'Construction Work Package Materials',
            'ConstructionWorkPackageMaterial',
            $mapping->id,
            $this->mappingName($mapping),
            $oldValues,
            $this->auditValues($mapping)
        );

        return $this->redirectToMapping(
            $mapping,
            'Mapping order updated successfully.'
        );
    }

    /**
     * Toggle mapping active/inactive status.
     */
    public function toggleStatus(
        ConstructionWorkPackageMaterial $mapping
    ): RedirectResponse {
        $mapping->load([
            'workPackage',
            'materialType',
        ]);

        $oldValues = $this->auditValues($mapping);

        $newStatus = ! $mapping->is_active;

        $mapping->update([
            'is_active' => $newStatus,
        ]);

        /*
         * An inactive mapping must never remain preferred.
         */
        if (! $newStatus && $mapping->is_preferred) {
            $mapping->update([
                'is_preferred' => false,
            ]);
        }

        $mapping->refresh()->load([
            'workPackage',
            'materialType',
        ]);

        $this->auditStatusChanged(
            'Construction Work Package Materials',
            'ConstructionWorkPackageMaterial',
            $mapping->id,
            $this->mappingName($mapping),
            $mapping->is_active,
            $oldValues,
            $this->auditValues($mapping)
        );

        return $this->redirectToMapping(
            $mapping,
            $mapping->is_active
                ? 'Material mapping activated successfully.'
                : 'Material mapping deactivated successfully.'
        );
    }

    /**
     * Make this Work Package the globally preferred package
     * for the selected Material Type.
     */
    public function makePreferred(
        ConstructionWorkPackageMaterial $mapping
    ): RedirectResponse {
        if (! $mapping->is_active) {
            return $this->redirectToMapping(
                $mapping,
                'Inactive mappings cannot be marked as preferred.',
                'error'
            );
        }

        DB::transaction(function () use ($mapping): void {
            $mapping->load([
                'workPackage',
                'materialType',
            ]);

            $oldPreferredMappings =
                ConstructionWorkPackageMaterial::query()
                    ->where(
                        'material_type_id',
                        $mapping->material_type_id
                    )
                    ->where('is_preferred', true)
                    ->where('id', '!=', $mapping->id)
                    ->get();

            foreach ($oldPreferredMappings as $oldPreferred) {
                $oldPreferred->load([
                    'workPackage',
                    'materialType',
                ]);

                $oldValues = $this->auditValues(
                    $oldPreferred
                );

                $oldPreferred->update([
                    'is_preferred' => false,
                ]);

                $oldPreferred->refresh();

                $this->auditUpdated(
                    'Construction Work Package Materials',
                    'ConstructionWorkPackageMaterial',
                    $oldPreferred->id,
                    $this->mappingName($oldPreferred),
                    $oldValues,
                    $this->auditValues($oldPreferred)
                );
            }

            $oldValues = $this->auditValues($mapping);

            $this->setPreferredMapping($mapping);

            $mapping->refresh()->load([
                'workPackage',
                'materialType',
            ]);

            $this->auditUpdated(
                'Construction Work Package Materials',
                'ConstructionWorkPackageMaterial',
                $mapping->id,
                $this->mappingName($mapping),
                $oldValues,
                $this->auditValues($mapping)
            );
        });

        return $this->redirectToMapping(
            $mapping,
            'Preferred Work Package updated for this Material Type.'
        );
    }

    /**
     * Shared data for mapping workspace.
     */
    private function formData(
        ?int $rootId = null,
        ?int $workPackageId = null
    ): array {
        $rootPackages = ConstructionWorkPackage::query()
            ->root()
            ->active()
            ->withCount([
                'activeChildren as active_children_count',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'sort_order',
            ]);

        $workPackagesQuery = ConstructionWorkPackage::query()
            ->child()
            ->active()
            ->with([
                'parent:id,code,name',
            ])
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($rootId) {
            $workPackagesQuery->where(
                'parent_id',
                $rootId
            );
        }

        $workPackages = $workPackagesQuery->get([
            'id',
            'parent_id',
            'code',
            'name',
            'sort_order',
        ]);

        $materialGroups = MaterialType::query()
            ->whereNotNull('material_group')
            ->where('material_group', '!=', '')
            ->distinct()
            ->orderBy('material_group')
            ->pluck('material_group');

        /*
         * Material picker:
         *
         * We load active Material Types and mark mappings in the Blade.
         * With the current 177 materials this is intentionally simple and
         * fast. Later this can become AJAX search without changing the
         * underlying mapping architecture.
         */
        $materialTypes = MaterialType::query()
            ->active()
            ->with('unit')
            ->ordered()
            ->get([
                'id',
                'material_group',
                'material_type_name',
                'material_type_code',
                'unit_master_id',
                'sequence',
                'is_active',
            ]);

        $mappedMaterialTypeIds = collect();

        if ($workPackageId) {
            $mappedMaterialTypeIds =
                ConstructionWorkPackageMaterial::query()
                    ->where(
                        'construction_work_package_id',
                        $workPackageId
                    )
                    ->where('is_active', true)
                    ->pluck('material_type_id');
        }

        return compact(
            'rootPackages',
            'workPackages',
            'materialGroups',
            'materialTypes',
            'mappedMaterialTypeIds'
        );
    }

    /**
     * Enforce one globally preferred Work Package per Material Type.
     */
    private function setPreferredMapping(
        ConstructionWorkPackageMaterial $mapping
    ): void {
        ConstructionWorkPackageMaterial::query()
            ->where(
                'material_type_id',
                $mapping->material_type_id
            )
            ->whereKeyNot($mapping->id)
            ->update([
                'is_preferred' => false,
            ]);

        $mapping->update([
            'is_preferred' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Values stored in the master audit log.
     */
    private function auditValues(
        ConstructionWorkPackageMaterial $mapping
    ): array {
        return [
            'id' => $mapping->id,

            'construction_work_package_id' =>
                $mapping->construction_work_package_id,

            'work_package_code' =>
                $mapping->workPackage?->code,

            'work_package_name' =>
                $mapping->workPackage?->name,

            'material_type_id' =>
                $mapping->material_type_id,

            'material_type_name' =>
                $mapping->materialType?->material_type_name,

            'material_group' =>
                $mapping->materialType?->material_group,

            'is_preferred' =>
                $mapping->is_preferred,

            'sort_order' =>
                $mapping->sort_order,

            'is_active' =>
                $mapping->is_active,
        ];
    }

    /**
     * Human-readable audit label.
     */
    private function mappingName(
        ConstructionWorkPackageMaterial $mapping
    ): string {
        $packageName = $mapping->workPackage?->name
            ?? 'Work Package #' .
                $mapping->construction_work_package_id;

        $materialName =
            $mapping->materialType?->material_type_name
            ?? 'Material Type #' .
                $mapping->material_type_id;

        return $packageName . ' → ' . $materialName;
    }

    /**
     * Return to the selected Work Package mapping workspace.
     */
    private function redirectToMapping(
        ConstructionWorkPackageMaterial $mapping,
        string $message,
        string $flashKey = 'success'
    ): RedirectResponse {
        $mapping->loadMissing('workPackage');

        return redirect()
            ->route(
                'construction-work-package-materials.index',
                [
                    'root_id' =>
                        $mapping->workPackage?->parent_id,

                    'work_package_id' =>
                        $mapping->construction_work_package_id,
                ]
            )
            ->with($flashKey, $message);
    }
    
}