<?php

namespace App\Http\Controllers;

use App\Models\MaterialConsumed;
use App\Models\MaterialReceived;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\ProjectBlock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockRegisterController extends Controller
{
    public function index(Request $request): View
    {
        $stock = [];

        $received = MaterialReceived::query()
            ->with([
                'project',
                'block',
                'items.materialType',
                'items.brand',
                'items.specification',
                'items.grade',
                'items.unit',
                'material.category',
            ])
            ->whereIn('status', ['Approved', 'approved']);

        $consumed = MaterialConsumed::query()
            ->with([
                'project',
                'block',
                'items.materialType',
                'items.brand',
                'items.specification',
                'items.grade',
                'items.unit',
                'material.category',
            ])
            ->whereIn('status', ['Approved', 'approved']);

        foreach (['project_id', 'project_block_id'] as $field) {
            if ($request->filled($field)) {
                $received->where($field, $request->integer($field));
                $consumed->where($field, $request->integer($field));
            }
        }

        if ($request->filled('material_group')) {
            $materialGroup = $request->string('material_group')->toString();

            $received->whereHas(
                'items.materialType',
                fn ($query) => $query->where(
                    'material_group',
                    $materialGroup
                )
            );

            $consumed->whereHas(
                'items.materialType',
                fn ($query) => $query->where(
                    'material_group',
                    $materialGroup
                )
            );
        }

        if ($request->filled('material_type_id')) {
            $materialTypeId = $request->integer('material_type_id');

            $received->whereHas(
                'items',
                fn ($query) => $query->where(
                    'material_type_id',
                    $materialTypeId
                )
            );

            $consumed->whereHas(
                'items',
                fn ($query) => $query->where(
                    'material_type_id',
                    $materialTypeId
                )
            );
        }

        foreach ($received->get() as $header) {
            if ($header->items->isNotEmpty()) {
                foreach ($header->items as $item) {
                    if (! $this->itemMatchesFilters($request, $item)) {
                        continue;
                    }

                    $key = $this->variantKey(
                        $header->project_id,
                        $item->material_type_id,
                        $item->brand_master_id,
                        $item->material_specification_id,
                        $item->material_grade_id,
                        $item->unit_master_id
                    );

                    $this->initializeVariant(
                        $stock,
                        $key,
                        $header,
                        $item
                    );

                    $stock[$key]['received_qty'] +=
                        $this->effectiveReceivedQuantity($item);

                    $this->updateLatestMovement(
                        $stock[$key],
                        $header->received_date,
                        $header->received_time,
                        $header->updated_at
                    );
                }

                continue;
            }

            if (
                $request->filled('material_group')
                || $request->filled('material_type_id')
                || ! $header->material_id
            ) {
                continue;
            }

            $key = $this->legacyKey(
                $header->project_id,
                $header->material_id,
                $header->unit
            );

            $this->initializeLegacy(
                $stock,
                $key,
                $header,
                $header->material
            );

            $stock[$key]['received_qty'] +=
                (float) ($header->quantity_received ?? 0);

            $this->updateLatestMovement(
                $stock[$key],
                $header->received_date,
                $header->received_time,
                $header->updated_at
            );
        }

        foreach ($consumed->get() as $header) {
            if ($header->items->isNotEmpty()) {
                foreach ($header->items as $item) {
                    if (! $this->itemMatchesFilters($request, $item)) {
                        continue;
                    }

                    $key = $this->variantKey(
                        $header->project_id,
                        $item->material_type_id,
                        $item->brand_master_id,
                        $item->material_specification_id,
                        $item->material_grade_id,
                        $item->unit_master_id
                    );

                    $this->initializeVariant(
                        $stock,
                        $key,
                        $header,
                        $item
                    );

                    $stock[$key]['consumed_qty'] +=
                        (float) $item->quantity_consumed;

                    $stock[$key]['wastage_qty'] +=
                        (float) $item->wastage_quantity;

                    $this->updateLatestMovement(
                        $stock[$key],
                        $header->consumed_date,
                        $header->consumed_time,
                        $header->updated_at
                    );
                }

                continue;
            }

            if (
                $request->filled('material_group')
                || $request->filled('material_type_id')
                || ! $header->material_id
            ) {
                continue;
            }

            $key = $this->legacyKey(
                $header->project_id,
                $header->material_id,
                $header->unit
            );

            $this->initializeLegacy(
                $stock,
                $key,
                $header,
                $header->material
            );

            $stock[$key]['consumed_qty'] +=
                (float) ($header->quantity_consumed ?? 0);

            $stock[$key]['wastage_qty'] +=
                (float) ($header->wastage_quantity ?? 0);

            $this->updateLatestMovement(
                $stock[$key],
                $header->consumed_date,
                $header->consumed_time,
                $header->updated_at
            );
        }

        $stockRows = collect($stock)
            ->map(function (array $row): array {
                $row['issued_qty'] =
                    $row['consumed_qty'] + $row['wastage_qty'];

                $row['balance_qty'] =
                    $row['received_qty'] - $row['issued_qty'];

                return $row;
            })
            ->filter(
                fn (array $row) =>
                    $row['received_qty'] != 0
                    || $row['issued_qty'] != 0
            )
            ->sortByDesc('latest_movement_at')
            ->values();

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

        $materialGroups = $materialTypes
            ->pluck('material_group')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view(
            'stock-register.index',
            compact(
                'stockRows',
                'projects',
                'projectBlocks',
                'materialTypes',
                'materialGroups'
            )
        );
    }

    private function itemMatchesFilters(
        Request $request,
        $item
    ): bool {
        if (
            $request->filled('material_group')
            && $item->materialType?->material_group
                !== $request->string('material_group')->toString()
        ) {
            return false;
        }

        if (
            $request->filled('material_type_id')
            && (int) $item->material_type_id
                !== $request->integer('material_type_id')
        ) {
            return false;
        }

        return true;
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

    private function updateLatestMovement(
        array &$row,
        mixed $date,
        mixed $time,
        mixed $fallback
    ): void {
        $dateValue = $date
            ? (string) $date
            : null;

        $timeValue = $time
            ? (string) $time
            : '00:00:00';

        $timestamp = $dateValue
            ? strtotime($dateValue . ' ' . $timeValue)
            : strtotime((string) $fallback);

        $timestamp = $timestamp ?: 0;

        if ($timestamp > ($row['latest_movement_at'] ?? 0)) {
            $row['latest_movement_at'] = $timestamp;
        }
    }

    private function variantKey(
        int $projectId,
        int $typeId,
        ?int $brandId,
        ?int $specificationId,
        ?int $gradeId,
        int $unitId
    ): string {
        return implode(':', [
            'new',
            $projectId,
            $typeId,
            $brandId ?? 0,
            $specificationId ?? 0,
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

    private function initializeVariant(
        array &$stock,
        string $key,
        $header,
        $item
    ): void {
        if (isset($stock[$key])) {
            return;
        }

        $stock[$key] = [
            'variant_key' => $key,
            'is_legacy' => false,
            'project_id' => $header->project_id,
            'project_name' =>
                $header->project?->project_name ?? '-',
            'project_block_id' => $header->project_block_id,
            'block_name' =>
                $header->block?->name ?? '-',
            'material_type_id' => $item->material_type_id,
            'material_type_name' =>
                $item->materialType?->material_type_name ?? '-',
            'material_group' =>
                $item->materialType?->material_group ?? '-',
            'brand_master_id' => $item->brand_master_id,
            'brand_name' =>
                $item->brand?->brand_name ?? '-',
            'material_specification_id' =>
                $item->material_specification_id,
            'specification_name' =>
                $item->specification?->specification_name ?? '-',
            'material_grade_id' =>
                $item->material_grade_id,
            'grade_name' =>
                $item->grade?->grade_name ?? '-',
            'unit_master_id' => $item->unit_master_id,
            'unit' =>
                $item->unit?->unit_name ?? '-',
            'material' => $item->materialType,
            'received_qty' => 0.0,
            'consumed_qty' => 0.0,
            'wastage_qty' => 0.0,
            'issued_qty' => 0.0,
            'balance_qty' => 0.0,
            'latest_movement_at' => 0,
        ];
    }

    private function initializeLegacy(
        array &$stock,
        string $key,
        $header,
        $material
    ): void {
        if (isset($stock[$key])) {
            return;
        }

        $stock[$key] = [
            'variant_key' => $key,
            'is_legacy' => true,
            'project_id' => $header->project_id,
            'project_name' =>
                $header->project?->project_name ?? '-',
            'project_block_id' => $header->project_block_id,
            'block_name' =>
                $header->block?->name ?? '-',
            'material_type_id' => null,
            'material_type_name' =>
                $material?->material_name ?? '-',
            'material_group' =>
                $material?->category?->category_name ?? 'Legacy',
            'brand_master_id' => null,
            'brand_name' => '-',
            'material_specification_id' => null,
            'specification_name' => '-',
            'material_grade_id' => null,
            'grade_name' => '-',
            'unit_master_id' => null,
            'unit' =>
                $header->unit ?? $material?->unit ?? '-',
            'material' => $material,
            'received_qty' => 0.0,
            'consumed_qty' => 0.0,
            'wastage_qty' => 0.0,
            'issued_qty' => 0.0,
            'balance_qty' => 0.0,
            'latest_movement_at' => 0,
        ];
    }
}
