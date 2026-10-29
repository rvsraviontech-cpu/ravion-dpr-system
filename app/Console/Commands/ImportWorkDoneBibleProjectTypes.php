<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

class ImportWorkDoneBibleProjectTypes extends Command
{
    protected $signature = 'ravion:import-workdone-bible-project-types {file : Path to Bible V5 workbook} {--apply : Commit validated project type masters}';
    protected $description = 'Validate/import the 20_PROJECT_TYPE_TEMPLATES sheet without activating project operations';

    public function handle(): int
    {
        try {
            $file = (string) $this->argument('file');
            if (!is_file($file)) $file = base_path($file);
            if (!is_file($file)) throw new RuntimeException('Workbook not found.');

            $reader = IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly(['20_PROJECT_TYPE_TEMPLATES']);
            $book = $reader->load($file);
            $sheet = $book->getSheetByName('20_PROJECT_TYPE_TEMPLATES');
            if (!$sheet) throw new RuntimeException('Missing 20_PROJECT_TYPE_TEMPLATES sheet.');

            $rows = $sheet->toArray(null, true, true, false);
            $headers = array_map(fn ($v) => trim((string) $v), array_shift($rows) ?? []);
            $required = ['Code', 'Project Type', 'Activation Scope'];
            if (count($headers) !== count(array_unique($headers))) throw new RuntimeException('Duplicate column headers.');
            foreach ($required as $column) {
                if (!in_array($column, $headers, true)) throw new RuntimeException("Missing column: {$column}");
            }

            $items = []; $seen = [];
            foreach ($rows as $i => $values) {
                if (!array_filter($values, fn ($v) => $v !== null && trim((string) $v) !== '')) continue;
                $values = array_slice(array_pad($values, count($headers), null), 0, count($headers));
                $r = array_combine($headers, $values);
                $code = trim((string) ($r['Code'] ?? ''));
                $type = trim((string) ($r['Project Type'] ?? ''));
                $scope = trim((string) ($r['Activation Scope'] ?? ''));
                if ($code === '' || $type === '' || $scope === '') throw new RuntimeException('Missing required field at Excel row '.($i + 2));
                if (isset($seen[$code])) throw new RuntimeException("Duplicate project type code: {$code}");
                $seen[$code] = true;
                $items[] = [
                    'code' => $code,
                    'project_type' => $type,
                    'activation_scope' => $scope,
                    'source_row' => json_encode($r, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ];
            }
            if (count($items) !== 9) throw new RuntimeException('Expected 9 Bible project types, found '.count($items));
            $this->info('Validated 20_PROJECT_TYPE_TEMPLATES: 9 project types (PT01–PT09).');
            $this->warn('Activation Scope is descriptive; this sheet does not map individual WD codes to project types.');
            if (!$this->option('apply')) {
                $this->comment('Dry run only. No database writes.');
                return self::SUCCESS;
            }
            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    DB::table('work_done_bible_project_types')->updateOrInsert(
                        ['code' => $item['code']],
                        array_merge($item, ['updated_at' => now()])
                    );
                }
            });
            $this->info('Imported 9 Bible project types. No project operations were activated.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
