<?php

namespace App\Exports;

use App\Models\WeeklyWageSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class WeeklyWageSheetExport implements
    FromArray,
    WithColumnWidths,
    WithEvents,
    WithTitle
{
    private array $sheetRows = [];

    private array $labourHeaderRows = [];

    private array $siteChargeHeaderRows = [];

    private array $summaryRows = [];

    private int $labourTableHeaderRow = 0;

    private int $labourTableStartRow = 0;

    private int $labourTableEndRow = 0;

    private int $siteChargeTableHeaderRow = 0;

    private int $siteChargeTableStartRow = 0;

    private int $siteChargeTableEndRow = 0;

    private int $lastColumnIndex = 11;

    public function __construct(
        private readonly WeeklyWageSheet $weeklyWageSheet
    ) {
        $this->weeklyWageSheet->loadMissing([
            'project:id,project_name,project_code',
            'details' => function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orderBy('id');
            },
            'details.labour:id,full_name,labour_group_id',
            'details.labour.labourGroup:id,code,name,sort_order',
            'details.designationRole:id,name',
            'charges' => function ($query): void {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
            'charges.activity',
            'charges.contractor:id,contractor_name',
            'generatedBy:id,name',
            'submittedBy:id,name',
            'approvedBy:id,name',
            'paidBy:id,name',
        ]);

        $this->buildSheetRows();
    }

    public function array(): array
    {
        return $this->sheetRows;
    }

    public function title(): string
    {
        return 'Weekly Wage Sheet';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 28,
            'C' => 16,
            'D' => 14,
            'E' => 14,
            'F' => 13,
            'G' => 13,
            'H' => 14,
            'I' => 14,
            'J' => 15,
            'K' => 22,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (AfterSheet $event): void {
                    $sheet = $event->sheet->getDelegate();

                    $lastColumn =
                        Coordinate::stringFromColumnIndex(
                            $this->lastColumnIndex
                        );

                    $lastRow = count($this->sheetRows);

                    foreach (range(1, $lastRow) as $rowNumber) {
                        $sheet->getRowDimension($rowNumber)
                            ->setRowHeight(-1);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | General Sheet Setup
                    |--------------------------------------------------------------------------
                    */

                    $sheet->setShowGridlines(false);

                    $sheet->getSheetView()
                        ->setZoomScale(90);

                    $sheet->getPageSetup()
                        ->setOrientation(
                            PageSetup::ORIENTATION_LANDSCAPE
                        );

                    $sheet->getPageSetup()
                        ->setPaperSize(
                            PageSetup::PAPERSIZE_A4
                        );

                    $sheet->getPageSetup()
                        ->setFitToWidth(1);

                    $sheet->getPageSetup()
                        ->setFitToHeight(1);

                    $sheet->getPageMargins()
                        ->setTop(0.3);

                    $sheet->getPageMargins()
                        ->setBottom(0.3);

                    $sheet->getPageMargins()
                        ->setLeft(0.2);

                    $sheet->getPageMargins()
                        ->setRight(0.2);

                    /*
                    |--------------------------------------------------------------------------
                    | Main Title
                    |--------------------------------------------------------------------------
                    */

                    $sheet->mergeCells(
                        "A1:{$lastColumn}1"
                    );

                    $sheet->mergeCells(
                        "A2:{$lastColumn}2"
                    );

                    $sheet->getStyle(
                        "A1:{$lastColumn}1"
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                            'color' => [
                                'rgb' => '0F172A',
                            ],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'DBEAFE',
                            ],
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,
                            'vertical' =>
                                Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $sheet->getStyle(
                        "A2:{$lastColumn}2"
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                            'color' => [
                                'rgb' => '334155',
                            ],
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,
                            'vertical' =>
                                Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $sheet->getRowDimension(1)
                        ->setRowHeight(26);

                    $sheet->getRowDimension(2)
                        ->setRowHeight(22);

                    /*
                    |--------------------------------------------------------------------------
                    | Meta Information
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        "A4:{$lastColumn}7"
                    )->applyFromArray([
                        'font' => [
                            'size' => 10,
                        ],
                        'alignment' => [
                            'vertical' =>
                                Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>
                                    Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => 'CBD5E1',
                                ],
                            ],
                        ],
                    ]);

                    $sheet->getStyle(
                        'A4:A7'
                    )->getFont()
                        ->setBold(true);

                    $sheet->getStyle(
                        'F4:F7'
                    )->getFont()
                        ->setBold(true);

                    $sheet->getStyle(
                        "A4:{$lastColumn}7"
                    )->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        );

                    $sheet->getStyle(
                        "A4:{$lastColumn}7"
                    )->getFill()
                        ->getStartColor()
                        ->setRGB('F8FAFC');

                    /*
                    |--------------------------------------------------------------------------
                    | Section Headers
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $this->labourHeaderRows
                        as $rowNumber
                    ) {
                        $sheet->mergeCells(
                            "A{$rowNumber}:{$lastColumn}{$rowNumber}"
                        );

                        $sheet->getStyle(
                            "A{$rowNumber}:{$lastColumn}{$rowNumber}"
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 11,
                                'color' => [
                                    'rgb' => '1E3A8A',
                                ],
                            ],
                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'EFF6FF',
                                ],
                            ],
                            'alignment' => [
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                    'color' => [
                                        'rgb' => '93C5FD',
                                    ],
                                ],
                            ],
                        ]);
                    }

                    foreach (
                        $this->siteChargeHeaderRows
                        as $rowNumber
                    ) {
                        $sheet->mergeCells(
                            "A{$rowNumber}:{$lastColumn}{$rowNumber}"
                        );

                        $sheet->getStyle(
                            "A{$rowNumber}:{$lastColumn}{$rowNumber}"
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 11,
                                'color' => [
                                    'rgb' => '7C2D12',
                                ],
                            ],
                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'FFF7ED',
                                ],
                            ],
                            'alignment' => [
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                    'color' => [
                                        'rgb' => 'FDBA74',
                                    ],
                                ],
                            ],
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Labour Table
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        "A{$this->labourTableHeaderRow}:{$lastColumn}{$this->labourTableHeaderRow}"
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 9,
                        ],
                        'fill' => [
                            'fillType' =>
                                Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'F3F4F6',
                            ],
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,
                            'vertical' =>
                                Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>
                                    Border::BORDER_THIN,
                                'color' => [
                                    'rgb' => 'CBD5E1',
                                ],
                            ],
                        ],
                    ]);

                    if (
                        $this->labourTableStartRow > 0
                        && $this->labourTableEndRow
                            >= $this->labourTableStartRow
                    ) {
                        $sheet->getStyle(
                            "A{$this->labourTableStartRow}:{$lastColumn}{$this->labourTableEndRow}"
                        )->applyFromArray([
                            'font' => [
                                'size' => 9,
                            ],
                            'alignment' => [
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                    'color' => [
                                        'rgb' => 'E2E8F0',
                                    ],
                                ],
                            ],
                        ]);

                        $sheet->getStyle(
                            "C{$this->labourTableStartRow}:J{$this->labourTableEndRow}"
                        )->getNumberFormat()
                            ->setFormatCode(
                                '#,##0.00'
                            );

                        $sheet->getStyle(
                            "C{$this->labourTableStartRow}:J{$this->labourTableEndRow}"
                        )->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_RIGHT
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Site Charge Table
                    |--------------------------------------------------------------------------
                    */

                    if ($this->siteChargeTableHeaderRow > 0) {
                        $sheet->getStyle(
                            "A{$this->siteChargeTableHeaderRow}:{$lastColumn}{$this->siteChargeTableHeaderRow}"
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 9,
                            ],
                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'F3F4F6',
                                ],
                            ],
                            'alignment' => [
                                'horizontal' =>
                                    Alignment::HORIZONTAL_CENTER,
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                    'color' => [
                                        'rgb' => 'CBD5E1',
                                    ],
                                ],
                            ],
                        ]);
                    }

                    if (
                        $this->siteChargeTableStartRow > 0
                        && $this->siteChargeTableEndRow
                            >= $this->siteChargeTableStartRow
                    ) {
                        $sheet->getStyle(
                            "A{$this->siteChargeTableStartRow}:{$lastColumn}{$this->siteChargeTableEndRow}"
                        )->applyFromArray([
                            'font' => [
                                'size' => 9,
                            ],
                            'alignment' => [
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                    'color' => [
                                        'rgb' => 'E2E8F0',
                                    ],
                                ],
                            ],
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Summary Rows
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $this->summaryRows
                        as $rowNumber
                    ) {
                        $sheet->getStyle(
                            "A{$rowNumber}:{$lastColumn}{$rowNumber}"
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 10,
                            ],
                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'E2E8F0',
                                ],
                            ],
                            'borders' => [
                                'top' => [
                                    'borderStyle' =>
                                        Border::BORDER_MEDIUM,
                                    'color' => [
                                        'rgb' => '475569',
                                    ],
                                ],
                                'bottom' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                    'color' => [
                                        'rgb' => '94A3B8',
                                    ],
                                ],
                            ],
                        ]);

                        $sheet->getStyle(
                            "J{$rowNumber}:K{$rowNumber}"
                        )->getNumberFormat()
                            ->setFormatCode(
                                '#,##0.00'
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Footer
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getHeaderFooter()
                        ->setOddFooter(
                            '&LGenerated from Ravion ERP'
                            . '&RPage &P of &N'
                        );

                    $sheet->getStyle(
                        "A1:{$lastColumn}{$lastRow}"
                    )->getAlignment()
                        ->setVertical(
                            Alignment::VERTICAL_CENTER
                        );
                },
        ];
    }

    private function buildSheetRows(): void
    {
        $sheet = $this->weeklyWageSheet;

        $projectName =
            $sheet->project?->project_name
            ?? 'Unknown Project';

        $projectCode =
            $sheet->project?->project_code;

        $periodLabel =
            optional($sheet->week_start_date)->format('d M Y')
            . ' to '
            . optional($sheet->week_end_date)->format('d M Y');

        $this->sheetRows[] = [
            'Ravion ERP - Weekly Wage Sheet',
        ];

        $this->sheetRows[] = [
            $projectName
            . (
                $projectCode
                    ? " ({$projectCode})"
                    : ''
            )
            . ' | '
            . $periodLabel,
        ];

        $this->sheetRows[] = [''];

        $this->sheetRows[] = [
            'Wage Sheet Number',
            $sheet->wage_sheet_number,
            '',
            '',
            '',
            'Status',
            ucfirst((string) $sheet->status),
        ];

        $this->sheetRows[] = [
            'Project',
            $projectName,
            '',
            '',
            '',
            'Week',
            $periodLabel,
        ];

        $this->sheetRows[] = [
            'Generated By',
            $sheet->generatedBy?->name ?? '—',
            '',
            '',
            '',
            'Approved By',
            $sheet->approvedBy?->name ?? '—',
        ];

        $this->sheetRows[] = [
            'Payment Status',
            ucfirst((string) $sheet->status),
            '',
            '',
            '',
            'Payment Reference',
            $sheet->payment_reference ?? '—',
        ];

        $this->sheetRows[] = [''];

        $this->labourHeaderRows[] =
            count($this->sheetRows) + 1;

        $this->sheetRows[] = [
            'Labour Wage Calculations',
        ];

        $this->labourTableHeaderRow =
            count($this->sheetRows) + 1;

        $this->sheetRows[] = [
            '#',
            'Labour / Designation',
            'Daily Rate',
            'Payable Days',
            'Normal Wage',
            'OT Hours',
            'OT Rate',
            'OT Wage',
            'Additions',
            'Deductions',
            'Net Payable',
        ];

        $this->labourTableStartRow =
            count($this->sheetRows) + 1;

        $groupedDetails = $sheet->details
            ->sortBy(fn ($detail) => sprintf(
                '%08d|%s|%s',
                (int) ($detail->labour?->labourGroup?->sort_order ?? 999999),
                strtolower((string) ($detail->labour?->labourGroup?->name ?? 'Un-grouped Labour')),
                strtolower((string) ($detail->labour?->full_name ?? ''))
            ))
            ->groupBy(fn ($detail) => $detail->labour?->labourGroup?->name ?? 'Un-grouped Labour');

        $serial = 1;

        foreach ($groupedDetails as $groupName => $groupDetails) {
            $this->labourHeaderRows[] = count($this->sheetRows) + 1;
            $this->sheetRows[] = [
                'Labour Group: ' . $groupName . ' | Labour: ' . $groupDetails->count(),
            ];

            foreach ($groupDetails as $detail) {
                $labourLabel = $detail->labour?->full_name ?? 'Unavailable Labour';
                $designation = $detail->designationRole?->name;

                if ($designation) {
                    $labourLabel .= "\n{$designation}";
                }

                $this->sheetRows[] = [
                    $serial++,
                    $labourLabel,
                    (float) $detail->daily_wage_rate,
                    (float) $detail->payable_days,
                    (float) $detail->normal_wage,
                    (float) $detail->ot_hours,
                    (float) $detail->ot_hourly_rate,
                    (float) $detail->ot_wage,
                    (float) $detail->additions,
                    (float) $detail->deductions,
                    (float) $detail->net_payable,
                ];
            }

            $this->summaryRows[] = count($this->sheetRows) + 1;
            $this->sheetRows[] = [
                '',
                $groupName . ' - Group Wage Total',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                (float) $groupDetails->sum('net_payable'),
            ];
        }

        $this->labourTableEndRow =
            count($this->sheetRows);

        $this->summaryRows[] =
            count($this->sheetRows) + 1;

        $this->sheetRows[] = [
            '',
            'Labour Totals',
            '',
            (float) $sheet->total_payable_days,
            (float) $sheet->total_normal_wages,
            (float) $sheet->total_ot_hours,
            '',
            (float) $sheet->total_ot_wages,
            (float) $sheet->total_labour_additions,
            (float) $sheet->total_labour_deductions,
            (float) $sheet->net_labour_wages,
        ];

        $this->sheetRows[] = [''];

        $this->siteChargeHeaderRows[] =
            count($this->sheetRows) + 1;

        $this->sheetRows[] = [
            'Site Additional Charges',
        ];

        $this->siteChargeTableHeaderRow =
            count($this->sheetRows) + 1;

        $this->sheetRows[] = [
            '#',
            'Charge Type',
            'Description',
            'Activity',
            'Contractor',
            '',
            '',
            '',
            '',
            'Amount',
            'Remarks',
        ];

        $this->siteChargeTableStartRow =
            count($this->sheetRows) + 1;

        if ($sheet->charges->isEmpty()) {
            $this->sheetRows[] = [
                '',
                'No site charges added',
            ];
        } else {
            foreach (
                $sheet->charges
                as $index => $charge
            ) {
                $this->sheetRows[] = [
                    $index + 1,
                    $charge->charge_type,
                    $charge->description ?? '—',
                    $charge->activity?->activity_name ?? '—',
                    $charge->contractor?->contractor_name ?? '—',
                    '',
                    '',
                    '',
                    '',
                    (float) $charge->amount,
                    $charge->remarks ?? '—',
                ];
            }
        }

        $this->siteChargeTableEndRow =
            count($this->sheetRows);

        $this->summaryRows[] =
            count($this->sheetRows) + 1;

        $this->sheetRows[] = [
            '',
            'Site Charges Total',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            (float) $sheet->total_site_charges,
            '',
        ];

        $this->sheetRows[] = [''];

        $this->summaryRows[] =
            count($this->sheetRows) + 1;

        $this->sheetRows[] = [
            '',
            'Total Project Payable',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            (float) $sheet->total_project_payable,
        ];

        $this->sheetRows[] = [''];

        $this->sheetRows[] = [
            'Prepared By',
            $sheet->generatedBy?->name ?? '—',
            '',
            'Submitted By',
            $sheet->submittedBy?->name ?? '—',
            '',
            'Approved By',
            $sheet->approvedBy?->name ?? '—',
            '',
            'Paid By',
            $sheet->paidBy?->name ?? '—',
        ];
    }
}
