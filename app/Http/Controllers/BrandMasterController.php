<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\BrandMaster;
use App\Models\BrandSegment;
use App\Models\MaterialProductBrand;
use App\Models\MaterialType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandMasterController extends Controller
{
    /**
     * Display the Brand Master list.
     */
    public function index(Request $request): View
    {
        $query = BrandMaster::query()
            ->with([
                'segment',
                'materialType.unit',
            ]);

        if ($request->filled('brand_segment_id')) {
            $query->where(
                'brand_segment_id',
                $request->integer('brand_segment_id')
            );
        }

        /*
         * Legacy filters are intentionally retained temporarily so
         * existing URLs/forms do not break during the transition.
         */
        if ($request->filled('material_group')) {
            $query->whereHas(
                'materialType',
                fn (Builder $builder) => $builder->where(
                    'material_group',
                    $request->string('material_group')->toString()
                )
            );
        }

        if ($request->filled('material_type_id')) {
            $query->where(
                'material_type_id',
                $request->integer('material_type_id')
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('brand_code', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%")
                    ->orWhereHas(
                        'segment',
                        fn (Builder $segmentQuery) => $segmentQuery
                            ->where(
                                'segment_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'segment_code',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'materialType',
                        fn (Builder $typeQuery) => $typeQuery
                            ->where(
                                'material_type_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'material_group',
                                'like',
                                "%{$search}%"
                            )
                    );
            });
        }

        if (
            $request->has('status')
            && $request->status !== ''
            && $request->status !== null
        ) {
            $query->where(
                'is_active',
                $request->boolean('status')
            );
        }

        $brands = $query
            ->orderByDesc('is_active')
            ->orderByRaw(
                'CASE WHEN brand_segment_id IS NULL THEN 1 ELSE 0 END'
            )
            ->orderBy('brand_segment_id')
            ->orderBy('sequence')
            ->orderBy('brand_name')
            ->paginate(
                config('rds.pagination.per_page', 25)
            )
            ->withQueryString();

        return view(
            'brand-masters.index',
            array_merge(
                compact('brands'),
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
            'brand-masters.create',
            $this->formData()
        );
    }

    /**
     * Store a new Brand.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBrand($request);

        $validated['brand_name'] = trim(
            $validated['brand_name']
        );

        if (isset($validated['brand_code'])) {
            $validated['brand_code'] = $this->nullableTrim(
                $validated['brand_code']
            );
        }

        if (isset($validated['remarks'])) {
            $validated['remarks'] = $this->nullableTrim(
                $validated['remarks']
            );
        }

        $validated['sequence'] =
            $validated['sequence'] ?? 0;

        $validated['is_active'] = true;

        /*
         * IMPORTANT:
         * New canonical Brands are not owned by one Material Type.
         * material_type_id therefore remains NULL for new records.
         */
        $brand = BrandMaster::create($validated);

        AuditHelper::log(
            'Brand Masters',
            'Created',
            'BrandMaster',
            $brand->id,
            'Material brand created: ' . $brand->brand_name,
            null,
            $this->auditValues(
                $brand->fresh([
                    'segment',
                    'materialType',
                ])
            )
        );

        return redirect()
            ->route('brand-masters.index')
            ->with(
                'success',
                'Material brand created successfully.'
            );
    }

    /**
     * Show the edit form.
     */
    public function edit(BrandMaster $brandMaster): View
    {
        $brandMaster->load([
            'segment',
            'materialType',
            'productMappings.product.productGroup',
            'productMappings.product.productType',
            'productMappings.product.unit',
        ]);

        $availableProducts = MaterialType::query()
            ->with([
                'productGroup',
                'productType',
                'unit',
            ])
            ->where('is_active', true)
            ->where('is_legacy', false)
            ->where('master_status', 'CANONICAL')
            ->whereDoesntHave(
                'productBrandMappings',
                function (Builder $query) use ($brandMaster) {
                    $query->where(
                        'brand_master_id',
                        $brandMaster->id
                    );
                }
            )
            ->orderBy('material_group')
            ->orderBy('sequence')
            ->orderBy('material_type_name')
            ->get();

        return view(
            'brand-masters.edit',
            array_merge(
                compact(
                    'brandMaster',
                    'availableProducts'
                ),
                $this->formData()
            )
        );
    }

    /**
     * Update an existing Brand.
     */
    public function update(
        Request $request,
        BrandMaster $brandMaster
    ): RedirectResponse {
        $validated = $this->validateBrand(
            $request,
            $brandMaster
        );

        $validated['brand_name'] = trim(
            $validated['brand_name']
        );

        if (array_key_exists('brand_code', $validated)) {
            $validated['brand_code'] = $this->nullableTrim(
                $validated['brand_code']
            );
        }

        if (array_key_exists('remarks', $validated)) {
            $validated['remarks'] = $this->nullableTrim(
                $validated['remarks']
            );
        }

        $validated['sequence'] =
            $validated['sequence'] ?? 0;

        $validated['is_active'] =
            $request->boolean('is_active');

        /*
         * Existing legacy classification fields are deliberately
         * not included in validated data. Therefore editing a Brand
         * cannot erase material_type_id, material_category_id or
         * activity_id from historical records.
         */
        $oldValues = $this->auditValues(
            $brandMaster->load([
                'segment',
                'materialType',
            ])
        );

        $brandMaster->update($validated);

        AuditHelper::log(
            'Brand Masters',
            'Updated',
            'BrandMaster',
            $brandMaster->id,
            'Material brand updated: '
                . $brandMaster->brand_name,
            $oldValues,
            $this->auditValues(
                $brandMaster->fresh([
                    'segment',
                    'materialType',
                ])
            )
        );

        return redirect()
            ->route('brand-masters.index')
            ->with(
                'success',
                'Material brand updated successfully.'
            );
    }

    /**
     * Associate a canonical Product with this Brand.
     */
    public function storeProductMapping(
        Request $request,
        BrandMaster $brandMaster
    ): RedirectResponse {
        $validated = $request->validate([
            'material_type_id' => [
                'required',
                'integer',

                Rule::exists(
                    'material_types',
                    'id'
                )->where(
                    fn ($query) => $query
                        ->where('is_active', true)
                        ->where('is_legacy', false)
                        ->where(
                            'master_status',
                            'CANONICAL'
                        )
                ),

                Rule::unique(
                    'material_product_brand',
                    'material_type_id'
                )->where(
                    fn ($query) => $query->where(
                        'brand_master_id',
                        $brandMaster->id
                    )
                ),
            ],

            'is_preferred' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $product = MaterialType::query()
            ->where('is_active', true)
            ->where('is_legacy', false)
            ->where('master_status', 'CANONICAL')
            ->findOrFail(
                $validated['material_type_id']
            );

        DB::transaction(
            function () use (
                $validated,
                $request,
                $brandMaster,
                $product
            ) {
                $mapping = MaterialProductBrand::create([
                    'material_type_id' => $product->id,
                    'brand_master_id' => $brandMaster->id,
                    'is_preferred' =>
                        $request->boolean('is_preferred'),
                    'sort_order' =>
                        $validated['sort_order'] ?? 0,
                    'is_active' => true,
                    'remarks' =>
                        $validated['remarks'] ?? null,
                ]);

                AuditHelper::log(
                    'Product Brand Mapping',
                    'Created',
                    'MaterialProductBrand',
                    $mapping->id,
                    'Product assigned to brand: '
                        . $product->material_type_name
                        . ' → '
                        . $brandMaster->brand_name,
                    null,
                    $this->productMappingAuditValues(
                        $mapping->fresh([
                            'product',
                            'brand',
                        ])
                    )
                );
            }
        );

        return redirect()
            ->route(
                'brand-masters.edit',
                $brandMaster
            )
            ->with(
                'success',
                'Product '
                    . $product->material_type_name
                    . ' assigned successfully.'
            );
    }

    /**
     * Update a Product ↔ Brand association.
     */
    public function updateProductMapping(
        Request $request,
        BrandMaster $brandMaster,
        MaterialProductBrand $materialProductBrand
    ): RedirectResponse {
        $this->ensureProductMappingBelongsToBrand(
            $brandMaster,
            $materialProductBrand
        );

        $validated = $request->validate([
            'is_preferred' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(
            function () use (
                $validated,
                $request,
                $materialProductBrand
            ) {
                $old = $this->productMappingAuditValues(
                    $materialProductBrand->load([
                        'product',
                        'brand',
                    ])
                );

                $materialProductBrand->update([
                    'is_preferred' =>
                        $request->boolean('is_preferred'),
                    'sort_order' =>
                        $validated['sort_order'] ?? 0,
                    'remarks' =>
                        $validated['remarks'] ?? null,
                ]);

                $materialProductBrand
                    ->refresh()
                    ->load([
                        'product',
                        'brand',
                    ]);

                AuditHelper::log(
                    'Product Brand Mapping',
                    'Updated',
                    'MaterialProductBrand',
                    $materialProductBrand->id,
                    'Brand-first Product mapping updated: '
                        . (
                            $materialProductBrand
                                ->brand
                                ?->brand_name
                            ?? '-'
                        )
                        . ' ↔ '
                        . (
                            $materialProductBrand
                                ->product
                                ?->material_type_name
                            ?? '-'
                        ),
                    $old,
                    $this->productMappingAuditValues(
                        $materialProductBrand
                    )
                );
            }
        );

        return redirect()
            ->route(
                'brand-masters.edit',
                $brandMaster
            )
            ->with(
                'success',
                'Product association updated successfully.'
            );
    }

    /**
     * Activate/deactivate a Product ↔ Brand association.
     */
    public function toggleProductMapping(
        BrandMaster $brandMaster,
        MaterialProductBrand $materialProductBrand
    ): RedirectResponse {
        $this->ensureProductMappingBelongsToBrand(
            $brandMaster,
            $materialProductBrand
        );

        DB::transaction(
            function () use ($materialProductBrand) {
                $old = $this->productMappingAuditValues(
                    $materialProductBrand->load([
                        'product',
                        'brand',
                    ])
                );

                $materialProductBrand->update([
                    'is_active' =>
                        ! $materialProductBrand->is_active,
                ]);

                $materialProductBrand
                    ->refresh()
                    ->load([
                        'product',
                        'brand',
                    ]);

                AuditHelper::log(
                    'Product Brand Mapping',
                    $materialProductBrand->is_active
                        ? 'Activated'
                        : 'Deactivated',
                    'MaterialProductBrand',
                    $materialProductBrand->id,
                    (
                        $materialProductBrand->is_active
                            ? 'Product-Brand mapping activated: '
                            : 'Product-Brand mapping deactivated: '
                    )
                        . (
                            $materialProductBrand
                                ->brand
                                ?->brand_name
                            ?? '-'
                        )
                        . ' ↔ '
                        . (
                            $materialProductBrand
                                ->product
                                ?->material_type_name
                            ?? '-'
                        ),
                    $old,
                    $this->productMappingAuditValues(
                        $materialProductBrand
                    )
                );
            }
        );

        return redirect()
            ->route(
                'brand-masters.edit',
                $brandMaster
            )
            ->with(
                'success',
                'Product association status updated successfully.'
            );
    }

    /**
     * Activate or deactivate a Brand.
     */
    public function toggleStatus(
        BrandMaster $brandMaster
    ): RedirectResponse {
        $oldValues = $this->auditValues(
            $brandMaster->load([
                'segment',
                'materialType',
            ])
        );

        $brandMaster->update([
            'is_active' => ! $brandMaster->is_active,
        ]);

        $brandMaster->refresh()->load([
            'segment',
            'materialType',
        ]);

        AuditHelper::log(
            'Brand Masters',
            $brandMaster->is_active
                ? 'Activated'
                : 'Deactivated',
            'BrandMaster',
            $brandMaster->id,
            $brandMaster->is_active
                ? 'Material brand activated: '
                    . $brandMaster->brand_name
                : 'Material brand deactivated: '
                    . $brandMaster->brand_name,
            $oldValues,
            $this->auditValues($brandMaster)
        );

        return back()->with(
            'success',
            'Material brand status updated successfully.'
        );
    }

    /**
     * Shared Brand form/filter data.
     */
    private function formData(): array
    {
        $brandSegments = BrandSegment::query()
            ->active()
            ->ordered()
            ->get();

        /*
         * Keep legacy Material Type data available temporarily
         * because the existing index/edit Blade files still refer
         * to these variables until their UI migration is completed.
         */
        $materialTypes = MaterialType::query()
            ->with('unit')
            ->where('is_active', true)
            ->orderBy('material_group')
            ->orderBy('sequence')
            ->orderBy('material_type_name')
            ->get();

        return [
            'brandSegments' => $brandSegments,

            'materialTypes' => $materialTypes,

            'materialGroups' => $materialTypes
                ->pluck('material_group')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
        ];
    }

    /**
     * Validate canonical Brand identity.
     *
     * Brand identity is:
     *
     * Brand Segment + Brand Name
     *
     * Product applicability is handled independently through
     * material_product_brand.
     */
    private function validateBrand(
        Request $request,
        ?BrandMaster $brandMaster = null
    ): array {
        return $request->validate([
            'brand_segment_id' => [
                'required',
                'integer',

                Rule::exists(
                    'brand_segments',
                    'id'
                )->where(
                    fn ($query) => $query->where(
                        'is_active',
                        true
                    )
                ),
            ],

            'brand_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'brand_masters',
                    'brand_name'
                )
                    ->where(
                        fn ($query) => $query->where(
                            'brand_segment_id',
                            $request->integer(
                                'brand_segment_id'
                            )
                        )
                    )
                    ->ignore($brandMaster?->id),
            ],

            'brand_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'sequence' => [
                'nullable',
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
        ], [
            'brand_segment_id.required' =>
                'Please select a Brand Segment.',

            'brand_segment_id.exists' =>
                'The selected Brand Segment is invalid or inactive.',

            'brand_name.unique' =>
                'This brand already exists in the selected Brand Segment.',
        ]);
    }

    /**
     * Confirm that the mapping belongs to this Brand.
     */
    private function ensureProductMappingBelongsToBrand(
        BrandMaster $brandMaster,
        MaterialProductBrand $materialProductBrand
    ): void {
        abort_unless(
            (int) $materialProductBrand->brand_master_id
                === (int) $brandMaster->id,
            404
        );
    }

    /**
     * Product ↔ Brand mapping audit values.
     */
    private function productMappingAuditValues(
        MaterialProductBrand $mapping
    ): array {
        return [
            'id' => $mapping->id,
            'brand_master_id' =>
                $mapping->brand_master_id,
            'brand' =>
                $mapping->brand?->brand_name,
            'material_type_id' =>
                $mapping->material_type_id,
            'product' =>
                $mapping->product?->material_type_name,
            'is_preferred' =>
                $mapping->is_preferred,
            'sort_order' =>
                $mapping->sort_order,
            'is_active' =>
                $mapping->is_active,
            'remarks' =>
                $mapping->remarks,
        ];
    }

    /**
     * Brand Master audit values.
     *
     * Canonical Brand Segment and legacy Material Type are both
     * recorded so historical transitions remain auditable.
     */
    private function auditValues(
        BrandMaster $brand
    ): array {
        return [
            'id' => $brand->id,

            'brand_segment_id' =>
                $brand->brand_segment_id,

            'brand_segment_code' =>
                $brand->segment?->segment_code,

            'brand_segment_name' =>
                $brand->segment?->segment_name,

            'material_type_id' =>
                $brand->material_type_id,

            'material_type_name' =>
                $brand->materialType
                    ?->material_type_name,

            'material_group' =>
                $brand->materialType
                    ?->material_group,

            'brand_name' =>
                $brand->brand_name,

            'brand_code' =>
                $brand->brand_code,

            'sequence' =>
                $brand->sequence,

            'is_active' =>
                $brand->is_active,

            'remarks' =>
                $brand->remarks,
        ];
    }

    /**
     * Trim optional text and convert an empty string to NULL.
     */
    private function nullableTrim(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }
}