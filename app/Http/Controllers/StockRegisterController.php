<?php

namespace App\Http\Controllers;

use App\Models\MaterialConsumed;
use App\Models\MaterialReceived;
use App\Models\MaterialType;
use App\Models\Project;
use App\Models\ProjectBlock;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class StockRegisterController extends Controller
{
    public function index(Request $request)
    {
        $stock = [];

        $received = MaterialReceived::query()
            ->with([
                'project',
                'block',
                'items.materialType',
                'items.materialVariant',
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
                'items.materialVariant',
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
                        $item->unit_master_id,
                        $item->material_variant_id ? (int) $item->material_variant_id : null
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
                        $item->unit_master_id,
                        $item->material_variant_id ? (int) $item->material_variant_id : null
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

        $allRows = collect($stock)->map(function (array $row): array {
            $row['issued_qty'] = $row['consumed_qty'] + $row['wastage_qty'];
            $row['balance_qty'] = $row['received_qty'] - $row['issued_qty'];
            $row['material_label'] = $row['material_type_name'];
            $row['specification_display'] = $row['specification_name'];
            if (!empty($row['material_variant_id'])) {
                $variant = $row['variant_name'] ?? '';
                $row['material_label'] = $row['base_material_name'] ?? $row['material_type_name'];
                $row['specification_display'] = trim(implode(' · ', array_filter([
                    $variant, $row['variant_size'] ?? null,
                    ($row['specification_name'] ?? '-') !== '-' ? $row['specification_name'] : null,
                    ($row['grade_name'] ?? '-') !== '-' ? $row['grade_name'] : null,
                    ($row['brand_name'] ?? '-') !== '-' ? $row['brand_name'] : null,
                ]))) ?: '-';
            } else {
                $row['specification_display'] = trim(implode(' · ', array_filter([
                    ($row['specification_name'] ?? '-') !== '-' ? $row['specification_name'] : null,
                    ($row['grade_name'] ?? '-') !== '-' ? $row['grade_name'] : null,
                    ($row['brand_name'] ?? '-') !== '-' ? $row['brand_name'] : null,
                ]))) ?: '-';
            }
            return $row;
        })->filter(fn (array $row) => $row['received_qty'] != 0 || $row['issued_qty'] != 0);

        $request->validate([
            'project_id' => ['nullable', 'integer'],
            'material_item' => ['nullable', 'string', 'max:80'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'stock_status' => ['nullable', 'in:all,available,zero,negative'],
            'export' => ['nullable', 'in:pdf,xlsx'],
        ]);

        // The project is a parent filter: only offer stock items belonging
        // to the selected project, regardless of the current material filter.
        // This also keeps the picker useful when the report has date/status filters.
        $itemOptions = $allRows->map(fn ($row) => [
            'key' => !empty($row['material_type_id']) ? 'type:'.$row['material_type_id'] : 'legacy:'.($row['legacy_material_id'] ?? 0),
            'label' => $row['base_material_name'] ?? $row['material_type_name'],
        ])->unique('key')->sortBy('label')->values();

        $stockRows = $allRows->filter(function (array $row) use ($request): bool {
            if ($request->filled('material_item')) {
                $key = !empty($row['material_type_id']) ? 'type:'.$row['material_type_id'] : 'legacy:'.($row['legacy_material_id'] ?? 0);
                if ($key !== $request->input('material_item')) return false;
            }
            $balance = (float) $row['balance_qty'];
            $status = $request->input('stock_status', 'all');
            if ($status === 'available' && $balance <= 0) return false;
            if ($status === 'zero' && $balance != 0) return false;
            if ($status === 'negative' && $balance >= 0) return false;
            $timestamp = (int) ($row['latest_movement_at'] ?? 0);
            if ($request->filled('date_from') && $timestamp < strtotime($request->input('date_from').' 00:00:00')) return false;
            if ($request->filled('date_to') && $timestamp > strtotime($request->input('date_to').' 23:59:59')) return false;
            return true;
        })->sort(function ($a, $b) {
            $dateCompare = ($b['latest_movement_at'] ?? 0) <=> ($a['latest_movement_at'] ?? 0);
            return $dateCompare ?: strcmp($a['variant_key'], $b['variant_key']);
        })->values();

        $projects = Project::query()->where('status', 'Active')->orderBy('project_name')->get();
        $availableStockCount = $stockRows->filter(fn ($row) => (float) $row['balance_qty'] > 0)->count();

        if ($request->input('export') === 'pdf') {
            return $this->exportPdf($stockRows, $request);
        }
        if ($request->input('export') === 'xlsx') {
            return $this->exportExcel($stockRows, $request);
        }

        return view('stock-register.index', compact('stockRows', 'projects', 'itemOptions', 'availableStockCount'));
    }

    private function itemMatchesFilters(Request $request, $item): bool
    {
        return true; // Filter material identity after aggregation; retain all movements for accurate balances.
    }

    private function exportPdf($rows, Request $request)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            abort(503, 'PDF export requires barryvdh/laravel-dompdf. Install it using composer require barryvdh/laravel-dompdf.');
        }
        $groups = $rows->groupBy('project_name');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('stock-register.export-pdf', [
            'groups' => $groups,
            'requestFilters' => $request->only('project_id','material_item','date_from','date_to','stock_status'),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');
        return $pdf->download('ravion-stock-register-'.now()->format('Ymd-His').'.pdf');
    }

    private function exportExcel($rows, Request $request)
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            abort(503, 'Excel export requires phpoffice/phpspreadsheet (usually installed with Laravel Excel).');
        }
        $workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $workbook->getActiveSheet();
        $sheet->setTitle('Stock Register');
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'RAVION | STOCK REGISTER');
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Generated '.now()->format('d M Y h:i A').' | Latest approved movement; current balances');
        $headers = ['#','Date','Project','Material','Specification','Unit','Received','Consumed','Wastage','Available'];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true)->setSize(17);
        $sheet->getStyle('A4:J4')->getFont()->setBold(true);
        $sheet->getStyle('A4:J4')->getFill()->setFillType('solid')->getStartColor()->setRGB('DCEAF6');
        $line = 5;
        foreach ($rows->groupBy('project_name') as $project => $projectRows) {
            $sheet->mergeCells("A{$line}:J{$line}");
            $sheet->setCellValue("A{$line}", 'PROJECT: '.$project);
            $sheet->getStyle("A{$line}:J{$line}")->getFont()->setBold(true);
            $sheet->getStyle("A{$line}:J{$line}")->getFill()->setFillType('solid')->getStartColor()->setRGB('EAF1F7');
            $line++;
            foreach ($projectRows as $row) {
                $sheet->fromArray([
                    $line - 4, $row['latest_movement_at'] ? date('d M Y', $row['latest_movement_at']) : '-',
                    $row['project_name'], $row['material_label'], $row['specification_display'], $row['unit'],
                    (float)$row['received_qty'],(float)$row['consumed_qty'],(float)$row['wastage_qty'],(float)$row['balance_qty'],
                ], null, 'A'.$line);
                $line++;
            }
        }
        foreach (range('A','J') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        $sheet->freezePane('A5');
        $sheet->getStyle('G5:J'.$line)->getNumberFormat()->setFormatCode('#,##0.###;[Red](#,##0.###)');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook);
        $temporary = tempnam(sys_get_temp_dir(), 'ravion_stock_');
        $writer->save($temporary);
        $workbook->disconnectWorksheets();
        return response()->download($temporary, 'ravion-stock-register-'.now()->format('Ymd-His').'.xlsx')->deleteFileAfterSend(true);
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
        // Eloquent date casts may stringify as "YYYY-MM-DD 00:00:00".
        // Appending a separate time to that full datetime makes strtotime() fail.
        // Parse the calendar date and transaction time independently.
        $timestamp = 0;

        try {
            if ($date) {
                $calendarDate = Carbon::parse($date)->format('Y-m-d');
                $clockTime = $time
                    ? Carbon::parse((string) $time)->format('H:i:s')
                    : '00:00:00';
                $timestamp = Carbon::parse($calendarDate . ' ' . $clockTime)->timestamp;
            } elseif ($fallback) {
                $timestamp = Carbon::parse($fallback)->timestamp;
            }
        } catch (\Throwable $exception) {
            // Historical rows may have incomplete dates; use their update time.
            try {
                $timestamp = $fallback ? Carbon::parse($fallback)->timestamp : 0;
            } catch (\Throwable $ignored) {
                $timestamp = 0;
            }
        }

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
        int $unitId,
        ?int $materialVariantId = null
    ): string {
        return implode(':', [
            'new',
            $projectId,
            $typeId,
            $brandId ?? 0,
            $specificationId ?? 0,
            $gradeId ?? 0,
            $unitId,
            $materialVariantId === null ? 'legacy' : 'v' . $materialVariantId,
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
            'material_variant_id' => $item->material_variant_id,
            'variant_name' => $item->materialVariant?->variant_name,
            'variant_size' => $item->materialVariant?->size_dimension,
            'base_material_name' => $item->materialType?->material_type_name ?? '-',
            'material_type_name' => $item->materialType?->material_type_name ?? '-',
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
            'legacy_material_id' => $header->material_id,
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
