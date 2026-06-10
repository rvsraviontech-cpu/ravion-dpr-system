<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialReceived;
use App\Models\MaterialConsumed;
use App\Models\Project;
use App\Models\ProjectBlock;
use Illuminate\Http\Request;

class MaterialLedgerController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::where('is_active', true)
            ->orderBy('material_name')
            ->get();

        $projects = Project::where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        $projectBlocks = ProjectBlock::where('is_active', true)
            ->orderBy('name')
            ->get();

        $ledgerRows = [];

        $receivedQuery = MaterialReceived::with(['project', 'block', 'material'])
            ->whereIn('status', ['Approved', 'approved']);

        $consumedQuery = MaterialConsumed::with(['project', 'block', 'material'])
            ->whereIn('status', ['Approved', 'approved']);

        if ($request->filled('material_id')) {
            $receivedQuery->where('material_id', $request->material_id);
            $consumedQuery->where('material_id', $request->material_id);
        }

        if ($request->filled('project_id')) {
            $receivedQuery->where('project_id', $request->project_id);
            $consumedQuery->where('project_id', $request->project_id);
        }

        if ($request->filled('project_block_id')) {
            $receivedQuery->where('project_block_id', $request->project_block_id);
            $consumedQuery->where('project_block_id', $request->project_block_id);
        }

        if ($request->filled('from_date')) {
            $receivedQuery->whereDate('received_date', '>=', $request->from_date);
            $consumedQuery->whereDate('consumed_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $receivedQuery->whereDate('received_date', '<=', $request->to_date);
            $consumedQuery->whereDate('consumed_date', '<=', $request->to_date);
        }

        foreach ($receivedQuery->get() as $received) {
            $ledgerRows[] = [
                'date' => $received->received_date,
                'type' => 'Received',
                'project' => $received->project?->project_name ?? '-',
                'block' => $received->block?->name ?? '-',
                'reference' => $received->challan_number ?? $received->bill_number ?? '-',
                'received_qty' => $received->quantity_received,
                'consumed_qty' => 0,
                'unit' => $received->unit ?? '-',
                'remarks' => $received->remarks ?? '-',
            ];
        }

        foreach ($consumedQuery->get() as $consumed) {
            $ledgerRows[] = [
                'date' => $consumed->consumed_date,
                'type' => 'Consumed',
                'project' => $consumed->project?->project_name ?? '-',
                'block' => $consumed->block?->name ?? '-',
                'reference' => 'Consumption #' . $consumed->id,
                'received_qty' => 0,
                'consumed_qty' => $consumed->quantity_consumed,
                'unit' => $consumed->unit ?? '-',
                'remarks' => $consumed->remarks ?? '-',
            ];
        }

        usort($ledgerRows, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        $runningBalance = 0;

        foreach ($ledgerRows as $index => $row) {
            $runningBalance += $row['received_qty'];
            $runningBalance -= $row['consumed_qty'];

            $ledgerRows[$index]['balance_qty'] = $runningBalance;
        }

        return view('material-ledger.index', compact(
            'materials',
            'projects',
            'projectBlocks',
            'ledgerRows'
        ));
    }
}