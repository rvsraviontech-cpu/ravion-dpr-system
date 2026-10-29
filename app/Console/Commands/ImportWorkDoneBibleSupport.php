<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ImportWorkDoneBibleSupport extends Command
{
    protected $signature = 'ravion:import-workdone-bible-support {file : Work Done Bible V5 workbook path} {--apply : Write validated masters}';
    protected $description = 'Validate/import the five Bible V5 reference master sheets (Step 1B)';

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (!is_file($file)) {
            $file = base_path($file);
        }
        if (!is_file($file)) {
            $this->error('Workbook not found.');
            return self::FAILURE;
        }

        // Sheet, destination table, natural key, required headings, mapped columns.
        $specs = [
            ['04_MATERIAL_LOGIC', 'work_done_bible_material_relations', 'code',
                ['Code', 'Relationship', 'Definition', 'Can Earn Qty', 'Track Consumption'],
                ['code'=>'Code', 'relationship'=>'Relationship', 'definition'=>'Definition', 'can_earn_qty'=>'Can Earn Qty', 'track_consumption'=>'Track Consumption']],
            ['05_MEASUREMENT_ENGINE', 'work_done_bible_measurement_methods', 'method',
                ['Method', 'Name', 'Default UOM', 'Logic'],
                ['method'=>'Method', 'name'=>'Name', 'default_uom'=>'Default UOM', 'logic'=>'Logic']],
            ['10_QA_GATE_MASTER', 'work_done_bible_qa_gates', 'gate_type',
                ['Gate Type', 'Behaviour'], ['gate_type'=>'Gate Type', 'behaviour'=>'Behaviour']],
            ['11_STATUS_WORKFLOW', 'work_done_bible_statuses', 'status',
                ['Status', 'Meaning'], ['status'=>'Status', 'meaning'=>'Meaning']],
            ['08_INTERFACE_MATRIX', 'work_done_bible_interfaces', 'primary_assembly',
                ['Primary Assembly', 'Mandatory Interfaces', 'PHP Logic'],
                ['primary_assembly'=>'Primary Assembly', 'mandatory_interfaces'=>'Mandatory Interfaces', 'php_logic'=>'PHP Logic']],
        ];

        try {
            $reader = IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $book = $reader->load($file);
            $validated = [];
            foreach ($specs as [$sheetName, $table, $key, $required, $mapping]) {
                $sheet = $book->getSheetByName($sheetName);
                if (!$sheet) throw new RuntimeException("Missing sheet: {$sheetName}");
                $rows = $sheet->toArray(null, true, true, false);
                $headings = array_map(fn ($v) => trim((string) $v), array_shift($rows) ?? []);
                if (count($headings) !== count(array_unique($headings))) {
                    throw new RuntimeException("Duplicate headings in {$sheetName}");
                }
                foreach ($required as $heading) {
                    if (!in_array($heading, $headings, true)) {
                        throw new RuntimeException("Missing heading '{$heading}' in {$sheetName}");
                    }
                }
                $data = []; $seen = [];
                foreach ($rows as $index => $values) {
                    if (!array_filter($values, fn ($v) => $v !== null && trim((string) $v) !== '')) continue;
                    $row = array_combine($headings, array_slice(array_pad($values, count($headings), null), 0, count($headings)));
                    $naturalHeading = $mapping[$key];
                    $natural = trim((string) ($row[$naturalHeading] ?? ''));
                    if ($natural === '' || isset($seen[$natural])) {
                        throw new RuntimeException("Blank/duplicate {$naturalHeading} in {$sheetName}, Excel row " . ($index + 2));
                    }
                    $seen[$natural] = true;
                    $mapped = [];
                    foreach ($mapping as $column => $heading) {
                        $value = trim((string) ($row[$heading] ?? ''));
                        $mapped[$column] = $value === '' ? null : $value;
                    }
                    $mapped[$key] = $natural;
                    $mapped['source_row'] = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                    $data[] = $mapped;
                }
                if (!$data) throw new RuntimeException("No master rows in {$sheetName}");
                $validated[] = [$sheetName, $table, $key, $data];
                $this->info("Validated {$sheetName}: " . count($data) . ' rows');
            }
            if (!$this->option('apply')) {
                $this->comment('Dry run only. No database writes. Use --apply after reviewing counts.');
                return self::SUCCESS;
            }
            DB::transaction(function () use ($validated) {
                foreach ($validated as [$sheetName, $table, $key, $data]) {
                    foreach ($data as $row) {
                        DB::table($table)->updateOrInsert([$key => $row[$key]], $row + ['created_at' => now(), 'updated_at' => now()]);
                    }
                }
            });
            $this->info('Step 1B reference masters imported; existing transactions and core Bible operations untouched.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
