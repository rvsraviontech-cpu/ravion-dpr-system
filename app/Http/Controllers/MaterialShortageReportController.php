<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequirement;
use App\Models\MaterialReceived;
use App\Models\MaterialConsumed;
use App\Models\Project;
use App\Models\ProjectBlock;
use Illuminate\Http\Request;

class MaterialShortageReportController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('status', 'Active')->orderBy('project_name')->get();
        $projectBlocks = ProjectBlock::where('is_active', true)->orderBy('name')->get();

        $materials = Material::where('is_active', true)
            ->orderBy('material_name')
            ->get();

        $reportRows = [];

        foreach ($materials as $material) {
            $requirementQuery = MaterialRequirement::where('material_id', $material->id)
                ->whereIn('status', ['Approved', 'approved']);

            $receivedQuery = MaterialReceived::where('material_id', $material->id)
                ->whereIn('status', ['Approved', 'approved']);

            $consumedQuery = MaterialConsumed::where('material_id', $material->id)
                ->whereIn('status', ['Approved', 'approved']);

            if ($request->filled('project_id')) {
                $requirementQuery->where('project_id', $request->project_id);
                $receivedQuery->where('project_id', $request->project_id);
                $consumedQuery->where('project_id', $request->project_id);
            }

            if ($request->filled('project_block_id')) {
                $requirementQuery->where('project_block_id', $request->project_block_id);
                $receivedQuery->where('project_block_id', $request->project_block_id);
                $consumedQuery->where('project_block_id', $request->project_block_id);
            }

            $requiredQty = $requirementQuery->sum('required_quantity');
            $fulfilledQty = $requirementQuery->sum('fulfilled_quantity');

            $openRequirement = $requiredQty - $fulfilledQty;

            $receivedQty = $receivedQuery->sum('quantity_received');
            $consumedQty = $consumedQuery->sum('quantity_consumed');

            $availableStock = $receivedQty - $consumedQty;

            $shortageQty = $openRequirement - $availableStock;

            if ($openRequirement > 0 || $availableStock > 0 || $shortageQty > 0) {
                $reportRows[] = [
                    'material' => $material,
                    'unit' => $material->unit ?? '-',
                    'required_qty' => $requiredQty,
                    'fulfilled_qty' => $fulfilledQty,
                    'open_requirement' => $openRequirement,
                    'available_stock' => $availableStock,
                    'shortage_qty' => max($shortageQty, 0),
                ];
            }
        }

        return view('material-shortage-report.index', compact(
            'projects',
            'projectBlocks',
            'reportRows'
        ));
    }
}