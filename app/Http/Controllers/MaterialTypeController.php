<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\ActivityDivision;
use App\Models\BrandMaster;
use App\Models\MaterialProductBrand;
use App\Models\MaterialProductGroup;
use App\Models\MaterialProductType;
use App\Models\MaterialType;
use App\Models\UnitMaster;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaterialType::query()
            ->with(['productGroup', 'productType', 'unit', 'creator']);

        if ($request->filled('activity_division_id')) {
            $divisionId = $request->integer('activity_division_id');

            $query->whereExists(function (QueryBuilder $subQuery) use ($divisionId) {
                $subQuery
                    ->selectRaw('1')
                    ->from('material_product_usage_mappings as usage')
                    ->whereColumn('usage.material_type_id', 'material_types.id')
                    ->where('usage.activity_division_id', $divisionId)
                    ->where('usage.is_active', 1);
            });
        }

        if ($request->filled('material_product_group_id')) {
            $query->where(
                'material_types.material_product_group_id',
                $request->integer('material_product_group_id')
            );
        }

        if ($request->filled('material_product_type_id')) {
            $query->where(
                'material_types.material_product_type_id',
                $request->integer('material_product_type_id')
            );
        }

        if ($request->filled('inventory_type')) {
            $query->where(
                'material_types.inventory_type',
                trim((string) $request->input('inventory_type'))
            );
        }

        if ($request->filled('master_status')) {
            $query->where(
                'material_types.master_status',
                trim((string) $request->input('master_status'))
            );
        }

        $scope = trim((string) $request->input('catalogue_scope', 'canonical'));

        match ($scope) {
            '', 'canonical' => $query->where('material_types.is_legacy', 0),
            'catalogue' => $query->whereNotNull('material_types.catalogue_source_code'),
            'manual' => $query
                ->whereNull('material_types.catalogue_source_code')
                ->where('material_types.is_legacy', 0),
            'legacy' => $query->where('material_types.is_legacy', 1),
            'all' => null,
            default => $query->where('material_types.is_legacy', 0),
        };

        if ($request->filled('unit_master_id')) {
            $query->where(
                'material_types.unit_master_id',
                $request->integer('unit_master_id')
            );
        }

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';

            $query->where(function ($searchQuery) use ($like) {
                $searchQuery
                    ->whereRaw('LOWER(material_types.material_type_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(material_types.material_type_code, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(material_types.catalogue_source_code, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(material_types.material_group, "")) LIKE ?', [$like])
                    ->orWhereExists(function (QueryBuilder $subQuery) use ($like) {
                        $subQuery
                            ->selectRaw('1')
                            ->from('material_product_groups as groups')
                            ->whereColumn('groups.id', 'material_types.material_product_group_id')
                            ->where(function (QueryBuilder $groupSearch) use ($like) {
                                $groupSearch
                                    ->whereRaw('LOWER(groups.group_name) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(groups.group_code) LIKE ?', [$like]);
                            });
                    })
                    ->orWhereExists(function (QueryBuilder $subQuery) use ($like) {
                        $subQuery
                            ->selectRaw('1')
                            ->from('material_product_types as types')
                            ->whereColumn('types.id', 'material_types.material_product_type_id')
                            ->where(function (QueryBuilder $typeSearch) use ($like) {
                                $typeSearch
                                    ->whereRaw('LOWER(types.type_name) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(types.type_code) LIKE ?', [$like]);
                            });
                    })
                    ->orWhereExists(function (QueryBuilder $subQuery) use ($like) {
                        $subQuery
                            ->selectRaw('1')
                            ->from('material_search_aliases as aliases')
                            ->whereColumn('aliases.material_type_id', 'material_types.id')
                            ->where('aliases.is_active', 1)
                            ->where(function (QueryBuilder $aliasSearch) use ($like) {
                                $aliasSearch
                                    ->whereRaw('LOWER(aliases.alias) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(aliases.normalized_alias) LIKE ?', [$like]);
                            });
                    });
            });
        }

        if ($request->filled('status')) {
    $query->where(
        'material_types.is_active',
        $request->boolean('status')
    );
}

        $filteredProducts = (clone $query)->count();

        $filteredActiveProducts = (clone $query)
            ->where('material_types.is_active', 1)
            ->count();

        $filteredGroups = (clone $query)
            ->whereNotNull('material_types.material_product_group_id')
            ->distinct()
            ->count('material_types.material_product_group_id');

        $totalCanonicalProducts = MaterialType::query()
            ->where('is_legacy', 0)
            ->count();

        if ($search !== '') {
            $query->orderByRaw(
                'CASE
                    WHEN LOWER(material_types.material_type_name) = LOWER(?) THEN 0
                    WHEN LOWER(material_types.material_type_name) LIKE LOWER(?) THEN 1
                    ELSE 2
                 END',
                [$search, $search . '%']
            );
        }

        $materialTypes = $query
            ->orderByDesc('material_types.is_active')
            ->orderBy('material_types.material_product_group_id')
            ->orderBy('material_types.material_product_type_id')
            ->orderBy('material_types.sequence')
            ->orderBy('material_types.material_type_name')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view(
            'material-types.index',
            array_merge(
                compact(
                    'materialTypes',
                    'totalCanonicalProducts',
                    'filteredProducts',
                    'filteredActiveProducts',
                    'filteredGroups'
                ),
                $this->formData()
            )
        );
    }

    public function create(): View
    {
        return view('material-types.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMaterialType($request);

        $group = MaterialProductGroup::query()
            ->where('is_active', true)
            ->findOrFail($validated['material_product_group_id']);

        MaterialProductType::query()
            ->where('is_active', true)
            ->whereKey($validated['material_product_type_id'])
            ->where('material_product_group_id', $group->id)
            ->firstOrFail();

        $validated['material_group'] = $group->group_name;
        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = true;
        $validated['is_legacy'] = false;
        $validated['catalogue_source_code'] = null;
        $validated['created_by'] = auth()->id();

        $materialType = MaterialType::create($validated);

        AuditHelper::log(
            'Product Master',
            'Created',
            'MaterialType',
            $materialType->id,
            'Product created: ' . $materialType->material_type_name,
            null,
            $this->auditValues(
                $materialType->fresh(['productGroup', 'productType', 'unit'])
            )
        );

        return redirect()
            ->route('material-types.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(MaterialType $materialType): View
    {
        $materialType->load([
            'productGroup',
            'productType',
            'unit',
            'creator',
            'specifications',
            'grades',
            'variants',
            'searchAliases',
            'usageMappings',
            'productBrandMappings.brand',
        ]);

        $availableBrands = BrandMaster::query()
            ->where('is_active', true)
            ->whereDoesntHave('productMappings', function ($query) use ($materialType) {
                $query->where('material_type_id', $materialType->id);
            })
            ->orderBy('brand_name')
            ->get();

        return view('material-types.show', compact('materialType', 'availableBrands'));
    }

    public function storeBrandMapping(Request $request, MaterialType $materialType): RedirectResponse
    {
        $validated = $request->validate([
            'brand_master_id' => [
                'required', 'integer',
                Rule::exists('brand_masters', 'id')->where(fn ($q) => $q->where('is_active', true)),
                Rule::unique('material_product_brand', 'brand_master_id')
                    ->where(fn ($q) => $q->where('material_type_id', $materialType->id)),
            ],
            'is_preferred' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $brand = BrandMaster::query()->where('is_active', true)->findOrFail($validated['brand_master_id']);

        DB::transaction(function () use ($validated, $request, $materialType, $brand) {
            $mapping = MaterialProductBrand::create([
                'material_type_id' => $materialType->id,
                'brand_master_id' => $brand->id,
                'is_preferred' => $request->boolean('is_preferred'),
                'sort_order' => $validated['sort_order'] ?? 0,
                'is_active' => true,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            AuditHelper::log(
                'Product Brand Mapping', 'Created', 'MaterialProductBrand', $mapping->id,
                'Brand assigned to product: '.$brand->brand_name.' → '.$materialType->material_type_name,
                null, $this->brandMappingAuditValues($mapping->fresh(['product', 'brand']))
            );
        });

        return redirect()->route('material-types.show', $materialType)
            ->with('success', 'Brand '.$brand->brand_name.' assigned successfully.');
    }

    public function updateBrandMapping(
        Request $request,
        MaterialType $materialType,
        MaterialProductBrand $materialProductBrand
    ): RedirectResponse {
        $this->ensureBrandMappingBelongsToProduct($materialType, $materialProductBrand);

        $validated = $request->validate([
            'is_preferred' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated, $request, $materialProductBrand) {
            $old = $this->brandMappingAuditValues($materialProductBrand->load(['product', 'brand']));

            $materialProductBrand->update([
                'is_preferred' => $request->boolean('is_preferred'),
                'sort_order' => $validated['sort_order'] ?? 0,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $materialProductBrand->refresh()->load(['product', 'brand']);

            AuditHelper::log(
                'Product Brand Mapping', 'Updated', 'MaterialProductBrand', $materialProductBrand->id,
                'Product–Brand mapping updated: '.($materialProductBrand->product?->material_type_name ?? '-')
                    .' ↔ '.($materialProductBrand->brand?->brand_name ?? '-'),
                $old, $this->brandMappingAuditValues($materialProductBrand)
            );
        });

        return redirect()->route('material-types.show', $materialType)
            ->with('success', 'Brand association updated successfully.');
    }

    public function toggleBrandMapping(
        MaterialType $materialType,
        MaterialProductBrand $materialProductBrand
    ): RedirectResponse {
        $this->ensureBrandMappingBelongsToProduct($materialType, $materialProductBrand);

        DB::transaction(function () use ($materialProductBrand) {
            $old = $this->brandMappingAuditValues($materialProductBrand->load(['product', 'brand']));
            $materialProductBrand->update(['is_active' => ! $materialProductBrand->is_active]);
            $materialProductBrand->refresh()->load(['product', 'brand']);

            AuditHelper::log(
                'Product Brand Mapping',
                $materialProductBrand->is_active ? 'Activated' : 'Deactivated',
                'MaterialProductBrand', $materialProductBrand->id,
                ($materialProductBrand->is_active ? 'Product–Brand mapping activated: ' : 'Product–Brand mapping deactivated: ')
                    .($materialProductBrand->product?->material_type_name ?? '-')
                    .' ↔ '.($materialProductBrand->brand?->brand_name ?? '-'),
                $old, $this->brandMappingAuditValues($materialProductBrand)
            );
        });

        return redirect()->route('material-types.show', $materialType)
            ->with('success', 'Brand association status updated successfully.');
    }

    public function edit(MaterialType $materialType): View
    {
        $materialType->load(['productGroup', 'productType', 'unit']);

        return view(
            'material-types.edit',
            array_merge(
                compact('materialType'),
                $this->formData()
            )
        );
    }

    public function update(Request $request, MaterialType $materialType): RedirectResponse
    {
        $validated = $this->validateMaterialType($request, $materialType);

        $group = MaterialProductGroup::query()
            ->where('is_active', true)
            ->findOrFail($validated['material_product_group_id']);

        MaterialProductType::query()
            ->where('is_active', true)
            ->whereKey($validated['material_product_type_id'])
            ->where('material_product_group_id', $group->id)
            ->firstOrFail();

        $validated['material_group'] = $group->group_name;
        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['catalogue_source_code'], $validated['is_legacy']);

        $oldValues = $this->auditValues(
            $materialType->load(['productGroup', 'productType', 'unit'])
        );

        $materialType->update($validated);

        AuditHelper::log(
            'Product Master',
            'Updated',
            'MaterialType',
            $materialType->id,
            'Product updated: ' . $materialType->material_type_name,
            $oldValues,
            $this->auditValues(
                $materialType->fresh(['productGroup', 'productType', 'unit'])
            )
        );

        return redirect()
            ->route('material-types.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(MaterialType $materialType): RedirectResponse
    {
        $oldValues = $this->auditValues(
            $materialType->load(['productGroup', 'productType', 'unit'])
        );

        $materialType->update([
            'is_active' => ! $materialType->is_active,
        ]);

        $materialType->refresh();

        AuditHelper::log(
            'Product Master',
            $materialType->is_active ? 'Activated' : 'Deactivated',
            'MaterialType',
            $materialType->id,
            $materialType->is_active
                ? 'Product activated: ' . $materialType->material_type_name
                : 'Product deactivated: ' . $materialType->material_type_name,
            $oldValues,
            $this->auditValues(
                $materialType->load(['productGroup', 'productType', 'unit'])
            )
        );

        return back()->with('success', 'Product status updated successfully.');
    }

    private function formData(): array
    {
        $activityDivisions = ActivityDivision::query()
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();

        $productGroups = MaterialProductGroup::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('group_name')
            ->get();

        $productTypes = MaterialProductType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('type_name')
            ->get();

        $units = UnitMaster::query()
            ->where('is_active', true)
            ->orderBy('unit_name')
            ->get();

        $inventoryTypes = MaterialType::query()
            ->whereNotNull('inventory_type')
            ->where('inventory_type', '!=', '')
            ->distinct()
            ->orderBy('inventory_type')
            ->pluck('inventory_type');

        $masterStatuses = MaterialType::query()
            ->whereNotNull('master_status')
            ->where('master_status', '!=', '')
            ->distinct()
            ->orderBy('master_status')
            ->pluck('master_status');

        if (! $masterStatuses->contains('Approved')) {
            $masterStatuses->push('Approved');
        }

        if (! $masterStatuses->contains('Review')) {
            $masterStatuses->push('Review');
        }

        $divisionGroupMap = DB::table('material_product_usage_mappings as usage')
            ->join('material_types as products', 'products.id', '=', 'usage.material_type_id')
            ->where('usage.is_active', true)
            ->where('products.is_active', true)
            ->where('products.is_legacy', false)
            ->whereNotNull('usage.activity_division_id')
            ->whereNotNull('products.material_product_group_id')
            ->select(
                'usage.activity_division_id',
                'products.material_product_group_id'
            )
            ->distinct()
            ->get()
            ->groupBy('activity_division_id')
            ->map(fn ($rows) => $rows
                ->pluck('material_product_group_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all()
            )
            ->toArray();

        return compact(
            'activityDivisions',
            'productGroups',
            'productTypes',
            'units',
            'inventoryTypes',
            'masterStatuses',
            'divisionGroupMap'
        );
    }

    private function validateMaterialType(
        Request $request,
        ?MaterialType $materialType = null
    ): array {
        return $request->validate([
            'material_product_group_id' => [
                'required',
                'integer',
                'exists:material_product_groups,id',
            ],
            'material_product_type_id' => [
                'required',
                'integer',
                Rule::exists('material_product_types', 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'material_product_group_id',
                            $request->integer('material_product_group_id')
                        )
                    ),
            ],
            'material_type_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('material_types', 'material_type_name')
                    ->ignore($materialType?->id),
            ],
            'material_type_code' => ['nullable', 'string', 'max:100'],
            'unit_master_id' => ['required', 'integer', 'exists:unit_masters,id'],
            'inventory_type' => ['required', 'string', 'max:100'],
            'master_status' => ['required', 'string', 'max:50'],
            'sequence' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function ensureBrandMappingBelongsToProduct(
        MaterialType $materialType,
        MaterialProductBrand $materialProductBrand
    ): void {
        abort_unless(
            (int) $materialProductBrand->material_type_id === (int) $materialType->id,
            404
        );
    }

    private function brandMappingAuditValues(MaterialProductBrand $mapping): array
    {
        return [
            'id' => $mapping->id,
            'material_type_id' => $mapping->material_type_id,
            'product' => $mapping->product?->material_type_name,
            'brand_master_id' => $mapping->brand_master_id,
            'brand' => $mapping->brand?->brand_name,
            'is_preferred' => $mapping->is_preferred,
            'sort_order' => $mapping->sort_order,
            'is_active' => $mapping->is_active,
            'remarks' => $mapping->remarks,
        ];
    }

    private function auditValues(MaterialType $materialType): array
    {
        return [
            'id' => $materialType->id,
            'material_product_group_id' => $materialType->material_product_group_id,
            'product_group' => $materialType->productGroup?->group_name,
            'material_product_type_id' => $materialType->material_product_type_id,
            'product_type' => $materialType->productType?->type_name,
            'material_group' => $materialType->material_group,
            'material_type_name' => $materialType->material_type_name,
            'material_type_code' => $materialType->material_type_code,
            'catalogue_source_code' => $materialType->catalogue_source_code,
            'inventory_type' => $materialType->inventory_type,
            'master_status' => $materialType->master_status,
            'is_legacy' => $materialType->is_legacy,
            'unit_master_id' => $materialType->unit_master_id,
            'unit_name' => $materialType->unit?->unit_name,
            'sequence' => $materialType->sequence,
            'is_active' => $materialType->is_active,
            'remarks' => $materialType->remarks,
            'created_by' => $materialType->created_by,
        ];
    }
}
