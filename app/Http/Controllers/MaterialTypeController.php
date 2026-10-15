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
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialTypeController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaterialType::query()
            ->with(['productGroup', 'productType', 'unit', 'creator']);

        // Bible classification filters are applied at SQL level before pagination.
        $bibleFilters = [
            'material_bible_trade_id' => 't.id',
            'material_bible_main_category_id' => 'mc.id',
            'material_bible_sub_category_id' => 'sc.id',
        ];
        $selectedBibleFilters = [];
        foreach ($bibleFilters as $field => $column) {
            if ($request->filled($field)) {
                $selectedBibleFilters[$column] = $request->integer($field);
            }
        }
        if ($selectedBibleFilters) {
            $query->whereExists(function (QueryBuilder $sub) use ($selectedBibleFilters) {
                $sub->selectRaw('1')
                    ->from('material_bible_product_mappings as bpm')
                    ->join('material_bible_sub_categories as sc', 'sc.id', '=', 'bpm.material_bible_sub_category_id')
                    ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
                    ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
                    ->whereColumn('bpm.material_type_id', 'material_types.id')
                    ->where('bpm.is_active', 1)
                    ->where('sc.is_active', 1)
                    ->where('mc.is_active', 1)
                    ->where('t.is_active', 1);
                foreach ($selectedBibleFilters as $column => $value) {
                    $sub->where($column, $value);
                }
            });
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
                ->where('material_types.catalogue_source_code', 'RAVION_ERP_MANUAL')
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
            // Each word may match a different field (e.g. product TMT Bar + variant 12mm).
            // Retain the existing product, classification and alias search conditions.
            $tokens = preg_split('/\s+/u', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY);
            foreach (array_unique($tokens ?: []) as $token) {
                $like = '%' . $token . '%';
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
                        $subQuery->selectRaw('1')
                            ->from('material_bible_product_mappings as bpm')
                            ->join('material_bible_sub_categories as sc', 'sc.id', '=', 'bpm.material_bible_sub_category_id')
                            ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
                            ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
                            ->whereColumn('bpm.material_type_id', 'material_types.id')
                            ->where('bpm.is_active', 1)
                            ->where(function (QueryBuilder $names) use ($like) {
                                $names->whereRaw('LOWER(t.name) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(mc.name) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(sc.name) LIKE ?', [$like]);
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
                    })
                    ->orWhereExists(function (QueryBuilder $subQuery) use ($like) {
                        $subQuery->selectRaw('1')
                            ->from('material_variants as variants')
                            ->whereColumn('variants.material_type_id', 'material_types.id')
                            ->where('variants.is_active', 1)
                            ->where(function (QueryBuilder $variantSearch) use ($like) {
                                $variantSearch
                                    ->whereRaw('LOWER(variants.variant_name) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(variants.size_dimension, "")) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(variants.finish, "")) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(variants.colour_shade, "")) LIKE ?', [$like])
                                    ->orWhereRaw('LOWER(COALESCE(variants.search_aliases, "")) LIKE ?', [$like]);
                            });
                    });
            });
            }
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

        $filteredBibleProducts = (clone $query)
            ->whereExists(function (QueryBuilder $sub) {
                $sub->selectRaw('1')->from('material_bible_product_mappings as bpm')
                    ->whereColumn('bpm.material_type_id', 'material_types.id')
                    ->where('bpm.is_active', 1);
            })->count();

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

        // Batch-load Bible paths for this page only (avoids N+1 queries).
        $pageIds = $materialTypes->getCollection()->pluck('id')->all();
        $biblePaths = collect();
        if ($pageIds) {
            $biblePaths = DB::table('material_bible_product_mappings as bpm')
                ->join('material_bible_sub_categories as sc', 'sc.id', '=', 'bpm.material_bible_sub_category_id')
                ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
                ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
                ->whereIn('bpm.material_type_id', $pageIds)
                ->where('bpm.is_active', 1)
                ->where('sc.is_active', 1)
                ->where('mc.is_active', 1)
                ->where('t.is_active', 1)
                ->select('bpm.material_type_id', 't.name as trade_name', 'mc.name as main_name', 'sc.name as sub_name', 'sc.id as sub_id')
                ->distinct()
                ->orderBy('t.name')->orderBy('mc.name')->orderBy('sc.name')
                ->get()->groupBy('material_type_id');
        }

        return view(
            'material-types.index',
            array_merge(
                compact(
                    'materialTypes',
                    'totalCanonicalProducts',
                    'filteredProducts',
                    'filteredActiveProducts',
                    'filteredBibleProducts',
                    'biblePaths'
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

        // Bible taxonomy is authoritative for new products; legacy group/type stay null.
        $validated['material_product_group_id'] = null;
        $validated['material_product_type_id'] = null;
        $validated['material_group'] = null;
        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = true;
        $validated['is_legacy'] = false;
        // Distinguish new ERP products from historical records with a null source.
        $validated['catalogue_source_code'] = 'RAVION_ERP_MANUAL';
        $validated['created_by'] = auth()->id();

        $bibleSubCategoryId = $this->validatedBibleSubCategory($request, true);
        $materialType = DB::transaction(function () use ($validated, $bibleSubCategoryId) {
            $product = MaterialType::create($validated);
            if ($bibleSubCategoryId !== null) {
                $this->addBibleProductMapping($product->id, $bibleSubCategoryId);
            }
            return $product;
        });

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
            'variants.unit',
            'searchAliases',
            'usageMappings',
            'productBrandMappings.brand.segment',
        ]);

        // Original Bible hierarchy, including multiple valid paths per Item.
        // Variant-scoped mappings retain the exact path of each imported combination.
        $bibleMappings = DB::table('material_bible_product_mappings as bpm')
            ->join('material_bible_sub_categories as sc', 'sc.id', '=', 'bpm.material_bible_sub_category_id')
            ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
            ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
            ->where('bpm.material_type_id', $materialType->id)
            ->where('bpm.is_active', 1)
            ->where('sc.is_active', 1)
            ->where('mc.is_active', 1)
            ->where('t.is_active', 1)
            ->select([
                'bpm.id as mapping_id',
                'bpm.material_variant_id',
                'bpm.variant_scope_key',
                'bpm.source_code',
                't.id as trade_id',
                't.name as trade_name',
                'mc.id as main_id',
                'mc.name as main_name',
                'sc.id as sub_id',
                'sc.name as sub_name',
            ])
            ->orderBy('t.name')
            ->orderBy('mc.name')
            ->orderBy('sc.name')
            ->get();

        $biblePaths = $bibleMappings
            ->unique(fn ($mapping) => $mapping->trade_id . ':' . $mapping->main_id . ':' . $mapping->sub_id)
            ->values();

        $bibleVariantMappings = $bibleMappings
            ->filter(fn ($mapping) => $mapping->material_variant_id !== null)
            ->groupBy('material_variant_id');

        $availableBrands = BrandMaster::query()
            ->with('segment')
            ->where('is_active', true)
            ->whereDoesntHave('productMappings', function ($query) use ($materialType) {
                $query->where('material_type_id', $materialType->id);
            })
            ->orderByRaw('CASE WHEN brand_segment_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('brand_segment_id')
            ->orderBy('brand_name')
            ->get();

        return view('material-types.show', compact(
            'materialType',
            'availableBrands',
            'biblePaths',
            'bibleVariantMappings'
        ));
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

        $existingBiblePaths = DB::table('material_bible_product_mappings as bpm')
            ->join('material_bible_sub_categories as sc', 'sc.id', '=', 'bpm.material_bible_sub_category_id')
            ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
            ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
            ->where('bpm.material_type_id', $materialType->id)
            ->where('bpm.is_active', 1)
            ->select('t.id as trade_id', 't.name as trade_name', 'mc.id as main_id',
                'mc.name as main_name', 'sc.id as sub_id', 'sc.name as sub_name')
            ->distinct()
            ->orderBy('t.name')->orderBy('mc.name')->orderBy('sc.name')
            ->get();

        return view(
            'material-types.edit',
            array_merge(
                compact('materialType', 'existingBiblePaths'),
                $this->formData()
            )
        );
    }

    public function update(Request $request, MaterialType $materialType): RedirectResponse
    {
        $validated = $this->validateMaterialType($request, $materialType);

        // Preserve any existing legacy group/type fields on edit.
        $validated['sequence'] = $validated['sequence'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['catalogue_source_code'], $validated['is_legacy']);

        $oldValues = $this->auditValues(
            $materialType->load(['productGroup', 'productType', 'unit'])
        );

        // Legacy edit forms do not send Bible category fields: retain all mappings.
        $bibleSubCategoryId = $this->validatedBibleSubCategory($request);
        DB::transaction(function () use ($materialType, $validated, $bibleSubCategoryId) {
            $materialType->update($validated);
            if ($bibleSubCategoryId !== null) {
                $this->addBibleProductMapping($materialType->id, $bibleSubCategoryId);
            }
        });

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

        $bibleTrades = DB::table('material_bible_trades')
            ->where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get();
        $bibleMainCategories = DB::table('material_bible_main_categories')
            ->where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get();
        $bibleSubCategories = DB::table('material_bible_sub_categories')
            ->where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get();

        return compact(
            'bibleTrades', 'bibleMainCategories', 'bibleSubCategories',
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

    /**
     * Required for new products; optional for edits of existing products. When provided,
     * validate the full Trade -> Main -> Sub relationship, not just the sub ID.
     */
    private function validatedBibleSubCategory(Request $request, bool $required = false): ?int
    {
        $fields = ['material_bible_trade_id', 'material_bible_main_category_id', 'material_bible_sub_category_id'];
        $present = collect($fields)->contains(fn ($field) => $request->exists($field));
        if (!$present && !$required) {
            return null;
        }

        $data = $request->validate([
            'material_bible_trade_id' => ['required', 'integer', Rule::exists('material_bible_trades', 'id')->where('is_active', 1)],
            'material_bible_main_category_id' => ['required', 'integer', Rule::exists('material_bible_main_categories', 'id')->where('is_active', 1)],
            'material_bible_sub_category_id' => ['required', 'integer', Rule::exists('material_bible_sub_categories', 'id')->where('is_active', 1)],
        ]);

        $valid = DB::table('material_bible_sub_categories as sub')
            ->join('material_bible_main_categories as main', 'main.id', '=', 'sub.material_bible_main_category_id')
            ->join('material_bible_trades as trade', 'trade.id', '=', 'main.material_bible_trade_id')
            ->where('sub.id', $data['material_bible_sub_category_id'])
            ->where('main.id', $data['material_bible_main_category_id'])
            ->where('trade.id', $data['material_bible_trade_id'])
            ->where('sub.is_active', 1)
            ->where('main.is_active', 1)
            ->where('trade.is_active', 1)
            ->exists();

        if (!$valid) {
            throw ValidationException::withMessages([
                'material_bible_sub_category_id' => 'The selected Bible category path is invalid.',
            ]);
        }

        return (int) $data['material_bible_sub_category_id'];
    }

    /** Add a product-level path; never remove imported or other existing paths. */
    private function addBibleProductMapping(int $materialTypeId, int $subCategoryId): void
    {
        $exists = DB::table('material_bible_product_mappings')
            ->where('material_bible_sub_category_id', $subCategoryId)
            ->where('material_type_id', $materialTypeId)
            ->where('variant_scope_key', 0)
            ->exists();

        if (!$exists) {
            DB::table('material_bible_product_mappings')->insert([
                'material_bible_sub_category_id' => $subCategoryId,
                'material_type_id' => $materialTypeId,
                'material_variant_id' => null,
                'variant_scope_key' => 0,
                'source_code' => 'RAVION_ERP_MANUAL',
                'is_active' => 1,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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
