<?php

namespace App\Exports;

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

class LabourAttendanceRegisterExport implements
    FromArray,
    WithColumnWidths,
    WithEvents,
    WithTitle
{
    private array $sheetRows = [];

    private array $projectHeaderRows = [];

    private array $tableHeaderRows = [];

    private array $projectTotalRows = [];

    private int $grandTotalRow = 0;

    private int $lastColumnIndex;

    public function __construct(
        private readonly Collection $projectGroups,
        private readonly Collection $dateColumns,
        private readonly string $periodLabel,
        private readonly array $summary,
        private readonly array $filters = []
    ) {
        $this->lastColumnIndex =
            2 + $this->dateColumns->count() + 6;

        $this->buildSheetRows();
    }

    public function array(): array
    {
        return $this->sheetRows;
    }

    public function title(): string
    {
        return 'Attendance Register';
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 25,
            'B' => 30,
        ];

        $columnIndex = 3;

        foreach ($this->dateColumns as $dateColumn) {
            $widths[
                Coordinate::stringFromColumnIndex(
                    $columnIndex
                )
            ] = 8;

            $columnIndex++;
        }

        foreach ([7, 7, 7, 7, 12, 10] as $width) {
            $widths[
                Coordinate::stringFromColumnIndex(
                    $columnIndex
                )
            ] = $width;

            $columnIndex++;
        }

        return $widths;
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

                    /*
                    |--------------------------------------------------------------------------
                    | Title
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
                            'size' => 15,
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
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);

                    $sheet->getRowDimension(1)
                        ->setRowHeight(25);

                    $sheet->getRowDimension(2)
                        ->setRowHeight(20);

                    /*
                    |--------------------------------------------------------------------------
                    | Project Headers
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $this->projectHeaderRows
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

                        $sheet->getRowDimension(
                            $rowNumber
                        )->setRowHeight(22);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Table Headers
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $this->tableHeaderRows
                        as $rowNumber
                    ) {
                        $sheet->getStyle(
                            "A{$rowNumber}:{$lastColumn}{$rowNumber}"
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

                        $sheet->getStyle(
                            "A{$rowNumber}:B{$rowNumber}"
                        )->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_LEFT
                            );

                        $sheet->getRowDimension(
                            $rowNumber
                        )->setRowHeight(30);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Data Grid
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        "A3:{$lastColumn}{$lastRow}"
                    )->applyFromArray([
                        'font' => [
                            'size' => 9,
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
                                    'rgb' => 'E2E8F0',
                                ],
                            ],
                        ],
                    ]);

                    $sheet->getStyle(
                        "A3:B{$lastRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_LEFT
                        );

                    $sheet->getStyle(
                        "C3:{$lastColumn}{$lastRow}"
                    )->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    foreach (
                        $this->projectTotalRows
                        as $rowNumber
                    ) {
                        $sheet->getStyle(
                            "A{$rowNumber}:{$lastColumn}{$rowNumber}"
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],
                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'F1F5F9',
                                ],
                            ],
                            'borders' => [
                                'top' => [
                                    'borderStyle' =>
                                        Border::BORDER_MEDIUM,
                                    'color' => [
                                        'rgb' => '64748B',
                                    ],
                                ],
                            ],
                        ]);
                    }

                    if ($this->grandTotalRow > 0) {
                        $sheet->getStyle(
                            "A{$this->grandTotalRow}:{$lastColumn}{$this->grandTotalRow}"
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 10,
                            ],
                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'CBD5E1',
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
                                        Border::BORDER_MEDIUM,
                                    'color' => [
                                        'rgb' => '475569',
                                    ],
                                ],
                            ],
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Status Colours
                    |--------------------------------------------------------------------------
                    */

                    $firstDateColumn = 3;

                    $lastDateColumn =
                        $firstDateColumn
                        + $this->dateColumns->count()
                        - 1;

                    for (
                        $rowNumber = 1;
                        $rowNumber <= $lastRow;
                        $rowNumber++
                    ) {
                        for (
                            $columnIndex = $firstDateColumn;
                            $columnIndex <= $lastDateColumn;
                            $columnIndex++
                        ) {
                            $coordinate =
                                Coordinate::stringFromColumnIndex(
                                    $columnIndex
                                ) . $rowNumber;

                            $value = strtoupper(
                                trim(
                                    (string) $sheet
                                        ->getCell($coordinate)
                                        ->getValue()
                                )
                            );

                            $style = match ($value) {
                                'P' => [
                                    'fill' => 'DCFCE7',
                                    'font' => '15803D',
                                ],

                                'A' => [
                                    'fill' => 'FEE2E2',
                                    'font' => 'B91C1C',
                                ],

                                'HD' => [
                                    'fill' => 'FEF3C7',
                                    'font' => 'B45309',
                                ],

                                'L' => [
                                    'fill' => 'DBEAFE',
                                    'font' => '1D4ED8',
                                ],

                                'WO' => [
                                    'fill' => 'E5E7EB',
                                    'font' => '4B5563',
                                ],

                                'H' => [
                                    'fill' => 'EDE9FE',
                                    'font' => '6D28D9',
                                ],

                                default => null,
                            };

                            if ($style === null) {
                                continue;
                            }

                            $sheet->getStyle(
                                $coordinate
                            )->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'color' => [
                                        'rgb' =>
                                            $style['font'],
                                    ],
                                ],
                                'fill' => [
                                    'fillType' =>
                                        Fill::FILL_SOLID,
                                    'startColor' => [
                                        'rgb' =>
                                            $style['fill'],
                                    ],
                                ],
                            ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Freeze and Print Setup
                    |--------------------------------------------------------------------------
                    */

                    $sheet->freezePane('C5');

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

                    $sheet->getPageSetup()
                        ->setRowsToRepeatAtTopByStartAndEnd(
                            1,
                            2
                        );

                    $sheet->getPageMargins()
                        ->setTop(0.25);

                    $sheet->getPageMargins()
                        ->setBottom(0.25);

                    $sheet->getPageMargins()
                        ->setLeft(0.2);

                    $sheet->getPageMargins()
                        ->setRight(0.2);
                },
        ];
    }

    private function buildSheetRows(): void
    {
        /*
         * Do not insert completely empty rows.
         * Laravel Excel may skip them, which causes styling row numbers
         * to move and can merge over the date heading row.
         */

        $this->sheetRows[] = [
            'Ravion ERP - Labour Attendance Register',
        ];

        $this->sheetRows[] = [
            $this->periodLabel,
        ];

        foreach ($this->projectGroups as $projectGroup) {
            $projectName =
                $projectGroup['project_name']
                ?? 'Unknown Project';

            $projectCode =
                $projectGroup['project_code']
                ?? null;

            $projectHeading = $projectName;

            if ($projectCode) {
                $projectHeading .=
                    " ({$projectCode})";
            }

            $projectHeading .=
                ' | Labour: '
                . (
                    $projectGroup['summary']['total_labour']
                    ?? 0
                )
                . ' | P: '
                . (
                    $projectGroup['summary']['present']
                    ?? 0
                )
                . ' | A: '
                . (
                    $projectGroup['summary']['absent']
                    ?? 0
                )
                . ' | Normal: '
                . number_format(
                    (float) (
                        $projectGroup['summary']['normal_hours']
                        ?? 0
                    ),
                    2
                )
                . ' | OT: '
                . number_format(
                    (float) (
                        $projectGroup['summary']['ot_hours']
                        ?? 0
                    ),
                    2
                );

            $this->projectHeaderRows[] =
                count($this->sheetRows) + 1;

            $this->sheetRows[] = [
                $projectHeading,
            ];

            $this->tableHeaderRows[] =
                count($this->sheetRows) + 1;

            $this->sheetRows[] =
                $this->columnHeaders();

            foreach (
                $projectGroup['rows']
                as $row
            ) {
                $excelRow = [
                    $row['labour_name']
                        ?? 'Unavailable Labour',

                    $row['designation']
                        ?? '—',
                ];

                foreach (
                    $this->dateColumns
                    as $dateColumn
                ) {
                    $dayEntry =
                        $row['days'][
                            $dateColumn['key']
                        ] ?? null;

                    $excelRow[] =
                        $dayEntry['code']
                        ?? '—';
                }

                $excelRow[] =
                    $row['totals']['present']
                    ?? 0;

                $excelRow[] =
                    $row['totals']['absent']
                    ?? 0;

                $excelRow[] =
                    $row['totals']['half_day']
                    ?? 0;

                $excelRow[] =
                    $row['totals']['leave']
                    ?? 0;

                $excelRow[] =
                    (float) (
                        $row['totals']['normal_hours']
                        ?? 0
                    );

                $excelRow[] =
                    (float) (
                        $row['totals']['ot_hours']
                        ?? 0
                    );

                $this->sheetRows[] =
                    $excelRow;
            }

            $this->projectTotalRows[] =
                count($this->sheetRows) + 1;

            $totalRow = [
                'Project Totals',

                (
                    $projectGroup['summary']['total_labour']
                    ?? 0
                ) . ' Labour',
            ];

            foreach (
                $this->dateColumns
                as $dateColumn
            ) {
                $totalRow[] = '';
            }

            $totalRow[] =
                $projectGroup['summary']['present']
                ?? 0;

            $totalRow[] =
                $projectGroup['summary']['absent']
                ?? 0;

            $totalRow[] =
                $projectGroup['summary']['half_day']
                ?? 0;

            $totalRow[] =
                $projectGroup['summary']['leave']
                ?? 0;

            $totalRow[] =
                (float) (
                    $projectGroup['summary']['normal_hours']
                    ?? 0
                );

            $totalRow[] =
                (float) (
                    $projectGroup['summary']['ot_hours']
                    ?? 0
                );

            $this->sheetRows[] =
                $totalRow;
        }

        $this->grandTotalRow =
            count($this->sheetRows) + 1;

        $grandTotal = [
            'Grand Total',

            (
                $this->summary['total_labour']
                ?? 0
            ) . ' Labour',
        ];

        foreach (
            $this->dateColumns
            as $dateColumn
        ) {
            $grandTotal[] = '';
        }

        $grandTotal[] =
            $this->summary['present']
            ?? 0;

        $grandTotal[] =
            $this->summary['absent']
            ?? 0;

        $grandTotal[] =
            $this->summary['half_day']
            ?? 0;

        $grandTotal[] =
            $this->summary['leave']
            ?? 0;

        $grandTotal[] =
            (float) (
                $this->summary['normal_hours']
                ?? 0
            );

        $grandTotal[] =
            (float) (
                $this->summary['ot_hours']
                ?? 0
            );

        $this->sheetRows[] =
            $grandTotal;
    }

    private function columnHeaders(): array
    {
        $headers = [
            'Labour Name',
            'Designation',
        ];

        foreach (
            $this->dateColumns
            as $dateColumn
        ) {
            $headers[] =
                $dateColumn['date']->format(
                    'd D'
                );
        }

        return [
            ...$headers,
            'P',
            'A',
            'HD',
            'L',
            'Normal Hrs',
            'OT Hrs',
        ];
    }
}
