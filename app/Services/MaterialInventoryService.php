<?php

namespace App\Services;

use App\Models\MaterialConsumed;
use App\Models\MaterialReceived;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MaterialInventoryService
{
    public function projectInventory(int $projectId, bool $positiveAvailableOnly = false): Collection
    {
        $stock = [];

        $received = MaterialReceived::query()
            ->with(['items.materialType', 'items.brand', 'items.specification', 'items.grade', 'items.unit'])
            ->where('project_id', $projectId)
            ->whereIn('status', ['Approved', 'approved'])
            ->get();

        foreach ($received as $header) {
            foreach ($header->items as $item) {
                if (! $item->material_type_id || ! $item->unit_master_id) {
                    continue;
                }

                $key = $this->variantKey(
                    (int) $header->project_id,
                    (int) $item->material_type_id,
                    $item->brand_master_id ? (int) $item->brand_master_id : null,
                    $item->material_specification_id ? (int) $item->material_specification_id : null,
                    $item->material_grade_id ? (int) $item->material_grade_id : null,
                    (int) $item->unit_master_id
                );

                $this->ensureRow($stock, $key, (int) $header->project_id, $item);
                $stock[$key]['received_qty'] += $this->effectiveReceivedQuantity($item);
            }
        }

        $consumptions = MaterialConsumed::query()
            ->with(['items.materialType', 'items.brand', 'items.specification', 'items.grade', 'items.unit'])
            ->where('project_id', $projectId)
            ->whereIn('status', ['Approved', 'approved', 'Submitted', 'submitted'])
            ->get();

        foreach ($consumptions as $header) {
            $isApproved = strcasecmp((string) $header->status, 'Approved') === 0;
            $isSubmitted = strcasecmp((string) $header->status, 'Submitted') === 0;

            foreach ($header->items as $item) {
                if (! $item->material_type_id || ! $item->unit_master_id) {
                    continue;
                }

                $key = $this->variantKey(
                    (int) $header->project_id,
                    (int) $item->material_type_id,
                    $item->brand_master_id ? (int) $item->brand_master_id : null,
                    $item->material_specification_id ? (int) $item->material_specification_id : null,
                    $item->material_grade_id ? (int) $item->material_grade_id : null,
                    (int) $item->unit_master_id
                );

                $this->ensureRow($stock, $key, (int) $header->project_id, $item);

                $consumed = (float) $item->quantity_consumed;
                $wastage = (float) $item->wastage_quantity;

                if ($isApproved) {
                    $stock[$key]['approved_consumed_qty'] += $consumed;
                    $stock[$key]['approved_wastage_qty'] += $wastage;
                } elseif ($isSubmitted) {
                    $stock[$key]['submitted_consumed_qty'] += $consumed;
                    $stock[$key]['submitted_wastage_qty'] += $wastage;
                }
            }
        }

        $rows = collect($stock)->map(function (array $row): array {
            $row['approved_issued_qty'] = $row['approved_consumed_qty'] + $row['approved_wastage_qty'];
            $row['book_stock_qty'] = $row['received_qty'] - $row['approved_issued_qty'];
            $row['reserved_qty'] = $row['submitted_consumed_qty'] + $row['submitted_wastage_qty'];
            $row['available_qty'] = $row['book_stock_qty'] - $row['reserved_qty'];

            foreach ([
                'received_qty', 'approved_consumed_qty', 'approved_wastage_qty',
                'approved_issued_qty', 'submitted_consumed_qty', 'submitted_wastage_qty',
                'reserved_qty', 'book_stock_qty', 'available_qty',
            ] as $field) {
                $row[$field] = round((float) $row[$field], 3);
            }

            return $row;
        })->values();

        if ($positiveAvailableOnly) {
            $rows = $rows->filter(fn (array $row): bool => $row['available_qty'] > 0)->values();
        }

        return $rows->sortBy([
            ['material_group', 'asc'],
            ['material_type_name', 'asc'],
            ['brand_name', 'asc'],
            ['specification_name', 'asc'],
            ['grade_name', 'asc'],
            ['unit_name', 'asc'],
        ])->values();
    }

    public function availableForConsumption(int $projectId): Collection
    {
        return $this->projectInventory($projectId, true);
    }

    public function availableQuantity(
        int $projectId,
        int $materialTypeId,
        ?int $brandId,
        ?int $specificationId,
        ?int $gradeId,
        int $unitId
    ): float {
        $key = $this->variantKey(
            $projectId,
            $materialTypeId,
            $brandId,
            $specificationId,
            $gradeId,
            $unitId
        );

        $row = $this->projectInventory($projectId)->firstWhere('stock_key', $key);

        return $row ? (float) $row['available_qty'] : 0.0;
    }

    public function validateAvailableStock(int $projectId, array $items): void
    {
        $requested = [];

        foreach (array_values($items) as $index => $item) {
            $key = $this->variantKey(
                $projectId,
                (int) $item['material_type_id'],
                ! empty($item['brand_master_id']) ? (int) $item['brand_master_id'] : null,
                ! empty($item['material_specification_id']) ? (int) $item['material_specification_id'] : null,
                ! empty($item['material_grade_id']) ? (int) $item['material_grade_id'] : null,
                (int) $item['unit_master_id']
            );

            $requested[$key] ??= ['quantity' => 0.0, 'first_row_index' => $index];
            $requested[$key]['quantity'] +=
                (float) $item['quantity_consumed'] + (float) ($item['wastage_quantity'] ?? 0);
        }

        $inventory = $this->projectInventory($projectId)->keyBy('stock_key');
        $errors = [];

        foreach ($requested as $key => $request) {
            $inventoryRow = $inventory->get($key);
            $available = $inventoryRow ? (float) $inventoryRow['available_qty'] : 0.0;

            if ($request['quantity'] <= $available + 0.000001) {
                continue;
            }

            $index = $request['first_row_index'];
            $rowNumber = $index + 1;
            $label = $inventoryRow ? $this->stockLabel($inventoryRow) : 'the selected material combination';

            $errors["items.{$index}.quantity_consumed"][] =
                "Row {$rowNumber}: requested stock-out {$this->formatQuantity($request['quantity'])} "
                . "exceeds available stock {$this->formatQuantity($available)} for {$label}.";
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function variantKey(
        int $projectId,
        int $materialTypeId,
        ?int $brandId,
        ?int $specificationId,
        ?int $gradeId,
        int $unitId
    ): string {
        return implode(':', [
            'new',
            $projectId,
            $materialTypeId,
            $brandId ?? 0,
            $specificationId ?? 0,
            $gradeId ?? 0,
            $unitId,
        ]);
    }

    private function ensureRow(array &$stock, string $key, int $projectId, mixed $item): void
    {
        if (isset($stock[$key])) {
            return;
        }

        $stock[$key] = [
            'stock_key' => $key,
            'project_id' => $projectId,
            'material_type_id' => (int) $item->material_type_id,
            'material_type_name' => $item->materialType?->material_type_name,
            'material_group' => $item->materialType?->material_group,
            'brand_master_id' => $item->brand_master_id ? (int) $item->brand_master_id : null,
            'brand_name' => $item->brand?->brand_name,
            'material_specification_id' => $item->material_specification_id ? (int) $item->material_specification_id : null,
            'specification_name' => $item->specification?->specification_name,
            'material_grade_id' => $item->material_grade_id ? (int) $item->material_grade_id : null,
            'grade_name' => $item->grade?->grade_name,
            'unit_master_id' => (int) $item->unit_master_id,
            'unit_name' => $item->unit?->unit_name,
            'received_qty' => 0.0,
            'approved_consumed_qty' => 0.0,
            'approved_wastage_qty' => 0.0,
            'submitted_consumed_qty' => 0.0,
            'submitted_wastage_qty' => 0.0,
        ];
    }

    private function effectiveReceivedQuantity(mixed $item): float
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

    private function stockLabel(array $row): string
    {
        return collect([
            $row['material_type_name'] ?? null,
            $row['brand_name'] ?? null,
            $row['specification_name'] ?? null,
            $row['grade_name'] ?? null,
            $row['unit_name'] ?? null,
        ])->filter(fn ($value): bool => $value !== null && $value !== '')->implode(' / ');
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 3, '.', ''), '0'), '.');
    }
}
