<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialReceived;
use App\Models\MaterialConsumed;
use App\Models\Project;
use App\Models\ProjectBlock;
use Illuminate\Http\Request;

class StockRegisterController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::with('category')
            ->where('is_active', true)
            ->orderBy('material_name')
            ->get();

        $stockRows = [];

        foreach ($materials as $material) {
            $receivedQuery = MaterialReceived::where('material_id', $material->id)
                ->whereIn('status', ['Approved', 'approved']);

            $consumedQuery = MaterialConsumed::where('material_id', $material->id)
                ->whereIn('status', ['Approved', 'approved']);

            if ($request->filled('project_id')) {
                $receivedQuery->where('project_id', $request->project_id);
                $consumedQuery->where('project_id', $request->project_id);
            }

            if ($request->filled('project_block_id')) {
                $receivedQuery->where('project_block_id', $request->project_block_id);
                $consumedQuery->where('project_block_id', $request->project_block_id);
            }

            $receivedQty = $receivedQuery->sum('quantity_received');
            $consumedQty = $consumedQuery->sum('quantity_consumed');
            $balanceQty = $receivedQty - $consumedQty;

            if ($receivedQty > 0 || $consumedQty > 0) {
                $stockRows[] = [
                    'material' => $material,
                    'received_qty' => $receivedQty,
                    'consumed_qty' => $consumedQty,
                    'balance_qty' => $balanceQty,
                    'unit' => $material->unit ?? '-',
                ];
            }
        }

        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        $projectBlocks = ProjectBlock::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock-register.index', compact(
            'stockRows',
            'projects',
            'projectBlocks'
        ));
    }
}