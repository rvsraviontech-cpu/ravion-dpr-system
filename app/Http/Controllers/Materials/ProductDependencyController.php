<?php

namespace App\Http\Controllers\Materials;

use App\Http\Controllers\Controller;
use App\Models\MaterialType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            'variants' => fn ($query) => $query
                ->where('is_active', true)
                ->with(['unit:id,unit_name,unit_code,symbol', 'activeBrands'])
                ->orderBy('sort_order')
                ->orderBy('variant_name'),
        ]);

        // A product can belong to several Bible categories. Keep all active paths,
        // including the variant association, instead of choosing one arbitrarily.
        $bibleMappings = DB::table('material_bible_product_mappings as bpm')
            ->join('material_bible_sub_categories as sc', 'sc.id', '=', 'bpm.material_bible_sub_category_id')
            ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
            ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
            ->where('bpm.material_type_id', $materialType->id)
            ->where('bpm.is_active', true)
            ->where('sc.is_active', true)
            ->where('mc.is_active', true)
            ->where('t.is_active', true)
            ->select([
                'bpm.id as mapping_id',
                'bpm.material_variant_id',
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

        $unitData = static fn ($unit): ?array => $unit ? [
            'id' => $unit->id,
            'name' => $unit->unit_name,
            'code' => $unit->unit_code,
            'symbol' => $unit->symbol,
        ] : null;

        return response()->json([
            'data' => [
                'id' => $materialType->id,
                'name' => $materialType->material_type_name,
                'code' => $materialType->material_type_code,
                'catalogue_code' => $materialType->catalogue_source_code,
                'inventory_type' => $materialType->inventory_type,
                'unit' => $unitData($materialType->unit),
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
                    ])->values(),
                'variants' => $materialType->variants->map(function ($variant) use ($unitData) {
                    // Imported variant_name can combine Bible F and G. Keep it intact;
                    // size_dimension carries Bible G independently.
                    $size = trim((string) ($variant->size_dimension ?? ''));
                    $name = trim((string) $variant->variant_name);
                    $subItem = $name;
                    if ($size !== '' && str_ends_with($name, ' - '.$size)) {
                        $subItem = substr($name, 0, -strlen(' - '.$size));
                    } elseif ($size !== '' && $name === $size) {
                        $subItem = '';
                    }

                    return [
                        'id' => $variant->id,
                        'name' => $name,
                        'sub_item' => $subItem,
                        'size_specification' => $size,
                        'unit' => $unitData($variant->unit ?: $materialType->unit),
                        'brands' => $variant->activeBrands->map(fn ($brand) => [
                            'id' => $brand->id,
                            'name' => $brand->brand_name,
                            'is_preferred' => (bool) $brand->pivot->is_preferred,
                        ])->values(),
                    ];
                })->values(),
                'bible_mappings' => $bibleMappings->map(fn ($mapping) => [
                    'id' => $mapping->mapping_id,
                    'material_variant_id' => $mapping->material_variant_id,
                    'trade' => ['id' => $mapping->trade_id, 'name' => $mapping->trade_name],
                    'main_category' => ['id' => $mapping->main_id, 'name' => $mapping->main_name],
                    'sub_category' => ['id' => $mapping->sub_id, 'name' => $mapping->sub_name],
                ])->values(),
            ],
        ]);
    }
}
