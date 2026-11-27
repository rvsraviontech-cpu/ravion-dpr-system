<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class ClientSupportReportExport extends StringValueBinder implements FromArray,WithCustomValueBinder,WithStyles,WithColumnWidths {
 private array $rows=[]; private array $headings=[]; private int $columns=2;
 public function __construct(string $title,array $sections,string $generated,string $by) {
  $this->rows=[['RAVION | HOMES'],[$title],['Generated: '.$generated,'By: '.$by],['Confidential - Internal Management Report']];
  foreach($sections as $section){ $this->rows[]=[]; $this->rows[]=[$section['title']]; $this->headings[]=count($this->rows); $this->rows[]=$section['headers']; $this->headings[]=count($this->rows); $this->columns=max($this->columns,count($section['headers'])); foreach($section['rows'] as $row) $this->rows[]=$row; }
 }
 public function array():array{return $this->rows;}
 public function columnWidths():array { $widths=[]; for($i=1;$i<=$this->columns;$i++) $widths[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i)]=$this->columns>5?24:($i===2||$i===4?65:28); return $widths; }
 public function styles(Worksheet $sheet) { $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true)->setVertical('top'); $sheet->freezePane('A7'); foreach(array_merge([1,2],$this->headings) as $row){ $range='A'.$row.':'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->columns).$row; $sheet->getStyle($range)->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>'solid','startColor'=>['rgb'=>'204B75']]]); } }
}
