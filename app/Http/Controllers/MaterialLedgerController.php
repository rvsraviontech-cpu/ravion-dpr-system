<?php

namespace App\Http\Controllers;

use App\Models\MaterialConsumed;
use App\Models\MaterialReceived;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\ProjectBlock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialLedgerController extends Controller
{
    public function index(Request $request): View
    {
        $ledgerRows = [];

        $received = MaterialReceived::query()
            ->with([
                'project', 'block',
                'items.materialType', 'items.brand',
                'items.specification', 'items.grade', 'items.unit',
                'material',
            ])
            ->whereIn('status', ['Approved', 'approved']);

        $consumed = MaterialConsumed::query()
            ->with([
                'project', 'block',
                'items.materialType', 'items.brand',
                'items.specification', 'items.grade', 'items.unit',
                'material',
            ])
            ->whereIn('status', ['Approved', 'approved']);

        foreach (['project_id', 'project_block_id'] as $field) {
            if ($request->filled($field)) {
                $received->where($field, $request->integer($field));
                $consumed->where($field, $request->integer($field));
            }
        }

        if ($request->filled('from_date')) {
            $received->whereDate(
                'received_date',
                '>=',
                $request->input('from_date')
            );

            $consumed->whereDate(
                'consumed_date',
                '>=',
                $request->input('from_date')
            );
        }

        if ($request->filled('to_date')) {
            $received->whereDate(
                'received_date',
                '<=',
                $request->input('to_date')
            );

            $consumed->whereDate(
                'consumed_date',
                '<=',
                $request->input('to_date')
            );
        }

        if ($request->filled('material_type_id')) {
            $typeId = $request->integer('material_type_id');

            $received->whereHas('items', fn ($q) =>
                $q->where('material_type_id', $typeId)
            );

            $consumed->whereHas('items', fn ($q) =>
                $q->where('material_type_id', $typeId)
            );
        }

        foreach ($received->get() as $header) {
            if ($header->items->isNotEmpty()) {
                foreach ($header->items as $item) {
                    if (
                        $request->filled('material_type_id')
                        && (int) $item->material_type_id
                            !== $request->integer('material_type_id')
                    ) {
                        continue;
                    }

                    $ledgerRows[] = $this->receivedRow($header, $item);
                }

                continue;
            }

            if ($header->material_id) {
                $ledgerRows[] = $this->legacyReceivedRow($header);
            }
        }

        foreach ($consumed->get() as $header) {
            if ($header->items->isNotEmpty()) {
                foreach ($header->items as $item) {
                    if (
                        $request->filled('material_type_id')
                        && (int) $item->material_type_id
                            !== $request->integer('material_type_id')
                    ) {
                        continue;
                    }

                    $ledgerRows[] = $this->consumedRow($header, $item);
                }

                continue;
            }

            if ($header->material_id) {
                $ledgerRows[] = $this->legacyConsumedRow($header);
            }
        }

        usort($ledgerRows, function (array $a, array $b): int {
            $first = strtotime(
                (string) $a['date'] . ' ' . ($a['time'] ?? '00:00:00')
            );

            $second = strtotime(
                (string) $b['date'] . ' ' . ($b['time'] ?? '00:00:00')
            );

            if ($first === $second) {
                if ($a['movement_type'] !== $b['movement_type']) {
                    return $a['movement_type'] === 'IN' ? -1 : 1;
                }

                return $a['sequence_id'] <=> $b['sequence_id'];
            }

            return $first <=> $second;
        });

        $balances = [];

        foreach ($ledgerRows as $index => $row) {
            $key = $row['variant_key'];
            $balances[$key] = $balances[$key] ?? 0.0;
            $balances[$key] += (float) $row['received_qty'];
            $balances[$key] -= (float) $row['issued_qty'];
            $ledgerRows[$index]['balance_qty'] = $balances[$key];
        }

        $projects = Project::query()
            ->where('status', 'Active')
            ->orderBy('project_name')
            ->get();

        $projectBlocks = ProjectBlock::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $materialTypes = MaterialType::query()
            ->where('is_active', true)
            ->orderBy('material_group')
            ->orderBy('material_type_name')
            ->get();

        $materials = $materialTypes;

        return view('material-ledger.index', compact(
            'materials',
            'materialTypes',
            'projects',
            'projectBlocks',
            'ledgerRows'
        ));
    }

    private function receivedRow($header, $item): array
    {
        return [
            'date' => $header->received_date,
            'time' => $header->received_time,
            'sequence_id' => $header->id,
            'type' => 'Received',
            'movement_type' => 'IN',
            'project_id' => $header->project_id,
            'project' => $header->project?->project_name ?? '-',
            'project_block_id' => $header->project_block_id,
            'block' => $header->block?->name ?? '-',
            'variant_key' => $this->variantKey(
                $header->project_id,
                $item->material_type_id,
                $item->brand_master_id,
                $item->material_specification_id,
                $item->material_grade_id,
                $item->unit_master_id
            ),
            'material_type_id' => $item->material_type_id,
            'material' =>
                $item->materialType?->material_type_name ?? '-',
            'material_group' =>
                $item->materialType?->material_group ?? '-',
            'brand' => $item->brand?->brand_name ?? '-',
            'specification' =>
                $item->specification?->specification_name ?? '-',
            'grade' => $item->grade?->grade_name ?? '-',
            'reference' =>
                $header->challan_number
                ?? $header->bill_number
                ?? 'Receipt #' . $header->id,
            'received_qty' => $this->effectiveReceivedQuantity($item),
            'consumed_qty' => 0.0,
            'wastage_qty' => 0.0,
            'issued_qty' => 0.0,
            'unit' => $item->unit?->unit_name ?? '-',
            'remarks' =>
                $item->remarks ?? $header->remarks ?? '-',
        ];
    }

    private function consumedRow($header, $item): array
    {
        $consumed = (float) $item->quantity_consumed;
        $wastage = (float) $item->wastage_quantity;

        return [
            'date' => $header->consumed_date,
            'time' => $header->consumed_time,
            'sequence_id' => $header->id,
            'type' => 'Consumed',
            'movement_type' => 'OUT',
            'project_id' => $header->project_id,
            'project' => $header->project?->project_name ?? '-',
            'project_block_id' => $header->project_block_id,
            'block' => $header->block?->name ?? '-',
            'variant_key' => $this->variantKey(
                $header->project_id,
                $item->material_type_id,
                $item->brand_master_id,
                $item->material_specification_id,
                $item->material_grade_id,
                $item->unit_master_id
            ),
            'material_type_id' => $item->material_type_id,
            'material' =>
                $item->materialType?->material_type_name ?? '-',
            'material_group' =>
                $item->materialType?->material_group ?? '-',
            'brand' => $item->brand?->brand_name ?? '-',
            'specification' =>
                $item->specification?->specification_name ?? '-',
            'grade' => $item->grade?->grade_name ?? '-',
            'reference' => 'Consumption #' . $header->id,
            'received_qty' => 0.0,
            'consumed_qty' => $consumed,
            'wastage_qty' => $wastage,
            'issued_qty' => $consumed + $wastage,
            'unit' => $item->unit?->unit_name ?? '-',
            'remarks' =>
                $item->remarks
                ?? $item->wastage_reason
                ?? $header->remarks
                ?? '-',
        ];
    }

    private function legacyReceivedRow($header): array
    {
        return [
            'date' => $header->received_date,
            'time' => $header->received_time,
            'sequence_id' => $header->id,
            'type' => 'Received',
            'movement_type' => 'IN',
            'project_id' => $header->project_id,
            'project' => $header->project?->project_name ?? '-',
            'project_block_id' => $header->project_block_id,
            'block' => $header->block?->name ?? '-',
            'variant_key' => $this->legacyKey(
                $header->project_id,
                $header->material_id,
                $header->unit
            ),
            'material_type_id' => null,
            'material' =>
                $header->material?->material_name
                ?? $header->material_name
                ?? '-',
            'material_group' => 'Legacy',
            'brand' => $header->brand ?? '-',
            'specification' => $header->specification ?? '-',
            'grade' => '-',
            'reference' =>
                $header->challan_number
                ?? $header->bill_number
                ?? 'Receipt #' . $header->id,
            'received_qty' =>
                (float) ($header->quantity_received ?? 0),
            'consumed_qty' => 0.0,
            'wastage_qty' => 0.0,
            'issued_qty' => 0.0,
            'unit' => $header->unit ?? '-',
            'remarks' => $header->remarks ?? '-',
        ];
    }

    private function legacyConsumedRow($header): array
    {
        $consumed = (float) ($header->quantity_consumed ?? 0);
        $wastage = (float) ($header->wastage_quantity ?? 0);

        return [
            'date' => $header->consumed_date,
            'time' => $header->consumed_time,
            'sequence_id' => $header->id,
            'type' => 'Consumed',
            'movement_type' => 'OUT',
            'project_id' => $header->project_id,
            'project' => $header->project?->project_name ?? '-',
            'project_block_id' => $header->project_block_id,
            'block' => $header->block?->name ?? '-',
            'variant_key' => $this->legacyKey(
                $header->project_id,
                $header->material_id,
                $header->unit
            ),
            'material_type_id' => null,
            'material' => $header->material?->material_name ?? '-',
            'material_group' => 'Legacy',
            'brand' => '-',
            'specification' => '-',
            'grade' => '-',
            'reference' => 'Consumption #' . $header->id,
            'received_qty' => 0.0,
            'consumed_qty' => $consumed,
            'wastage_qty' => $wastage,
            'issued_qty' => $consumed + $wastage,
            'unit' => $header->unit ?? '-',
            'remarks' =>
                $header->remarks
                ?? $header->wastage_reason
                ?? '-',
        ];
    }

    private function effectiveReceivedQuantity($item): float
    {
        $verified =
            (float) $item->accepted_quantity
            + (float) $item->short_quantity
            + (float) $item->damaged_quantity
            + (float) $item->rejected_quantity;

        return $verified > 0
            ? (float) $item->accepted_quantity
            : (float) $item->quantity_received;
    }

    private function variantKey(
        int $projectId,
        int $typeId,
        ?int $brandId,
        ?int $specId,
        ?int $gradeId,
        int $unitId
    ): string {
        return implode(':', [
            'new',
            $projectId,
            $typeId,
            $brandId ?? 0,
            $specId ?? 0,
            $gradeId ?? 0,
            $unitId,
        ]);
    }

    private function legacyKey(
        int $projectId,
        int $materialId,
        ?string $unit
    ): string {
        return implode(':', [
            'legacy',
            $projectId,
            $materialId,
            strtolower(trim((string) $unit)),
        ]);
    }
}
