<?php

namespace App\Http\Controllers\Materials;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Read-only, progressively filtered Material Bible catalogue for operational forms. */
class MaterialBibleCatalogueController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'in:trades,main_categories,sub_categories,products'],
            'trade_id' => ['nullable', 'integer', 'min:1'],
            'main_category_id' => ['nullable', 'integer', 'min:1'],
            'sub_category_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $level = $validated['level'];
        $query = null;

        if ($level === 'trades') {
            $query = DB::table('material_bible_trades')
                ->where('is_active', true)
                ->select('id', 'name', 'code')
                ->orderBy('sort_order')->orderBy('name');
        } elseif ($level === 'main_categories') {
            $tradeId = $validated['trade_id'] ?? null;
            if (! $tradeId) {
                return response()->json(['message' => 'trade_id is required.'], 422);
            }
            $query = DB::table('material_bible_main_categories as mc')
                ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
                ->where('t.is_active', true)
                ->where('mc.is_active', true)
                ->where('mc.material_bible_trade_id', $tradeId)
                ->select('mc.id', 'mc.name', 'mc.code')
                ->orderBy('mc.sort_order')->orderBy('mc.name');
        } elseif ($level === 'sub_categories') {
            $mainId = $validated['main_category_id'] ?? null;
            if (! $mainId) {
                return response()->json(['message' => 'main_category_id is required.'], 422);
            }
            $query = DB::table('material_bible_sub_categories as sc')
                ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
                ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
                ->where('t.is_active', true)
                ->where('mc.is_active', true)
                ->where('sc.is_active', true)
                ->where('sc.material_bible_main_category_id', $mainId)
                ->select('sc.id', 'sc.name', 'sc.code')
                ->orderBy('sc.sort_order')->orderBy('sc.name');
        } else {
            $subId = $validated['sub_category_id'] ?? null;
            if (! $subId) {
                return response()->json(['message' => 'sub_category_id is required.'], 422);
            }
            $query = DB::table('material_bible_product_mappings as bpm')
                ->join('material_bible_sub_categories as sc', 'sc.id', '=', 'bpm.material_bible_sub_category_id')
                ->join('material_bible_main_categories as mc', 'mc.id', '=', 'sc.material_bible_main_category_id')
                ->join('material_bible_trades as t', 't.id', '=', 'mc.material_bible_trade_id')
                ->join('material_types as mt', 'mt.id', '=', 'bpm.material_type_id')
                ->where('bpm.material_bible_sub_category_id', $subId)
                ->where('bpm.is_active', true)
                ->where('sc.is_active', true)
                ->where('mc.is_active', true)
                ->where('t.is_active', true)
                ->where('mt.is_active', true)
                ->where('mt.is_legacy', false)
                ->whereIn('mt.master_status', ['Approved', 'CANONICAL'])
                ->select('mt.id', 'mt.material_type_name as name', 'mt.material_type_code as code')
                ->distinct()->orderBy('name');
        }

        return response()->json(['data' => $query->get()]);
    }
}
