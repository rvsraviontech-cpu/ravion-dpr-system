<?php

namespace App\Http\Controllers\Materials;

use App\Http\Controllers\Controller;
use App\Models\MaterialType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'activity_division_id' => ['nullable', 'integer', 'exists:activity_divisions,id'],
            'material_product_group_id' => ['nullable', 'integer', 'exists:material_product_groups,id'],
            'material_product_type_id' => ['nullable', 'integer', 'exists:material_product_types,id'],
            'inventory_type' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 30);

        $query = MaterialType::query()
            ->with([
                'productGroup:id,group_code,group_name',
                'productType:id,material_product_group_id,type_code,type_name',
                'unit:id,unit_name,unit_code,symbol',
            ])
            ->where('material_types.is_active', true)
            ->where('material_types.is_legacy', false)
            ->where('material_types.master_status', 'Approved');

        if (! empty($validated['activity_division_id'])) {
            $activityDivisionId = (int) $validated['activity_division_id'];

            $query->whereExists(function ($subQuery) use ($activityDivisionId) {
                $subQuery->selectRaw('1')
                    ->from('material_product_usage_mappings as usage')
                    ->whereColumn('usage.material_type_id', 'material_types.id')
                    ->where('usage.activity_division_id', $activityDivisionId)
                    ->where('usage.is_active', true);
            });
        }

        if (! empty($validated['material_product_group_id'])) {
            $query->where('material_types.material_product_group_id', (int) $validated['material_product_group_id']);
        }

        if (! empty($validated['material_product_type_id'])) {
            $query->where('material_types.material_product_type_id', (int) $validated['material_product_type_id']);
        }

        if (! empty($validated['inventory_type'])) {
            $query->where('material_types.inventory_type', $validated['inventory_type']);
        }

        if ($search !== '') {
            $normalized = mb_strtolower(preg_replace('/\s+/', ' ', $search));
            $like = '%'.$normalized.'%';
            $tokens = $this->searchTokens($normalized);

            $query->where(function (Builder $builder) use ($like, $tokens) {
                // Strong Product identity matches.
                $builder
                    ->whereRaw('LOWER(material_types.material_type_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(material_types.material_type_code, "")) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(COALESCE(material_types.catalogue_source_code, "")) LIKE ?', [$like])
                    ->orWhereHas('searchAliases', function (Builder $aliasQuery) use ($like) {
                        $aliasQuery->where('is_active', true)
                            ->where(function (Builder $q) use ($like) {
                                $q->whereRaw('LOWER(alias) LIKE ?', [$like])
                                  ->orWhereRaw('LOWER(normalized_alias) LIKE ?', [$like]);
                            });
                    });

                // Token-aware Product/alias match. This is what makes searches such
                // as "6A Switches" useful even when the canonical name says "6A Switch".
                foreach ($tokens as $token) {
                    $tokenLike = '%'.$token.'%';

                    $builder->orWhereRaw('LOWER(material_types.material_type_name) LIKE ?', [$tokenLike])
                        ->orWhereHas('searchAliases', function (Builder $aliasQuery) use ($tokenLike) {
                            $aliasQuery->where('is_active', true)
                                ->whereRaw('LOWER(alias) LIKE ?', [$tokenLike]);
                        });
                }

                // Classification is discovery fallback only, never the strongest match.
                $builder
                    ->orWhereRaw('LOWER(COALESCE(material_types.material_group, "")) LIKE ?', [$like])
                    ->orWhereHas('productGroup', fn (Builder $q) =>
                        $q->whereRaw('LOWER(group_name) LIKE ?', [$like])
                          ->orWhereRaw('LOWER(group_code) LIKE ?', [$like])
                    )
                    ->orWhereHas('productType', fn (Builder $q) =>
                        $q->whereRaw('LOWER(type_name) LIKE ?', [$like])
                          ->orWhereRaw('LOWER(type_code) LIKE ?', [$like])
                    );
            });

            $bindings = [$normalized, $normalized, $normalized.'%', $normalized];

            $tokenConditions = [];
            foreach ($tokens as $token) {
                $tokenConditions[] = 'LOWER(material_types.material_type_name) LIKE ?';
                $bindings[] = '%'.$token.'%';
            }

            $allTokensSql = $tokenConditions
                ? '('.implode(' AND ', $tokenConditions).')'
                : '0=1';

            $query->orderByRaw(
                "
                CASE
                    WHEN LOWER(material_types.material_type_name) = ? THEN 0
                    WHEN EXISTS (
                        SELECT 1 FROM material_search_aliases a
                        WHERE a.material_type_id = material_types.id
                          AND a.is_active = 1
                          AND LOWER(a.alias) = ?
                    ) THEN 1
                    WHEN LOWER(material_types.material_type_name) LIKE ? THEN 2
                    WHEN {$allTokensSql} THEN 3
                    WHEN EXISTS (
                        SELECT 1 FROM material_search_aliases a2
                        WHERE a2.material_type_id = material_types.id
                          AND a2.is_active = 1
                          AND LOWER(a2.alias) LIKE ?
                    ) THEN 4
                    WHEN LOWER(COALESCE(material_types.material_type_code, '')) = ?
                      OR LOWER(COALESCE(material_types.catalogue_source_code, '')) = ? THEN 5
                    ELSE 20
                END
                ",
                array_merge(
                    array_slice($bindings, 0, 3),
                    array_slice($bindings, 4),
                    [$normalized, $normalized, $normalized]
                )
            );
        }

        $products = $query
            ->orderBy('material_types.material_type_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $products->map(fn (MaterialType $product) => [
                'id' => $product->id,
                'name' => $product->material_type_name,
                'code' => $product->material_type_code,
                'catalogue_code' => $product->catalogue_source_code,
                'inventory_type' => $product->inventory_type,
                'master_status' => $product->master_status,
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
                'unit' => $product->unit ? [
                    'id' => $product->unit->id,
                    'name' => $product->unit->unit_name,
                    'code' => $product->unit->unit_code,
                    'symbol' => $product->unit->symbol,
                ] : null,
            ])->values(),
            'meta' => [
                'query' => $search,
                'returned' => $products->count(),
                'limit' => $limit,
            ],
        ]);
    }

    private function searchTokens(string $search): array
    {
        $tokens = preg_split('/[^a-z0-9]+/i', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($tokens)
            ->map(fn (string $token) => mb_strtolower($token))
            ->map(function (string $token) {
                // Small search-only singularisation: switches -> switch,
                // sockets -> socket, boxes -> box. It never changes master data.
                if (mb_strlen($token) > 4 && str_ends_with($token, 'ies')) {
                    return mb_substr($token, 0, -3).'y';
                }

                if (mb_strlen($token) > 4 && str_ends_with($token, 'es')) {
                    $withoutEs = mb_substr($token, 0, -2);
                    if (str_ends_with($withoutEs, 'ch') || str_ends_with($withoutEs, 'sh') || str_ends_with($withoutEs, 'x')) {
                        return $withoutEs;
                    }
                }

                if (mb_strlen($token) > 3 && str_ends_with($token, 's')) {
                    return mb_substr($token, 0, -1);
                }

                return $token;
            })
            ->filter(fn ($token) => mb_strlen($token) >= 2)
            ->unique()
            ->values()
            ->all();
    }
}
