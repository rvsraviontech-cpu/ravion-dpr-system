<?php

namespace App\Http\Controllers\Materials;

use App\Http\Controllers\Controller;
use App\Models\MaterialType;
use Illuminate\Http\JsonResponse;

class ProductDependencyController extends Controller
{
    public function __invoke(MaterialType $materialType): JsonResponse
    {
        if (
            ! $materialType->is_active
            || $materialType->is_legacy
            || ! in_array($materialType->master_status, ['Approved', 'CANONICAL'], true)
        ) {
            abort(404, 'Product is not available for operational use.');
        }

        $materialType->load([
            'unit:id,unit_name,unit_code,symbol',
            'specifications' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('specification_name'),
            'grades' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('grade_name'),
            'activeCanonicalBrands' => fn ($query) => $query
                ->where('brand_masters.is_active', true),
        ]);

        return response()->json([
            'data' => [
                'id' => $materialType->id,
                'name' => $materialType->material_type_name,
                'code' => $materialType->material_type_code,
                'catalogue_code' => $materialType->catalogue_source_code,
                'inventory_type' => $materialType->inventory_type,
                'unit' => $materialType->unit ? [
                    'id' => $materialType->unit->id,
                    'name' => $materialType->unit->unit_name,
                    'code' => $materialType->unit->unit_code,
                    'symbol' => $materialType->unit->symbol,
                ] : null,
                'specifications' => $materialType->specifications->map(fn ($item) => [
                    'id' => $item->id,
                    'code' => $item->specification_code,
                    'name' => $item->specification_name,
                ])->values(),
                'grades' => $materialType->grades->map(fn ($item) => [
                    'id' => $item->id,
                    'code' => $item->grade_code,
                    'name' => $item->grade_name,
                ])->values(),
                'brands' => $materialType->activeCanonicalBrands
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->brand_name,
                        'is_preferred' => (bool) $item->pivot->is_preferred,
                    ])
                    ->values(),
            ],
        ]);
    }
}
