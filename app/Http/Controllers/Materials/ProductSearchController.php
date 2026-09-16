<?php

namespace App\Http\Controllers\Materials;

use App\Http\Controllers\Controller;
use App\Models\MaterialType;
use App\Models\MaterialVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    private const BIBLE_SOURCE = 'RAVION_BIBLE_20260912';

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'activity_division_id' => [
                'nullable',
                'integer',
                'exists:activity_divisions,id',
            ],
            'material_product_group_id' => [
                'nullable',
                'integer',
                'exists:material_product_groups,id',
            ],
            'material_product_type_id' => [
                'nullable',
                'integer',
                'exists:material_product_types,id',
            ],
            'inventory_type' => [
                'nullable',
                'string',
                'max:100',
            ],
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 30);

        $normalized = $this->normalizeSearch($search);
        $concepts = $this->searchConcepts($normalized);

        $query = MaterialType::query()
            ->with([
                'productGroup:id,group_code,group_name',
                'productType:id,material_product_group_id,type_code,type_name',
                'unit:id,unit_name,unit_code,symbol',

                'variants' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->with([
                            'unit:id,unit_name,unit_code,symbol',
                            'specification:id,specification_name',
                            'grade:id,grade_name',
                            'searchAliases' => function ($aliasQuery) {
                                $aliasQuery
                                    ->where('is_active', true)
                                    ->select([
                                        'id',
                                        'material_type_id',
                                        'material_variant_id',
                                        'alias',
                                        'normalized_alias',
                                    ]);
                            },
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('variant_name');
                },
            ])
            ->where('material_types.is_active', true)
            ->where('material_types.is_legacy', false)
            ->whereIn('material_types.master_status', [
                'Approved',
                'CANONICAL',
            ]);

        /*
         * Optional contextual filters.
         */
        if (! empty($validated['activity_division_id'])) {
            $activityDivisionId =
                (int) $validated['activity_division_id'];

            $query->whereExists(
                function ($subQuery) use ($activityDivisionId) {
                    $subQuery
                        ->selectRaw('1')
                        ->from('material_product_usage_mappings as usage')
                        ->whereColumn(
                            'usage.material_type_id',
                            'material_types.id'
                        )
                        ->where(
                            'usage.activity_division_id',
                            $activityDivisionId
                        )
                        ->where('usage.is_active', true);
                }
            );
        }

        if (! empty($validated['material_product_group_id'])) {
            $query->where(
                'material_types.material_product_group_id',
                (int) $validated['material_product_group_id']
            );
        }

        if (! empty($validated['material_product_type_id'])) {
            $query->where(
                'material_types.material_product_type_id',
                (int) $validated['material_product_type_id']
            );
        }

        if (! empty($validated['inventory_type'])) {
            $query->where(
                'material_types.inventory_type',
                $validated['inventory_type']
            );
        }

        /*
         * Search.
         *
         * Every meaningful concept must match somewhere in:
         *
         * Product
         * OR Product Alias
         * OR Variant
         * OR Variant Alias
         * OR Classification
         *
         * Example:
         *
         * "20mm jelly"
         *
         * 20mm  -> Variant
         * jelly -> Product alias
         *
         * Both concepts therefore belong to the same Product.
         */
        if ($normalized !== '') {
            foreach ($concepts as $concept) {
                $this->applyConceptConstraint(
                    $query,
                    $concept
                );
            }

            /*
             * Relevance ranking.
             */
            $compactPhrase = $this->compactSearch($normalized);

            $query->orderByRaw(
                "
                CASE

                    /* Exact Product name */
                    WHEN LOWER(material_types.material_type_name) = ?
                        THEN 0

                    /* Exact Product alias */
                    WHEN EXISTS (
                        SELECT 1
                        FROM material_search_aliases psa
                        WHERE psa.material_type_id = material_types.id
                          AND psa.material_variant_id IS NULL
                          AND psa.is_active = 1
                          AND (
                              LOWER(psa.alias) = ?
                              OR LOWER(psa.normalized_alias) = ?
                          )
                    )
                        THEN 1

                    /* Exact/compact Variant match */
                    WHEN EXISTS (
                        SELECT 1
                        FROM material_variants mv
                        WHERE mv.material_type_id = material_types.id
                          AND mv.is_active = 1
                          AND (
                              REPLACE(
                                  LOWER(COALESCE(mv.variant_name, '')),
                                  ' ',
                                  ''
                              ) = ?
                              OR REPLACE(
                                  LOWER(COALESCE(mv.size_dimension, '')),
                                  ' ',
                                  ''
                              ) = ?
                          )
                    )
                        THEN 2

                    /* Product begins with phrase */
                    WHEN LOWER(material_types.material_type_name) LIKE ?
                        THEN 3

                    /* Bible canonical catalogue */
                    WHEN material_types.catalogue_source_code = ?
                        THEN 4

                    ELSE 20
                END
                ",
                [
                    $normalized,
                    $normalized,
                    $normalized,
                    $compactPhrase,
                    $compactPhrase,
                    $normalized.'%',
                    self::BIBLE_SOURCE,
                ]
            );
        } else {
            /*
             * General browsing:
             * Bible catalogue first, then other approved records.
             */
            $query->orderByRaw(
                'CASE WHEN material_types.catalogue_source_code = ? THEN 0 ELSE 1 END',
                [self::BIBLE_SOURCE]
            );
        }

        $products = $query
            ->orderBy('material_types.material_type_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $products
                ->map(
                    fn (MaterialType $product) =>
                        $this->formatProduct(
                            $product,
                            $normalized,
                            $concepts
                        )
                )
                ->values(),

            'meta' => [
                'query' => $search,
                'normalized_query' => $normalized,
                'concepts' => $concepts,
                'returned' => $products->count(),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Apply one search concept.
     *
     * Each concept must be represented somewhere inside the Product's
     * searchable identity.
     */
    private function applyConceptConstraint(
        Builder $query,
        string $concept
    ): void {
        $like = '%'.$concept.'%';
        $compact = $this->compactSearch($concept);
        $compactLike = '%'.$compact.'%';

        $query->where(
            function (Builder $builder) use (
                $like,
                $compactLike
            ) {
                /*
                 * Product identity.
                 */
                $builder
                    ->whereRaw(
                        'LOWER(material_types.material_type_name) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(material_types.material_type_code, "")) LIKE ?',
                        [$like]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(material_types.material_group, "")) LIKE ?',
                        [$like]
                    )

                    /*
                     * Product aliases.
                     */
                    ->orWhereHas(
                        'searchAliases',
                        function (Builder $aliasQuery) use ($like) {
                            $aliasQuery
                                ->where('is_active', true)
                                ->where(function (Builder $q) use ($like) {
                                    $q
                                        ->whereRaw(
                                            'LOWER(alias) LIKE ?',
                                            [$like]
                                        )
                                        ->orWhereRaw(
                                            'LOWER(normalized_alias) LIKE ?',
                                            [$like]
                                        );
                                });
                        }
                    )

                    /*
                     * Variant identity.
                     */
                    ->orWhereHas(
                        'variants',
                        function (Builder $variantQuery) use (
                            $like,
                            $compactLike
                        ) {
                            $variantQuery
                                ->where('is_active', true)
                                ->where(
                                    function (Builder $q) use (
                                        $like,
                                        $compactLike
                                    ) {
                                        $q
                                            ->whereRaw(
                                                'LOWER(COALESCE(variant_name, "")) LIKE ?',
                                                [$like]
                                            )
                                            ->orWhereRaw(
                                                'LOWER(COALESCE(variant_code, "")) LIKE ?',
                                                [$like]
                                            )
                                            ->orWhereRaw(
                                                'LOWER(COALESCE(size_dimension, "")) LIKE ?',
                                                [$like]
                                            )
                                            ->orWhereRaw(
                                                'LOWER(COALESCE(finish, "")) LIKE ?',
                                                [$like]
                                            )
                                            ->orWhereRaw(
                                                'LOWER(COALESCE(colour_shade, "")) LIKE ?',
                                                [$like]
                                            )
                                            ->orWhereRaw(
                                                'LOWER(COALESCE(search_aliases, "")) LIKE ?',
                                                [$like]
                                            )

                                            /*
                                             * Dimensional normalization:
                                             *
                                             * 12 mm == 12mm
                                             * 600 x 600 == 600x600
                                             */
                                            ->orWhereRaw(
                                                'REPLACE(LOWER(COALESCE(variant_name, "")), " ", "") LIKE ?',
                                                [$compactLike]
                                            )
                                            ->orWhereRaw(
                                                'REPLACE(LOWER(COALESCE(size_dimension, "")), " ", "") LIKE ?',
                                                [$compactLike]
                                            )
                                            ->orWhereRaw(
                                                'REPLACE(LOWER(COALESCE(search_aliases, "")), " ", "") LIKE ?',
                                                [$compactLike]
                                            );
                                    }
                                );
                        }
                    )

                    /*
                     * Existing classification.
                     */
                    ->orWhereHas(
                        'productGroup',
                        fn (Builder $q) =>
                            $q
                                ->whereRaw(
                                    'LOWER(group_name) LIKE ?',
                                    [$like]
                                )
                                ->orWhereRaw(
                                    'LOWER(group_code) LIKE ?',
                                    [$like]
                                )
                    )
                    ->orWhereHas(
                        'productType',
                        fn (Builder $q) =>
                            $q
                                ->whereRaw(
                                    'LOWER(type_name) LIKE ?',
                                    [$like]
                                )
                                ->orWhereRaw(
                                    'LOWER(type_code) LIKE ?',
                                    [$like]
                                )
                    );
            }
        );
    }

    /**
     * Format one Product for the shared frontend selector.
     */
    private function formatProduct(
        MaterialType $product,
        string $normalized,
        array $concepts
    ): array {
        $matchingVariants = collect();

        if ($normalized !== '') {
            $matchingVariants = $product->variants
                ->filter(
                    fn (MaterialVariant $variant) =>
                        $this->variantMatchesAnyConcept(
                            $variant,
                            $concepts
                        )
                )
                ->take(20)
                ->map(
                    fn (MaterialVariant $variant) =>
                        $this->formatVariant($variant)
                )
                ->values();
        }

        $classification =
            $product->productType?->type_name
            ?? $product->productGroup?->group_name
            ?? $product->material_group;

        return [
            'id' => $product->id,
            'name' => $product->material_type_name,
            'code' => $product->material_type_code,
            'catalogue_code' => $product->catalogue_source_code,
            'inventory_type' => $product->inventory_type,
            'master_status' => $product->master_status,

            'is_bible' =>
                $product->catalogue_source_code ===
                self::BIBLE_SOURCE,

            /*
             * Useful Bible-friendly classification.
             */
            'classification' => $classification,

            'material_group' => $product->material_group,

            'product_group' => $product->productGroup ? [
                'id' => $product->productGroup->id,
                'code' => $product->productGroup->group_code,
                'name' => $product->productGroup->group_name,
            ] : null,

            'product_type' => $product->productType ? [
                'id' => $product->productType->id,
                'code' => $product->productType->type_code,
                'name' => $product->productType->type_name,
            ] : null,

            /*
             * Default Product Master unit only.
             * Transaction unit remains editable.
             */
            'unit' => $product->unit ? [
                'id' => $product->unit->id,
                'name' => $product->unit->unit_name,
                'code' => $product->unit->unit_code,
                'symbol' => $product->unit->symbol,
            ] : null,

            'matching_variants' => $matchingVariants,

            'matching_variant_count' =>
                $matchingVariants->count(),
        ];
    }

    /**
     * Format one variant.
     */
    private function formatVariant(
        MaterialVariant $variant
    ): array {
        return [
            'id' => $variant->id,
            'code' => $variant->variant_code,
            'name' => $variant->variant_name,

            'specification' =>
                $variant->specification?->specification_name,

            'grade' =>
                $variant->grade?->grade_name,

            'size_dimension' =>
                $variant->size_dimension,

            'finish' =>
                $variant->finish,

            'colour_shade' =>
                $variant->colour_shade,

            'search_aliases' =>
                $variant->search_aliases,

            'unit' => $variant->unit ? [
                'id' => $variant->unit->id,
                'name' => $variant->unit->unit_name,
                'code' => $variant->unit->unit_code,
                'symbol' => $variant->unit->symbol,
            ] : null,
        ];
    }

    /**
     * Determine whether a loaded variant matches at least one of the
     * user's search concepts.
     *
     * The database query already guarantees the Product satisfies all
     * concepts across Product + Alias + Variant.
     *
     * This method only decides which variants are useful to display.
     */
    private function variantMatchesAnyConcept(
        MaterialVariant $variant,
        array $concepts
    ): bool {
        $parts = [
            $variant->variant_name,
            $variant->variant_code,
            $variant->size_dimension,
            $variant->finish,
            $variant->colour_shade,
            $variant->search_aliases,
            $variant->specification?->specification_name,
            $variant->grade?->grade_name,
        ];

        foreach ($variant->searchAliases as $alias) {
            $parts[] = $alias->alias;
            $parts[] = $alias->normalized_alias;
        }

        $haystack = $this->normalizeSearch(
            implode(
                ' ',
                array_filter(
                    $parts,
                    fn ($value) =>
                        $value !== null &&
                        trim((string) $value) !== ''
                )
            )
        );

        $compactHaystack =
            $this->compactSearch($haystack);

        foreach ($concepts as $concept) {
            if (
                str_contains($haystack, $concept) ||
                str_contains(
                    $compactHaystack,
                    $this->compactSearch($concept)
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert a user phrase into meaningful construction-search concepts.
     *
     * Examples:
     *
     * "12 mm TMT"   -> ["12mm", "tmt"]
     * "TMT 12mm"    -> ["tmt", "12mm"]
     * "20 mm jelly" -> ["20mm", "jelly"]
     * "tile adhesive" -> ["tile", "adhesive"]
     */
    private function searchConcepts(
        string $normalized
    ): array {
        if ($normalized === '') {
            return [];
        }

        /*
         * Join number + dimensional unit.
         *
         * 12 mm  -> 12mm
         * 20 MM  -> 20mm
         * 25 cm  -> 25cm
         */
        $prepared = preg_replace(
            '/(\d+(?:\.\d+)?)\s+(mm|cm|mtr|m|ft|inch|in)\b/i',
            '$1$2',
            $normalized
        ) ?? $normalized;

        /*
         * Join common dimension expressions.
         *
         * 600 x 600 -> 600x600
         */
        $prepared = preg_replace(
            '/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/iu',
            '$1x$2',
            $prepared
        ) ?? $prepared;

        $tokens = preg_split(
            '/[^a-z0-9.]+/i',
            $prepared,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        return collect($tokens)
            ->map(fn (string $token) =>
                mb_strtolower(trim($token))
            )
            ->map(fn (string $token) =>
                $this->singularizeSearchToken($token)
            )
            ->filter(
                fn (string $token) =>
                    mb_strlen($token) >= 2
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Small search-only singularisation.
     *
     * Never modifies master data.
     */
    private function singularizeSearchToken(
        string $token
    ): string {
        if (
            mb_strlen($token) > 4 &&
            str_ends_with($token, 'ies')
        ) {
            return mb_substr($token, 0, -3).'y';
        }

        if (
            mb_strlen($token) > 4 &&
            str_ends_with($token, 'es')
        ) {
            $withoutEs =
                mb_substr($token, 0, -2);

            if (
                str_ends_with($withoutEs, 'ch') ||
                str_ends_with($withoutEs, 'sh') ||
                str_ends_with($withoutEs, 'x')
            ) {
                return $withoutEs;
            }
        }

        if (
            mb_strlen($token) > 3 &&
            str_ends_with($token, 's')
        ) {
            return mb_substr($token, 0, -1);
        }

        return $token;
    }

    /**
     * Normalize ordinary search whitespace/case.
     */
    private function normalizeSearch(
        string $search
    ): string {
        $search =
            mb_strtolower(trim($search));

        return preg_replace(
            '/\s+/',
            ' ',
            $search
        ) ?? $search;
    }

    /**
     * Compact dimensional representation.
     *
     * "12 mm"       -> "12mm"
     * "600 x 600"   -> "600x600"
     */
    private function compactSearch(
        string $search
    ): string {
        $search =
            $this->normalizeSearch($search);

        $search = preg_replace(
            '/(\d+(?:\.\d+)?)\s+(mm|cm|mtr|m|ft|inch|in)\b/i',
            '$1$2',
            $search
        ) ?? $search;

        $search = preg_replace(
            '/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/iu',
            '$1x$2',
            $search
        ) ?? $search;

        return str_replace(' ', '', $search);
    }
}