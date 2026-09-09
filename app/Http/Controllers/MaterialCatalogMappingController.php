<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MaterialCatalogCategory;
use App\Models\MaterialCatalogItem;
use App\Models\MaterialCatalogSubcategory;
use App\Models\MaterialType;
use App\Models\UnitMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialCatalogMappingController extends Controller
{
    /**
     * Main Material Catalogue mapping page.
     *
     * Admin / PMO can:
     * 1. Search imported catalogue products.
     * 2. Map a catalogue item to an existing Material Type.
     * 3. Create a new Material Type from the catalogue item.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $categoryId = $request->integer('category_id') ?: null;
        $subcategoryId = $request->integer('subcategory_id') ?: null;
        $mappingStatus = $request->input('mapping_status', 'all');

        $query = MaterialCatalogItem::query()
            ->with([
                'category:id,code,name',
                'subcategory:id,material_catalog_category_id,code,name',
                'matchedMaterialType:id,material_type_name,material_type_code,material_group,unit_master_id,is_active',
                'matchedMaterialType.unit:id,unit_name,unit_code,symbol',
                'suggestedUnit:id,unit_name,unit_code,symbol',
            ])
            ->where('is_active', true);

        if ($search !== '') {
    $normalizedSearch = $this->normalizeSearchText($search);

    $query->where(function ($builder) use ($search, $normalizedSearch) {
        $builder
            ->where('source_item_name', 'like', "%{$search}%")
            ->orWhere('normalized_name', 'like', "%{$normalizedSearch}%")
            ->orWhere('suggested_base_name', 'like', "%{$search}%")
            ->orWhere('variant_text', 'like', "%{$search}%")
            ->orWhereHas('matchedMaterialType', function ($materialQuery) use ($search) {
                $materialQuery
                    ->where('material_type_name', 'like', "%{$search}%")
                    ->orWhere('material_type_code', 'like', "%{$search}%");
            });
    });
}

        if ($categoryId) {
            $query->where('material_catalog_category_id', $categoryId);
        }

        if ($subcategoryId) {
            $query->where('material_catalog_subcategory_id', $subcategoryId);
        }

        if ($mappingStatus === 'mapped') {
            $query->whereNotNull('matched_material_type_id');
        }

        if ($mappingStatus === 'unmapped') {
            $query->whereNull('matched_material_type_id');
        }

        $items = $query
            ->orderByRaw('matched_material_type_id IS NULL DESC')
            ->orderBy('source_item_name')
            ->paginate(50)
            ->withQueryString();

        $categories = MaterialCatalogCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        $subcategories = MaterialCatalogSubcategory::query()
            ->where('is_active', true)
            ->when(
                $categoryId,
                fn ($query) => $query->where(
                    'material_catalog_category_id',
                    $categoryId
                )
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'material_catalog_category_id',
                'code',
                'name',
            ]);

        $summary = [
            'total' => MaterialCatalogItem::query()
                ->where('is_active', true)
                ->count(),

            'mapped' => MaterialCatalogItem::query()
                ->where('is_active', true)
                ->whereNotNull('matched_material_type_id')
                ->count(),

            'unmapped' => MaterialCatalogItem::query()
                ->where('is_active', true)
                ->whereNull('matched_material_type_id')
                ->count(),

            'material_types' => MaterialType::query()->count(),
        ];

        return view(
            'material-catalog-mapping.index',
            compact(
                'items',
                'categories',
                'subcategories',
                'summary',
                'search',
                'categoryId',
                'subcategoryId',
                'mappingStatus'
            )
        );
    }

    /**
     * AJAX catalogue search.
     *
     * Used later by:
     * - Material Catalogue mapping screen
     * - Material Received V3
     * - Material Consumed
     * - Material Required
     */
    public function searchCatalogue(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $search = trim((string) $request->input('q', ''));
        $limit = (int) $request->input('limit', 20);

        if (mb_strlen($search) < 1) {
            return response()->json([
                'data' => [],
            ]);
        }

        $normalizedSearch = $this->normalizeSearchText($search);

        $items = MaterialCatalogItem::query()
            ->with([
                'category:id,code,name',
                'subcategory:id,material_catalog_category_id,code,name',
                'matchedMaterialType:id,material_type_name,material_type_code,material_group,unit_master_id,is_active',
                'matchedMaterialType.unit:id,unit_name,unit_code,symbol',
                'suggestedUnit:id,unit_name,unit_code,symbol',
            ])
            ->where('is_active', true)
            ->where(function ($query) use ($search, $normalizedSearch) {
                $query
                    ->where('source_item_name', 'like', "%{$search}%")
                    ->orWhere('normalized_name', 'like', "%{$normalizedSearch}%")
                    ->orWhere('suggested_base_name', 'like', "%{$search}%")
                    ->orWhere('variant_text', 'like', "%{$search}%")
                    ->orWhereHas('matchedMaterialType', function ($materialQuery) use ($search) {
                        $materialQuery
                            ->where('material_type_name', 'like', "%{$search}%")
                            ->orWhere('material_type_code', 'like', "%{$search}%");
                    });
            })
            ->orderByRaw(
                "
                CASE
                    WHEN LOWER(source_item_name) = ? THEN 0
                    WHEN LOWER(source_item_name) LIKE ? THEN 1
                    WHEN matched_material_type_id IS NOT NULL THEN 2
                    ELSE 3
                END
                ",
                [
                    mb_strtolower($search),
                    mb_strtolower($search) . '%',
                ]
            )
            ->orderBy('source_item_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $items->map(function (MaterialCatalogItem $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->source_item_name,

                    'category' => $item->category
                        ? [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                        ]
                        : null,

                    'subcategory' => $item->subcategory
                        ? [
                            'id' => $item->subcategory->id,
                            'name' => $item->subcategory->name,
                        ]
                        : null,

                    'variant_text' => $item->variant_text,

                    'mapped' => (bool) $item->matched_material_type_id,

                    'material_type' => $item->matchedMaterialType
                        ? [
                            'id' => $item->matchedMaterialType->id,
                            'name' => $item->matchedMaterialType->material_type_name,
                            'code' => $item->matchedMaterialType->material_type_code,
                            'material_group' => $item->matchedMaterialType->material_group,
                            'unit' => $item->matchedMaterialType->unit
                                ? [
                                    'id' => $item->matchedMaterialType->unit->id,
                                    'name' => $item->matchedMaterialType->unit->unit_name,
                                    'code' => $item->matchedMaterialType->unit->unit_code,
                                    'symbol' => $item->matchedMaterialType->unit->symbol,
                                ]
                                : null,
                        ]
                        : null,

                    'suggested_unit' => $item->suggestedUnit
                        ? [
                            'id' => $item->suggestedUnit->id,
                            'name' => $item->suggestedUnit->unit_name,
                            'code' => $item->suggestedUnit->unit_code,
                            'symbol' => $item->suggestedUnit->symbol,
                        ]
                        : null,
                ];
            })->values(),
        ]);
    }

    /**
     * Search existing Material Types.
     *
     * This allows "Screws" to be mapped to existing "Screw",
     * "Nails" to "Nail", etc., without creating duplicates.
     */
    public function searchMaterialTypes(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $search = trim((string) $request->input('q', ''));
        $limit = (int) $request->input('limit', 20);

        $query = MaterialType::query()
            ->with('unit:id,unit_name,unit_code,symbol')
            ->where('is_active', true);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('material_type_name', 'like', "%{$search}%")
                    ->orWhere('material_type_code', 'like', "%{$search}%")
                    ->orWhere('material_group', 'like', "%{$search}%");
            });
        }

        $materialTypes = $query
            ->orderByRaw(
                "
                CASE
                    WHEN LOWER(material_type_name) = ? THEN 0
                    WHEN LOWER(material_type_name) LIKE ? THEN 1
                    ELSE 2
                END
                ",
                [
                    mb_strtolower($search),
                    mb_strtolower($search) . '%',
                ]
            )
            ->orderBy('material_type_name')
            ->limit($limit)
            ->get([
                'id',
                'material_type_name',
                'material_type_code',
                'material_group',
                'unit_master_id',
                'is_active',
            ]);

        return response()->json([
            'data' => $materialTypes->map(function (MaterialType $materialType) {
                return [
                    'id' => $materialType->id,
                    'name' => $materialType->material_type_name,
                    'code' => $materialType->material_type_code,
                    'material_group' => $materialType->material_group,

                    'unit' => $materialType->unit
                        ? [
                            'id' => $materialType->unit->id,
                            'name' => $materialType->unit->unit_name,
                            'code' => $materialType->unit->unit_code,
                            'symbol' => $materialType->unit->symbol,
                        ]
                        : null,
                ];
            })->values(),
        ]);
    }

    /**
     * Map one catalogue item to an existing Material Type.
     */
    public function mapExisting(
        Request $request,
        MaterialCatalogItem $materialCatalogItem
    ): RedirectResponse {
        $validated = $request->validate([
            'material_type_id' => [
                'required',
                'integer',
                Rule::exists('material_types', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $materialType = MaterialType::query()
            ->findOrFail($validated['material_type_id']);

        $oldMaterialTypeId = $materialCatalogItem->matched_material_type_id;

        DB::transaction(function () use (
            $materialCatalogItem,
            $materialType
        ) {
            $this->mapCatalogueItemToMaterialType(
                $materialCatalogItem,
                $materialType
            );
        });

        AuditHelper::log(
            'Material Catalogue',
            'Mapped',
            'MaterialCatalogItem',
            $materialCatalogItem->id,
            sprintf(
                'Catalogue item "%s" mapped to Material Type "%s".',
                $materialCatalogItem->source_item_name,
                $materialType->material_type_name
            ),
            [
                'matched_material_type_id' => $oldMaterialTypeId,
            ],
            [
                'matched_material_type_id' => $materialType->id,
            ]
        );

        return back()->with(
            'success',
            sprintf(
                '%s mapped to %s successfully.',
                $materialCatalogItem->source_item_name,
                $materialType->material_type_name
            )
        );
    }

    /**
     * Create a new Material Type directly from a catalogue product.
     *
     * This deliberately remains simple.
     * We are NOT forcing variant creation here.
     */
    public function createMaterial(
        Request $request,
        MaterialCatalogItem $materialCatalogItem
    ): RedirectResponse {
        $validated = $request->validate([
            'material_type_name' => [
                'required',
                'string',
                'max:255',
            ],

            'unit_master_id' => [
                'nullable',
                'integer',
                Rule::exists('unit_masters', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $materialName = trim($validated['material_type_name']);

        $existingMaterialType = MaterialType::query()
            ->whereRaw(
                'LOWER(TRIM(material_type_name)) = ?',
                [mb_strtolower($materialName)]
            )
            ->first();

        if ($existingMaterialType) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    sprintf(
                        'Material "%s" already exists as %s. Please map the catalogue item to the existing Material Type instead.',
                        $existingMaterialType->material_type_name,
                        $existingMaterialType->material_type_code
                    )
                );
        }

        $materialType = DB::transaction(function () use (
            $validated,
            $materialName,
            $materialCatalogItem
        ) {
            $categoryName = $materialCatalogItem->category?->name
                ?? 'General Materials';

            $unitMasterId = $validated['unit_master_id']
                ?? $materialCatalogItem->suggested_unit_master_id;

            $nextSequence = ((int) MaterialType::query()->max('sequence')) + 1;

            $materialType = new MaterialType();

            /**
             * We assign fields directly instead of relying on the current
             * MaterialType::$fillable because the existing model still
             * contains legacy Activity fields.
             */
            $materialType->material_group = $categoryName;
            $materialType->material_type_name = $materialName;
            $materialType->material_type_code = $this->generateMaterialTypeCode(
                $categoryName,
                $materialName
            );
            $materialType->unit_master_id = $unitMasterId;
            $materialType->sequence = $nextSequence;
            $materialType->is_active = true;
            $materialType->remarks = $validated['remarks']
                ?? sprintf(
                    'Created from Material Catalogue item: %s',
                    $materialCatalogItem->source_item_name
                );
            $materialType->created_by = auth()->id();

            $materialType->save();

            $this->mapCatalogueItemToMaterialType(
                $materialCatalogItem,
                $materialType
            );

            return $materialType;
        });

        AuditHelper::log(
            'Material Catalogue',
            'Created Material',
            'MaterialType',
            $materialType->id,
            sprintf(
                'Material Type "%s" created from catalogue item "%s".',
                $materialType->material_type_name,
                $materialCatalogItem->source_item_name
            ),
            null,
            [
                'id' => $materialType->id,
                'material_group' => $materialType->material_group,
                'material_type_name' => $materialType->material_type_name,
                'material_type_code' => $materialType->material_type_code,
                'unit_master_id' => $materialType->unit_master_id,
                'sequence' => $materialType->sequence,
                'is_active' => $materialType->is_active,
            ]
        );

        return back()->with(
            'success',
            sprintf(
                'Material "%s" created and mapped successfully.',
                $materialType->material_type_name
            )
        );
    }

    /**
     * Remove catalogue → Material Type mapping.
     *
     * This does NOT delete the Material Type.
     */
    public function unmap(
        MaterialCatalogItem $materialCatalogItem
    ): RedirectResponse {
        if (!$materialCatalogItem->matched_material_type_id) {
            return back()->with(
                'info',
                'This catalogue item is already unmapped.'
            );
        }

        $oldMaterialTypeId = $materialCatalogItem->matched_material_type_id;
        $oldMaterialTypeName = $materialCatalogItem
            ->matchedMaterialType
            ?->material_type_name;

        DB::transaction(function () use ($materialCatalogItem) {
            $materialCatalogItem->update([
                'matched_material_type_id' => null,
                'match_status' => MaterialCatalogItem::STATUS_PENDING,
                'match_confidence' => null,
            ]);
        });

        AuditHelper::log(
            'Material Catalogue',
            'Unmapped',
            'MaterialCatalogItem',
            $materialCatalogItem->id,
            sprintf(
                'Catalogue item "%s" unmapped from Material Type "%s".',
                $materialCatalogItem->source_item_name,
                $oldMaterialTypeName ?? $oldMaterialTypeId
            ),
            [
                'matched_material_type_id' => $oldMaterialTypeId,
            ],
            [
                'matched_material_type_id' => null,
            ]
        );

        return back()->with(
            'success',
            'Catalogue mapping removed successfully.'
        );
    }

    /**
     * Data used by the "Create Material" modal.
     */
    public function formData(
        MaterialCatalogItem $materialCatalogItem
    ): JsonResponse {
        $materialCatalogItem->load([
            'category:id,code,name',
            'subcategory:id,material_catalog_category_id,code,name',
            'suggestedUnit:id,unit_name,unit_code,symbol',
            'matchedMaterialType:id,material_type_name,material_type_code,material_group,unit_master_id,is_active',
            'matchedMaterialType.unit:id,unit_name,unit_code,symbol',
        ]);

        $units = UnitMaster::query()
            ->where('is_active', true)
            ->orderBy('unit_type')
            ->orderBy('unit_name')
            ->get([
                'id',
                'unit_name',
                'unit_code',
                'symbol',
            ]);

        return response()->json([
            'item' => [
                'id' => $materialCatalogItem->id,
                'name' => $materialCatalogItem->source_item_name,
                'suggested_base_name' => $materialCatalogItem->suggested_base_name,
                'variant_text' => $materialCatalogItem->variant_text,

                'category' => $materialCatalogItem->category
                    ? [
                        'id' => $materialCatalogItem->category->id,
                        'name' => $materialCatalogItem->category->name,
                    ]
                    : null,

                'subcategory' => $materialCatalogItem->subcategory
                    ? [
                        'id' => $materialCatalogItem->subcategory->id,
                        'name' => $materialCatalogItem->subcategory->name,
                    ]
                    : null,

                'suggested_unit_id' => $materialCatalogItem
                    ->suggested_unit_master_id,

                'mapped_material_type' => $materialCatalogItem
                    ->matchedMaterialType
                    ? [
                        'id' => $materialCatalogItem->matchedMaterialType->id,
                        'name' => $materialCatalogItem->matchedMaterialType
                            ->material_type_name,
                        'code' => $materialCatalogItem->matchedMaterialType
                            ->material_type_code,
                    ]
                    : null,
            ],

            'units' => $units,
        ]);
    }

    /**
     * Map a catalogue item to the selected Material Type and ensure
     * the Material Type is linked to the catalogue subcategory.
     */
    private function mapCatalogueItemToMaterialType(
        MaterialCatalogItem $catalogueItem,
        MaterialType $materialType
    ): void {
        $catalogueItem->update([
            'matched_material_type_id' => $materialType->id,
            'match_status' => MaterialCatalogItem::STATUS_EXACT_MATCH,
            'match_confidence' => 100,
        ]);

        if (!$catalogueItem->material_catalog_subcategory_id) {
            return;
        }

        /**
         * We use the catalogue subcategory pivot as the reusable
         * browsing/category mapping.
         *
         * One Material Type may belong to several catalogue
         * subcategories without duplication.
         */
        DB::table('material_catalog_subcategory_material_type')
            ->updateOrInsert(
                [
                    'material_catalog_subcategory_id'
                        => $catalogueItem->material_catalog_subcategory_id,
                    'material_type_id'
                        => $materialType->id,
                ],
                [
                    'is_preferred' => false,
                    'sort_order' => 0,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
    }

    /**
     * Generate a short, readable, unique Material Type code.
     *
     * Example:
     * category = Plumbing Fittings
     * material = CPVC Elbow
     *
     * => PLUM-CPVCELBO
     */
    private function generateMaterialTypeCode(
        string $categoryName,
        string $materialName
    ): string {
        $groupPrefix = Str::upper(
            Str::substr(
                preg_replace('/[^A-Za-z0-9]/', '', $categoryName),
                0,
                4
            )
        );

        $materialPart = Str::upper(
            Str::substr(
                preg_replace('/[^A-Za-z0-9]/', '', $materialName),
                0,
                8
            )
        );

        if ($groupPrefix === '') {
            $groupPrefix = 'MAT';
        }

        if ($materialPart === '') {
            $materialPart = 'ITEM';
        }

        $baseCode = $groupPrefix . '-' . $materialPart;
        $code = $baseCode;
        $counter = 2;

        while (
            MaterialType::query()
                ->where('material_type_code', $code)
                ->exists()
        ) {
            $suffix = '-' . $counter;

            $allowedBaseLength = max(
                1,
                30 - strlen($suffix)
            );

            $code = substr(
                $baseCode,
                0,
                $allowedBaseLength
            ) . $suffix;

            $counter++;
        }

        return $code;
    }

    /**
     * Normalize search text consistently.
     */
    private function normalizeSearchText(string $value): string
    {
        $value = mb_strtolower(trim($value));

        $value = preg_replace(
            '/[^a-z0-9]+/u',
            ' ',
            $value
        );

        return trim(
            preg_replace('/\s+/', ' ', $value)
        );
    }
}