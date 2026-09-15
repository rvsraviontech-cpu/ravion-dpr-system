<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use XMLReader;
use ZipArchive;

class MaterialBibleAudit extends Command
{
    protected $signature = 'materials:bible-audit
        {file : Full path to Ravion Material Bible .xlsx}
        {--sheet=Material_Master_Final : Worksheet containing the final material master}
        {--report= : Optional report directory; defaults to storage/app/material-bible-audit/<timestamp>}';

    protected $description = 'READ-ONLY audit of the Ravion Material Bible against the current Materials Product Master.';

    private array $headers = [
        'Seq', 'Trade Category', 'Main Category', 'Sub Category', 'Item',
        'Sub Item / Variant', 'Size / Grade / Finish / Specification', 'Unit',
        'Item Nature', 'Used In / Application', 'Common Site Names / Search Aliases',
        'Daily Use / Fast Moving', 'High Value / Controlled',
    ];

    public function handle(): int
    {
        $file = realpath((string) $this->argument('file'));
        if (! $file || ! is_file($file)) {
            $this->error('Bible file not found.');
            return self::FAILURE;
        }

        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'xlsx') {
            $this->error('The Bible audit expects an .xlsx file.');
            return self::FAILURE;
        }

        $reportDir = $this->option('report')
            ? rtrim((string) $this->option('report'), DIRECTORY_SEPARATOR)
            : storage_path('app/material-bible-audit/'.now()->format('Ymd_His'));

        if (! is_dir($reportDir) && ! mkdir($reportDir, 0775, true) && ! is_dir($reportDir)) {
            throw new RuntimeException("Unable to create report directory: {$reportDir}");
        }

        $this->newLine();
        $this->info('Ravion Material Bible Audit');
        $this->warn('READ-ONLY MODE — this command does not INSERT, UPDATE, DELETE or deactivate database records.');
        $this->line('File: '.$file);
        $this->line('Sheet: '.$this->option('sheet'));
        $this->newLine();

        $rows = $this->readBible($file, (string) $this->option('sheet'));
        $audit = $this->auditRows($rows);
        $db = $this->auditDatabase($audit);
        $this->writeReports($reportDir, $audit, $db);
        $this->renderSummary($audit, $db, $reportDir);

        return ($audit['blocking_errors'] || $db['blocking_errors']) ? self::FAILURE : self::SUCCESS;
    }

    private function readBible(string $file, string $sheetName): array
    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            throw new RuntimeException('Unable to open XLSX archive.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetPath = $this->resolveSheetPath($zip, $sheetName);
            $xml = $zip->getFromName($sheetPath);
            if ($xml === false) {
                throw new RuntimeException("Worksheet XML not found for [{$sheetName}].");
            }

            $reader = new XMLReader();
            $reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT);
            $rows = [];
            $headerMap = [];
            $rowNumber = 0;

            while ($reader->read()) {
                if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                    continue;
                }

                $rowNumber++;
                $rowXml = $reader->readOuterXML();
                $cells = $this->parseRow($rowXml, $sharedStrings);

                if ($rowNumber === 2) {
                    foreach ($cells as $column => $value) {
                        $headerMap[$column] = trim($value);
                    }
                    $missing = array_diff($this->headers, array_values($headerMap));
                    if ($missing) {
                        throw new RuntimeException('Bible header mismatch. Missing: '.implode(', ', $missing));
                    }
                    continue;
                }

                if ($rowNumber < 3) {
                    continue;
                }

                $record = ['_excel_row' => $rowNumber];
                foreach ($headerMap as $column => $header) {
                    $record[$header] = trim((string) ($cells[$column] ?? ''));
                }

                if ($record['Item'] === '' && $record['Trade Category'] === '' && $record['Main Category'] === '') {
                    continue;
                }

                $rows[] = $record;
            }

            $reader->close();
            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) return [];

        $reader = new XMLReader();
        $reader->XML($xml, null, LIBXML_NONET | LIBXML_COMPACT);
        $strings = [];
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'si') {
                $fragment = simplexml_load_string($reader->readOuterXML());
                $text = '';
                if ($fragment !== false) {
                    foreach ($fragment->xpath('.//*[local-name()="t"]') ?: [] as $node) {
                        $text .= (string) $node;
                    }
                }
                $strings[] = $text;
            }
        }
        $reader->close();
        return $strings;
    }

    private function resolveSheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('Invalid XLSX: workbook metadata is missing.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Invalid XLSX workbook metadata.');
        }

        $workbook->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $rels->registerXPathNamespace('p', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relationshipId = null;
        foreach ($workbook->xpath('//m:sheets/m:sheet') ?: [] as $sheet) {
            if ((string) $sheet['name'] === $sheetName) {
                $attrs = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                $relationshipId = (string) $attrs['id'];
                break;
            }
        }
        if (! $relationshipId) throw new RuntimeException("Worksheet [{$sheetName}] was not found.");

        foreach ($rels->xpath('//p:Relationship') ?: [] as $rel) {
            if ((string) $rel['Id'] === $relationshipId) {
                $target = ltrim((string) $rel['Target'], '/');
                if (str_starts_with($target, 'xl/')) return $target;
                return 'xl/'.preg_replace('#^(\.\./)+#', '', $target);
            }
        }

        throw new RuntimeException("Worksheet relationship for [{$sheetName}] was not found.");
    }

    private function parseRow(string $rowXml, array $sharedStrings): array
    {
        $row = simplexml_load_string($rowXml);
        if ($row === false) return [];
        $row->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $cells = [];

        foreach ($row->xpath('//m:c') ?: [] as $cell) {
            $ref = (string) $cell['r'];
            preg_match('/^[A-Z]+/', $ref, $m);
            $column = $m[0] ?? '';
            $type = (string) $cell['t'];
            $value = '';

            if ($type === 's') {
                $idx = isset($cell->v) ? (int) $cell->v : -1;
                $value = $sharedStrings[$idx] ?? '';
            } elseif ($type === 'inlineStr') {
                foreach ($cell->xpath('.//*[local-name()="t"]') ?: [] as $node) $value .= (string) $node;
            } else {
                $value = isset($cell->v) ? (string) $cell->v : '';
            }
            $cells[$column] = $value;
        }
        return $cells;
    }

    private function auditRows(array $rows): array
    {
        $seen = [];
        $duplicates = [];
        $errors = [];
        $warnings = [];
        $units = [];
        $aliases = [];
        $trade = [];
        $main = [];
        $sub = [];
        $items = [];
        $variants = 0;
        $fast = 0;
        $controlled = 0;

        foreach ($rows as $row) {
            foreach (['Trade Category','Main Category','Sub Category','Item','Unit','Item Nature'] as $required) {
                if ($row[$required] === '') {
                    $errors[] = [$row['_excel_row'], $row['Seq'], $required, 'Required value is blank'];
                }
            }

            $identity = $this->key(implode('|', [
                $row['Trade Category'], $row['Main Category'], $row['Sub Category'], $row['Item'],
                $row['Sub Item / Variant'], $row['Size / Grade / Finish / Specification'], $row['Unit'],
            ]));
            if (isset($seen[$identity])) {
                $duplicates[] = [$row['_excel_row'], $row['Seq'], $seen[$identity], $row['Item'], $row['Sub Item / Variant'], $row['Size / Grade / Finish / Specification'], $row['Unit']];
            } else {
                $seen[$identity] = $row['_excel_row'];
            }

            $trade[$this->key($row['Trade Category'])] = $row['Trade Category'];
            $main[$this->key($row['Main Category'])] = $row['Main Category'];
            $sub[$this->key($row['Sub Category'])] = $row['Sub Category'];
            $items[$this->key($row['Item'])] = $row['Item'];
            if ($row['Sub Item / Variant'] !== '' || $row['Size / Grade / Finish / Specification'] !== '') $variants++;
            $units[$this->key($row['Unit'])] = $row['Unit'];
            if (strcasecmp($row['Daily Use / Fast Moving'], 'Yes') === 0) $fast++;
            if (strcasecmp($row['High Value / Controlled'], 'Yes') === 0) $controlled++;

            foreach ($this->splitAliases($row['Common Site Names / Search Aliases']) as $alias) {
                $aliases[$this->key($row['Item']).'|'.$this->key($alias)] = [$row['Item'], $alias];
            }
        }

        return [
            'rows' => $rows,
            'row_count' => count($rows),
            'trade_count' => count($trade),
            'main_count' => count($main),
            'sub_count' => count($sub),
            'item_count' => count($items),
            'variant_rows' => $variants,
            'units' => array_values($units),
            'aliases' => array_values($aliases),
            'alias_count' => count($aliases),
            'fast_count' => $fast,
            'controlled_count' => $controlled,
            'duplicates' => $duplicates,
            'errors' => $errors,
            'warnings' => $warnings,
            'blocking_errors' => count($duplicates) + count($errors),
        ];
    }

    private function auditDatabase(array $audit): array
    {
        $required = ['material_types','unit_masters','material_product_groups','material_product_types','material_search_aliases','material_variants'];
        $missingTables = array_values(array_filter($required, fn ($t) => ! Schema::hasTable($t)));
        if ($missingTables) {
            return ['blocking_errors' => count($missingTables), 'missing_tables' => $missingTables];
        }

        $unitRows = DB::table('unit_masters')->where('is_active', 1)->get(['id','unit_name','unit_code']);
        $unitByCode = $unitRows->keyBy(fn ($u) => $this->key($u->unit_code));
        $unitByName = $unitRows->keyBy(fn ($u) => $this->key($u->unit_name));
        $unitAudit = [];
        foreach ($audit['units'] as $source) {
            $resolved = $this->resolveUnit($source, $unitByCode, $unitByName);
            $unitAudit[] = [$source, $resolved['status'], $resolved['default_code'], $resolved['alternative_codes'], $resolved['note']];
        }

        $refs = $this->discoverMaterialTypeReferences();
        $referencedIds = [];
        foreach ($refs as $ref) {
            if (! $ref['has_rows']) continue;
            foreach (DB::table($ref['table'])->whereNotNull($ref['column'])->distinct()->pluck($ref['column']) as $id) {
                $referencedIds[(int) $id] = true;
            }
        }

        $existing = DB::table('material_types')->get(['id','material_type_name','material_type_code','catalogue_source_code','master_status','is_legacy','is_active']);
        $existingByName = $existing->groupBy(fn ($p) => $this->key($p->material_type_name));
        $bibleItemNames = collect($audit['rows'])->pluck('Item')->unique(fn ($v) => $this->key($v));
        $exact = [];
        $ambiguous = [];
        $bibleOnly = [];
        foreach ($bibleItemNames as $name) {
            $matches = $existingByName->get($this->key($name), collect());
            if ($matches->count() === 1) {
                $p = $matches->first();
                $exact[] = [$name, $p->id, $p->material_type_name, isset($referencedIds[$p->id]) ? 'Yes' : 'No', $p->catalogue_source_code];
            } elseif ($matches->count() > 1) {
                $ambiguous[] = [$name, $matches->pluck('id')->implode('|'), $matches->pluck('material_type_name')->implode(' | ')];
            } else {
                $bibleOnly[] = [$name];
            }
        }

        $bibleKeys = $bibleItemNames->mapWithKeys(fn ($n) => [$this->key($n) => true]);
        $oldOnly = [];
        $protectedOldOnly = [];
        foreach ($existing as $p) {
            if ($bibleKeys->has($this->key($p->material_type_name))) continue;
            $row = [$p->id,$p->material_type_name,$p->material_type_code,$p->catalogue_source_code,$p->is_active,$p->is_legacy,isset($referencedIds[$p->id]) ? 'Yes' : 'No'];
            $oldOnly[] = $row;
            if (isset($referencedIds[$p->id])) $protectedOldOnly[] = $row;
        }

        return [
            'blocking_errors' => 0,
            'missing_tables' => [],
            'material_types' => $existing->count(),
            'active_material_types' => $existing->where('is_active', 1)->count(),
            'groups' => DB::table('material_product_groups')->count(),
            'types' => DB::table('material_product_types')->count(),
            'variants' => DB::table('material_variants')->count(),
            'aliases' => DB::table('material_search_aliases')->count(),
            'unit_audit' => $unitAudit,
            'unresolved_units' => count(array_filter($unitAudit, fn ($r) => $r[1] === 'UNRESOLVED')),
            'references' => $refs,
            'referenced_product_count' => count($referencedIds),
            'exact_matches' => $exact,
            'ambiguous_matches' => $ambiguous,
            'bible_only' => $bibleOnly,
            'old_only' => $oldOnly,
            'protected_old_only' => $protectedOldOnly,
        ];
    }

    private function discoverMaterialTypeReferences(): array
{
    /*
     * IMPORTANT:
     * Only true transactional/history tables protect a legacy
     * Material Type from retirement during the Bible cutover.
     *
     * Master tables, aliases, specifications, usage mappings,
     * catalogue mappings and staging tables are deliberately excluded.
     */
    $transactionTables = [
        'material_requirement_items',
        'purchase_order_items',
        'material_received_items',
        'material_consumed_items',
        'material_dispatch_items',
        'material_dispatch_receipt_items',
    ];

    $result = [];

    foreach ($transactionTables as $tableName) {
        if (! Schema::hasTable($tableName)) {
            continue;
        }

        if (! Schema::hasColumn($tableName, 'material_type_id')) {
            continue;
        }

        $rows = DB::table($tableName)
            ->whereNotNull('material_type_id')
            ->count();

        $distinctProducts = $rows > 0
            ? DB::table($tableName)
                ->whereNotNull('material_type_id')
                ->distinct()
                ->count('material_type_id')
            : 0;

        $result[] = [
            'table' => $tableName,
            'column' => 'material_type_id',
            'rows' => $rows,
            'distinct_products' => $distinctProducts,
            'has_rows' => $rows > 0,
        ];
    }

    return $result;
}

    private function resolveUnit(string $source, $byCode, $byName): array
{
    $raw = strtoupper(trim($source));

    /*
     * Bible terminology that maps to one canonical Unit Master code.
     */
    $synonyms = [
        'SFT' => 'SQFT',
        'TOOL/ASSET' => 'TOOL',
    ];

    /*
     * Bible units that intentionally permit more than one
     * operational transaction unit.
     *
     * First code = Product Master default.
     * Remaining codes = permitted alternatives/reference.
     */
    $composites = [
        'CFT/CUM' => ['CFT', 'CUM'],
        'BOX/SFT' => ['BOX', 'SQFT'],
    ];

    /*
     * Composite units.
     */
    if (isset($composites[$raw])) {
        $codes = $composites[$raw];

        $ok = collect($codes)->every(
            fn ($code) => $byCode->has($this->key($code))
        );

        return [
            'status' => $ok ? 'COMPOSITE' : 'UNRESOLVED',
            'default_code' => $ok ? $codes[0] : '',
            'alternative_codes' => $ok
                ? implode('|', array_slice($codes, 1))
                : '',
            'note' => $ok
                ? 'Default + allowed alternative; transaction unit remains editable.'
                : 'One or more component units missing.',
        ];
    }

    /*
     * Exact Unit Master code or Bible synonym.
     *
     * DAY and HOUR now resolve naturally here because those
     * Unit Master codes exist in the database.
     */
    $code = $synonyms[$raw] ?? $raw;

    if ($byCode->has($this->key($code))) {
        $isSynonym = isset($synonyms[$raw]);

        return [
            'status' => $isSynonym ? 'SYNONYM' : 'EXACT',
            'default_code' => $code,
            'alternative_codes' => '',
            'note' => $isSynonym
                ? "{$raw} maps to {$code}"
                : '',
        ];
    }

    /*
     * Last safe fallback: match an active Unit Master by name.
     */
    if ($byName->has($this->key($source))) {
        $unit = $byName->get($this->key($source));

        return [
            'status' => 'NAME_MATCH',
            'default_code' => $unit->unit_code,
            'alternative_codes' => '',
            'note' => 'Matched active Unit Master by name.',
        ];
    }

    return [
        'status' => 'UNRESOLVED',
        'default_code' => '',
        'alternative_codes' => '',
        'note' => 'Requires Unit Master/policy decision before import.',
    ];
}

    private function splitAliases(string $value): array
    {
        if (trim($value) === '') return [];
        $parts = preg_split('/\s*[;,|]\s*/u', trim($value)) ?: [];
        return array_values(array_unique(array_filter(array_map('trim', $parts))));
    }

    private function key(?string $value): string
    {
        return Str::of((string) $value)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString();
    }

    private function writeReports(string $dir, array $audit, array $db): void
    {
        $this->csv($dir.'/01_bible_errors.csv', ['Excel Row','Seq','Field','Issue'], $audit['errors']);
        $this->csv($dir.'/02_bible_duplicates.csv', ['Excel Row','Seq','First Excel Row','Item','Variant','Specification','Unit'], $audit['duplicates']);
        $this->csv($dir.'/03_unit_mapping_audit.csv', ['Bible Unit','Status','Default Unit Code','Alternative Unit Codes','Note'], $db['unit_audit'] ?? []);
        $this->csv($dir.'/04_exact_old_product_matches.csv', ['Bible Item','Material Type ID','Existing Name','Transaction Referenced','Catalogue Source Code'], $db['exact_matches'] ?? []);
        $this->csv($dir.'/05_ambiguous_old_product_matches.csv', ['Bible Item','Existing IDs','Existing Names'], $db['ambiguous_matches'] ?? []);
        $this->csv($dir.'/06_bible_only_items.csv', ['Bible Item'], $db['bible_only'] ?? []);
        $this->csv($dir.'/07_old_catalogue_only_products.csv', ['ID','Name','Code','Catalogue Source Code','Active','Legacy','Transaction Referenced'], $db['old_only'] ?? []);
        $this->csv($dir.'/08_protected_old_catalogue_products.csv', ['ID','Name','Code','Catalogue Source Code','Active','Legacy','Transaction Referenced'], $db['protected_old_only'] ?? []);
        $this->csv($dir.'/09_material_type_fk_references.csv', ['Table','Column','Rows','Distinct Products'], array_map(fn ($r) => [$r['table'],$r['column'],$r['rows'],$r['distinct_products']], $db['references'] ?? []));
        $this->csv($dir.'/10_bible_aliases.csv', ['Bible Item','Alias'], $audit['aliases']);

        $summary = [
            'generated_at' => now()->toIso8601String(),
            'mode' => 'READ ONLY',
            'bible' => [
                'rows' => $audit['row_count'], 'trade_categories' => $audit['trade_count'], 'main_categories' => $audit['main_count'],
                'sub_categories' => $audit['sub_count'], 'distinct_items' => $audit['item_count'], 'variant_or_spec_rows' => $audit['variant_rows'],
                'aliases' => $audit['alias_count'], 'fast_moving_rows' => $audit['fast_count'], 'controlled_rows' => $audit['controlled_count'],
                'duplicate_rows' => count($audit['duplicates']), 'errors' => count($audit['errors']),
            ],
            'database' => [
                'material_types' => $db['material_types'] ?? null, 'active_material_types' => $db['active_material_types'] ?? null,
                'groups' => $db['groups'] ?? null, 'types' => $db['types'] ?? null, 'variants' => $db['variants'] ?? null, 'aliases' => $db['aliases'] ?? null,
                'referenced_products' => $db['referenced_product_count'] ?? null, 'exact_matches' => count($db['exact_matches'] ?? []),
                'ambiguous_matches' => count($db['ambiguous_matches'] ?? []), 'bible_only_items' => count($db['bible_only'] ?? []),
                'old_only_products' => count($db['old_only'] ?? []), 'protected_old_only_products' => count($db['protected_old_only'] ?? []),
                'unresolved_units' => $db['unresolved_units'] ?? null,
            ],
        ];
        file_put_contents($dir.'/00_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function csv(string $path, array $header, array $rows): void
    {
        $fh = fopen($path, 'wb');
        fputcsv($fh, $header);
        foreach ($rows as $row) fputcsv($fh, $row);
        fclose($fh);
    }

    private function renderSummary(array $a, array $db, string $reportDir): void
    {
        $this->table(['Bible Check','Count'], [
            ['Rows', number_format($a['row_count'])], ['Trade Categories', number_format($a['trade_count'])],
            ['Main Categories', number_format($a['main_count'])], ['Sub Categories', number_format($a['sub_count'])],
            ['Distinct Items', number_format($a['item_count'])], ['Variant / Specification Rows', number_format($a['variant_rows'])],
            ['Search Aliases', number_format($a['alias_count'])], ['Exact Duplicate Rows', number_format(count($a['duplicates']))],
            ['Required-field Errors', number_format(count($a['errors']))],
        ]);
        $this->newLine();
        $this->table(['Database Reconciliation','Count'], [
            ['Current Product Master', number_format($db['material_types'] ?? 0)],
            ['Transaction-referenced Products', number_format($db['referenced_product_count'] ?? 0)],
            ['Exact Bible Item ↔ Old Product Matches', number_format(count($db['exact_matches'] ?? []))],
            ['Ambiguous Name Matches', number_format(count($db['ambiguous_matches'] ?? []))],
            ['Bible-only Distinct Items', number_format(count($db['bible_only'] ?? []))],
            ['Old-catalogue-only Products', number_format(count($db['old_only'] ?? []))],
            ['Protected Old-only Products', number_format(count($db['protected_old_only'] ?? []))],
            ['Unresolved Bible Units', number_format($db['unresolved_units'] ?? 0)],
        ]);
        $this->newLine();
        $this->info('Audit reports written to: '.$reportDir);
        $this->warn('No database rows were modified. Do NOT run a cutover yet; review 00_summary.json and the CSV reports first.');
    }
}
