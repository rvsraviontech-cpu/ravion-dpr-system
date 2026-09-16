<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use XMLReader;
use ZipArchive;

class MaterialBibleImport extends Command
{
    protected $signature = 'materials:bible-import
        {file : Path to Ravion Material Bible XLSX}
        {--apply : Actually write the import to the database}';

    protected $description = 'Dry-run or additively import the validated Ravion Material Bible into Product Master.';

    private const SHEET_NAME = 'Material_Master_Final';
    private const SOURCE_CODE = 'RAVION_BIBLE_20260912';
    private const SOURCE_LABEL = 'Ravion Material Bible Final 12-09-2026';

    private array $headers = [];

    private Collection $unitsByCode;

    private array $stats = [
    'source_rows' => 0,
    'distinct_items' => 0,
    'products_to_create' => 0,
    'products_to_adopt' => 0,
    'products_already_imported' => 0,
    'variants_to_create' => 0,
    'variants_already_imported' => 0,
    'aliases_to_create' => 0,
    'aliases_already_imported' => 0,
    'unit_errors' => 0,
    'row_errors' => 0,
];

    public function handle(): int
    {
        $input = (string) $this->argument('file');
        $path = $this->resolvePath($input);
        $apply = (bool) $this->option('apply');

        $this->newLine();
        $this->info('Ravion Material Bible Phase 2 Import');
        $this->line(
            $apply
                ? '<fg=yellow>APPLY MODE — database records may be INSERTED/UPDATED.</>'
                : '<fg=green>DRY-RUN MODE — no database rows will be modified.</>'
        );
        $this->line('File: ' . $path);
        $this->line('Source: ' . self::SOURCE_CODE);
        $this->newLine();

        try {
            $this->validateEnvironment($path);

            $this->unitsByCode = DB::table('unit_masters')
                ->where('is_active', 1)
                ->get()
                ->keyBy(fn ($unit) => $this->key((string) $unit->unit_code));

            $rows = $this->readBible($path);

            if ($rows->isEmpty()) {
                throw new RuntimeException('No Bible data rows were found.');
            }

            $this->stats['source_rows'] = $rows->count();

            $items = $this->buildItems($rows);

            $this->stats['distinct_items'] = $items->count();

            $this->analyse($items);

            $this->showSummary($apply);

            if (!$apply) {
                $this->newLine();
                $this->info('Dry-run completed successfully. No database rows were modified.');
                $this->line(
                    'Review the counts above. Run again with --apply only after the dry-run is accepted.'
                );

                return self::SUCCESS;
            }

            if ($this->stats['unit_errors'] > 0 || $this->stats['row_errors'] > 0) {
                $this->error(
                    'Import blocked because validation errors remain. No database rows were modified.'
                );

                return self::FAILURE;
            }

            if (!$this->confirm(
                'Apply the Ravion Material Bible additive import now? Existing catalogue products will NOT be deactivated.',
                false
            )) {
                $this->warn('Import cancelled. No database rows were modified.');

                return self::SUCCESS;
            }

            DB::transaction(function () use ($items) {
                $this->applyImport($items);
            }, 3);

            $this->newLine();
            $this->info('Material Bible additive import completed successfully.');
            $this->line('No legacy Product Master records were deactivated or deleted.');
            $this->line('Do NOT perform catalogue cutover yet.');
            $this->newLine();

            $this->showDatabaseCounts();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function validateEnvironment(string $path): void
    {
        if (!File::exists($path)) {
            throw new RuntimeException("Bible file not found: {$path}");
        }

        foreach ([
            'material_types',
            'material_variants',
            'material_search_aliases',
            'unit_masters',
        ] as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException("Required database table is missing: {$table}");
            }
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP ZipArchive extension is required.');
        }

        if (!class_exists(XMLReader::class)) {
            throw new RuntimeException('PHP XMLReader extension is required.');
        }
    }

    private function resolvePath(string $input): string
    {
        if (File::exists($input)) {
            return realpath($input) ?: $input;
        }

        $candidate = base_path($input);

        if (File::exists($candidate)) {
            return realpath($candidate) ?: $candidate;
        }

        return $candidate;
    }

    private function readBible(string $path): Collection
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open the XLSX workbook.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetPath = $this->resolveSheetPath($zip, self::SHEET_NAME);

            if (!$sheetPath) {
                throw new RuntimeException(
                    'Worksheet "' . self::SHEET_NAME . '" was not found.'
                );
            }

            $stream = $zip->getStream($sheetPath);

            if (!$stream) {
                throw new RuntimeException('Unable to read Bible worksheet XML.');
            }

            $reader = new XMLReader();

            if (!$reader->open(
                'zip://' . $path . '#' . $sheetPath,
                null,
                LIBXML_NONET | LIBXML_COMPACT
            )) {
                throw new RuntimeException('Unable to open worksheet XML stream.');
            }

            $rows = collect();
$headerMap = [];
$headerFound = false;

while ($reader->read()) {
    if (
        $reader->nodeType !== XMLReader::ELEMENT ||
        $reader->localName !== 'row'
    ) {
        continue;
    }

    $rowXml = $reader->readOuterXML();

    if (!$rowXml) {
        continue;
    }

    $values = $this->parseRow($rowXml, $sharedStrings);

    /*
     * The Material Bible contains title/information rows above
     * the actual tabular header.
     *
     * Do not assume the first non-empty worksheet row is the header.
     * Search until we find the row containing the core Bible columns.
     */
    if (!$headerFound) {
        $detected = collect($values)
            ->map(fn ($value) => $this->key((string) $value))
            ->filter()
            ->values()
            ->all();

        $requiredHeaderMarkers = [
            'seq',
            'trade category',
            'main category',
            'sub category',
            'item',
            'unit',
        ];

        $isHeaderRow = collect($requiredHeaderMarkers)
            ->every(fn ($header) => in_array(
                $this->key($header),
                $detected,
                true
            ));

        if (!$isHeaderRow) {
            continue;
        }

        $headerMap = $this->buildHeaderMap($values);
        $headerFound = true;

        $this->line(
            'Bible header row detected successfully.'
        );

        continue;
    }

    $row = $this->mapBibleRow($values, $headerMap);

    if ($this->isBlankBibleRow($row)) {
        continue;
    }

    $rows->push($row);
}

if (!$headerFound) {
    throw new RuntimeException(
        'Could not locate the Material Bible column header row in worksheet "' .
        self::SHEET_NAME .
        '".'
    );
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

        if ($xml === false) {
            return [];
        }

        $document = simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $strings = [];

        foreach ($document->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $text = '';

            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }

            $strings[] = $text;
        }

        return $strings;
    }

    private function resolveSheetPath(ZipArchive $zip, string $sheetName): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            return null;
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);

        if ($workbook === false || $rels === false) {
            return null;
        }

        $workbook->registerXPathNamespace(
            'main',
            'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
        );
        $workbook->registerXPathNamespace(
            'r',
            'http://schemas.openxmlformats.org/officeDocument/2006/relationships'
        );

        $relationships = [];

        foreach ($rels->Relationship as $relationship) {
            $relationships[(string) $relationship['Id']] = (string) $relationship['Target'];
        }

        $sheets = $workbook->xpath('//main:sheets/main:sheet') ?: [];

        foreach ($sheets as $sheet) {
            if ((string) $sheet['name'] !== $sheetName) {
                continue;
            }

            $attributes = $sheet->attributes(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships'
            );

            $relationshipId = (string) $attributes['id'];
            $target = $relationships[$relationshipId] ?? null;

            if (!$target) {
                return null;
            }

            $target = ltrim($target, '/');

            if (str_starts_with($target, 'xl/')) {
                return $target;
            }

            return 'xl/' . $target;
        }

        return null;
    }

    private function parseRow(string $rowXml, array $sharedStrings): array
    {
        $row = simplexml_load_string($rowXml);

        if ($row === false) {
            return [];
        }

        $values = [];

        foreach ($row->c as $cell) {
            $reference = (string) $cell['r'];
            $column = preg_replace('/\d+/', '', $reference);
            $index = $this->columnIndex($column);

            $type = (string) $cell['t'];
            $value = '';

            if ($type === 's') {
                $sharedIndex = isset($cell->v) ? (int) $cell->v : -1;
                $value = $sharedStrings[$sharedIndex] ?? '';
            } elseif ($type === 'inlineStr') {
                if (isset($cell->is->t)) {
                    $value = (string) $cell->is->t;
                } elseif (isset($cell->is->r)) {
                    foreach ($cell->is->r as $run) {
                        $value .= (string) $run->t;
                    }
                }
            } else {
                $value = isset($cell->v) ? (string) $cell->v : '';
            }

            $values[$index] = trim($value);
        }

        return $values;
    }

    private function columnIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $number = 0;

        for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
            $number = ($number * 26) + (ord($letters[$i]) - 64);
        }

        return $number - 1;
    }

    private function buildHeaderMap(array $values): array
{
    $map = [];

    foreach ($values as $index => $header) {
        $normalized = $this->key((string) $header);

        if ($normalized !== '') {
            $map[$normalized] = $index;
        }
    }

    /*
     * Expected normalized headers from:
     * Ravion Material Bible Final 12-09-2026.xlsx
     *
     * Original workbook headers:
     * Seq
     * Trade Category
     * Main Category
     * Sub Category
     * Item
     * Sub Item / Variant
     * Size / Grade / Finish / Specification
     * Unit
     * Item Nature
     * Used In / Application
     * Common Site Names / Search Aliases
     * Daily Use / Fast Moving
     * High Value / Controlled
     */
    $required = [
        'seq',
        'trade category',
        'main category',
        'sub category',
        'item',
        'sub item variant',
        'size grade finish specification',
        'unit',
        'item nature',
        'used in application',
        'common site names search aliases',
        'daily use fast moving',
        'high value controlled',
    ];

    $missing = [];

    foreach ($required as $header) {
        $normalized = $this->key($header);

        if (!array_key_exists($normalized, $map)) {
            $missing[] = $header;
        }
    }

    if (!empty($missing)) {
        throw new RuntimeException(
            'Required Bible column(s) could not be found: '
            . implode(', ', $missing)
            . '. Detected headers: '
            . implode(' | ', array_keys($map))
        );
    }

    return $map;
}

    private function mapBibleRow(array $values, array $map): array
    {
        $get = function (string $header) use ($values, $map): string {
    $normalized = $this->key($header);
    $index = $map[$normalized] ?? null;

    return $index === null
        ? ''
        : trim((string) ($values[$index] ?? ''));
};

        return [
            'seq' => $get('seq'),
            'trade_category' => $get('trade category'),
            'main_category' => $get('main category'),
            'sub_category' => $get('sub category'),
            'item' => $get('item'),
            'sub_item' => $get('sub item variant'),
            'specification' => $get('size grade finish specification'),
            'unit' => $get('unit'),
            'item_nature' => $get('item nature'),
            'application' => $get('used in application'),
            'aliases' => $get('common site names search aliases'),
            'fast_moving' => $get('daily use fast moving'),
            'controlled' => $get('high value controlled'),
        ];
    }

    private function isBlankBibleRow(array $row): bool
    {
        return trim($row['item']) === ''
            && trim($row['main_category']) === ''
            && trim($row['sub_category']) === '';
    }

    private function buildItems(Collection $rows): Collection
    {
        $grouped = [];

        foreach ($rows as $row) {
            if (trim($row['item']) === '') {
                $this->stats['row_errors']++;
                continue;
            }

            $itemKey = $this->key($row['item']);

            if ($itemKey === '') {
                $this->stats['row_errors']++;
                continue;
            }

            if (!isset($grouped[$itemKey])) {
                $grouped[$itemKey] = [
                    'key' => $itemKey,
                    'name' => trim($row['item']),
                    'rows' => [],
                    'default_unit_code' => null,
                    'source_key' => sha1('RMB|' . $itemKey),
                ];
            }

            $unit = $this->resolveUnit($row['unit']);

            if ($unit['status'] === 'UNRESOLVED') {
                $this->stats['unit_errors']++;
            }

            $row['_unit'] = $unit;
            $grouped[$itemKey]['rows'][] = $row;
        }

        foreach ($grouped as &$item) {
            $unitCodes = collect($item['rows'])
                ->pluck('_unit.default_code')
                ->filter()
                ->unique()
                ->values();

            /*
             * Product Master unit is a DEFAULT only.
             * If an Item appears with multiple valid units, choose the unit
             * used by the earliest Bible sequence deterministically.
             */
            $orderedRows = collect($item['rows'])
                ->sortBy(fn ($row) => is_numeric($row['seq']) ? (float) $row['seq'] : PHP_INT_MAX);

            $item['default_unit_code'] = $orderedRows
                ->pluck('_unit.default_code')
                ->filter()
                ->first();

            $item['unit_codes'] = $unitCodes->all();
        }

        unset($item);

        return collect(array_values($grouped));
    }

    private function analyse(Collection $items): void
{
    /*
     * Existing Bible products from a previous successful/imported run.
     */
    $existingBibleProducts = DB::table('material_types')
        ->where('catalogue_source_code', self::SOURCE_CODE)
        ->get()
        ->keyBy(fn ($row) => $this->key((string) $row->material_type_name));

    /*
     * All Product Master records.
     *
     * material_type_name has a global UNIQUE constraint, therefore an
     * exact existing product name must be ADOPTED rather than duplicated.
     */
    $allProducts = DB::table('material_types')
        ->get()
        ->keyBy(fn ($row) => $this->key((string) $row->material_type_name));

    $this->stats['products_to_adopt'] = 0;

    foreach ($items as $item) {
        $existingBible = $existingBibleProducts->get($item['key']);
        $existingAny = $allProducts->get($item['key']);

        if ($existingBible) {
            $this->stats['products_already_imported']++;

            $product = $existingBible;
        } elseif ($existingAny) {
            $this->stats['products_to_adopt']++;

            $product = $existingAny;
        } else {
            $this->stats['products_to_create']++;

            $product = null;
        }

        $existingVariants = collect();

        if ($product) {
            $existingVariants = DB::table('material_variants')
                ->where('material_type_id', $product->id)
                ->pluck('variant_code')
                ->mapWithKeys(
                    fn ($code) => [(string) $code => true]
                );
        }

        foreach ($this->buildVariantsForItem($item) as $variant) {
            if ($existingVariants->has($variant['variant_code'])) {
                $this->stats['variants_already_imported']++;
            } else {
                $this->stats['variants_to_create']++;
            }
        }

        $existingAliases = collect();

        if ($product) {
            $existingAliases = DB::table('material_search_aliases')
                ->where('material_type_id', $product->id)
                ->pluck('normalized_alias')
                ->mapWithKeys(
                    fn ($alias) => [(string) $alias => true]
                );
        }

        foreach ($this->buildAliasesForItem($item) as $alias) {
            if ($existingAliases->has($alias['normalized_alias'])) {
                $this->stats['aliases_already_imported']++;
            } else {
                $this->stats['aliases_to_create']++;
            }
        }
    }
}

    private function applyImport(Collection $items): void
{
    $now = now();
    $sort = 0;

    foreach ($items as $item) {
        $sort += 10;

        $unitId = $this->unitId($item['default_unit_code']);

        /*
         * IMPORTANT:
         *
         * material_type_name is globally UNIQUE.
         *
         * Therefore:
         *
         * 1. Existing Bible product  -> UPDATE / reconcile
         * 2. Existing legacy product -> ADOPT same ID into Bible
         * 3. No existing product     -> CREATE new Bible product
         *
         * We never create a second Product Master row with the same name.
         */
        $existing = DB::table('material_types')
            ->whereRaw('LOWER(TRIM(material_type_name)) = ?', [
                mb_strtolower(trim($item['name'])),
            ])
            ->first();

        $remarks = $this->productRemarks($item);

        if ($existing) {
            /*
             * ADOPT or reconcile the existing Product Master ID.
             *
             * This intentionally preserves:
             * - material_types.id
             * - all historical transaction foreign keys
             *
             * We do not replace the ID.
             */
            DB::table('material_types')
                ->where('id', $existing->id)
                ->update([
                    'material_group' => $this->primaryMainCategory($item),
                    'catalogue_source_code' => self::SOURCE_CODE,
                    'inventory_type' => $this->inventoryType($item),
                    'master_status' => 'CANONICAL',
                    'is_legacy' => 0,
                    'unit_master_id' => $unitId,
                    'sequence' => $sort,
                    'is_active' => 1,
                    'remarks' => $remarks,
                    'updated_at' => $now,
                ]);

            $materialTypeId = (int) $existing->id;
        } else {
            $materialTypeId = (int) DB::table('material_types')
                ->insertGetId([
                    'material_product_group_id' => null,
                    'material_product_type_id' => null,
                    'material_group' => $this->primaryMainCategory($item),
                    'material_type_name' => $item['name'],
                    'material_type_code' => $this->productCode($item),
                    'catalogue_source_code' => self::SOURCE_CODE,
                    'inventory_type' => $this->inventoryType($item),
                    'master_status' => 'CANONICAL',
                    'is_legacy' => 0,
                    'unit_master_id' => $unitId,
                    'sequence' => $sort,
                    'is_active' => 1,
                    'remarks' => $remarks,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        $this->upsertVariants(
            $materialTypeId,
            $item,
            $now
        );

        $this->upsertAliases(
            $materialTypeId,
            $item,
            $now
        );
    }
}

    private function buildVariantsForItem(array $item): array
    {
        $variants = [];

        foreach ($item['rows'] as $row) {
            $subItem = trim($row['sub_item']);
            $specification = trim($row['specification']);

            /*
             * A Bible row without Sub Item and without Specification is the
             * base product itself and does not need a meaningless variant.
             */
            if ($subItem === '' && $specification === '') {
                continue;
            }

            $identity = implode('|', [
                $item['key'],
                $this->key($subItem),
                $this->key($specification),
                $this->key($row['_unit']['default_code'] ?? ''),
            ]);

            $variantCode = 'RMBV-' . strtoupper(substr(sha1($identity), 0, 16));

            if (isset($variants[$variantCode])) {
                continue;
            }

            $variantNameParts = array_values(array_filter([
                $subItem,
                $specification,
            ], fn ($value) => trim($value) !== ''));

            $variants[$variantCode] = [
                'variant_code' => $variantCode,
                'variant_name' => implode(' - ', $variantNameParts),
                'size_dimension' => $specification !== '' ? $specification : null,
                'unit_code' => $row['_unit']['default_code'] ?? null,
                'search_aliases' => trim($row['aliases']) ?: null,
                'remarks' => $this->variantRemarks($row),
            ];
        }

        return array_values($variants);
    }

    private function buildAliasesForItem(array $item): array
    {
        $aliases = [];

        /*
         * Include the canonical Item itself so normalization/search behaviour
         * remains deterministic.
         */
        $this->pushAlias($aliases, $item['name']);

        foreach ($item['rows'] as $row) {
            foreach ($this->splitAliases($row['aliases']) as $alias) {
                $this->pushAlias($aliases, $alias);
            }

            if (trim($row['sub_item']) !== '') {
                $this->pushAlias($aliases, $row['sub_item']);
            }

            /*
             * Full product + variant wording is useful for site search,
             * without polluting Product Master with thousands of products.
             */
            $full = trim(implode(' ', array_filter([
                $item['name'],
                trim($row['sub_item']),
                trim($row['specification']),
            ])));

            if ($full !== $item['name']) {
                $this->pushAlias($aliases, $full);
            }
        }

        return array_values($aliases);
    }

    private function pushAlias(array &$aliases, string $alias): void
    {
        $alias = trim($alias);

        if ($alias === '') {
            return;
        }

        $normalized = $this->key($alias);

        if ($normalized === '') {
            return;
        }

        $aliases[$normalized] = [
            'alias' => $alias,
            'normalized_alias' => $normalized,
        ];
    }

    private function upsertVariants(int $materialTypeId, array $item, $now): void
    {
        $sort = 0;

        foreach ($this->buildVariantsForItem($item) as $variant) {
            $sort += 10;

            $existing = DB::table('material_variants')
                ->where('material_type_id', $materialTypeId)
                ->where('variant_code', $variant['variant_code'])
                ->first();

            $payload = [
                'variant_name' => $variant['variant_name'],
                'material_specification_id' => null,
                'material_grade_id' => null,
                'unit_master_id' => $this->unitId($variant['unit_code']),
                'size_dimension' => $variant['size_dimension'],
                'finish' => null,
                'colour_shade' => null,
                'search_aliases' => $variant['search_aliases'],
                'sort_order' => $sort,
                'is_active' => 1,
                'remarks' => $variant['remarks'],
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('material_variants')
                    ->where('id', $existing->id)
                    ->update($payload);
            } else {
                DB::table('material_variants')->insert(array_merge($payload, [
                    'material_type_id' => $materialTypeId,
                    'variant_code' => $variant['variant_code'],
                    'created_at' => $now,
                ]));
            }
        }
    }

    private function upsertAliases(int $materialTypeId, array $item, $now): void
    {
        $sort = 0;

        foreach ($this->buildAliasesForItem($item) as $alias) {
            $sort += 10;

            $existing = DB::table('material_search_aliases')
                ->where('material_type_id', $materialTypeId)
                ->whereNull('material_variant_id')
                ->where('normalized_alias', $alias['normalized_alias'])
                ->first();

            $payload = [
                'alias' => $alias['alias'],
                'sort_order' => $sort,
                'is_active' => 1,
                'source' => self::SOURCE_CODE,
                'remarks' => self::SOURCE_LABEL,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('material_search_aliases')
                    ->where('id', $existing->id)
                    ->update($payload);
            } else {
                DB::table('material_search_aliases')->insert(array_merge($payload, [
                    'material_type_id' => $materialTypeId,
                    'material_variant_id' => null,
                    'normalized_alias' => $alias['normalized_alias'],
                    'created_at' => $now,
                ]));
            }
        }
    }

    private function resolveUnit(string $source): array
    {
        $raw = strtoupper(trim($source));

        $synonyms = [
            'SFT' => 'SQFT',
            'TOOL/ASSET' => 'TOOL',
        ];

        $composites = [
            'CFT/CUM' => ['CFT', 'CUM'],
            'BOX/SFT' => ['BOX', 'SQFT'],
        ];

        if (isset($composites[$raw])) {
            $codes = $composites[$raw];

            $ok = collect($codes)->every(
                fn ($code) => $this->unitsByCode->has($this->key($code))
            );

            return [
                'status' => $ok ? 'COMPOSITE' : 'UNRESOLVED',
                'default_code' => $ok ? $codes[0] : null,
                'alternative_codes' => $ok ? array_slice($codes, 1) : [],
            ];
        }

        $code = $synonyms[$raw] ?? $raw;

        if ($this->unitsByCode->has($this->key($code))) {
            return [
                'status' => isset($synonyms[$raw]) ? 'SYNONYM' : 'EXACT',
                'default_code' => $code,
                'alternative_codes' => [],
            ];
        }

        return [
            'status' => 'UNRESOLVED',
            'default_code' => null,
            'alternative_codes' => [],
        ];
    }

    private function unitId(?string $code): ?int
    {
        if (!$code) {
            return null;
        }

        $unit = $this->unitsByCode->get($this->key($code));

        return $unit ? (int) $unit->id : null;
    }

    private function splitAliases(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        $parts = preg_split('/\s*[;,|]\s*/u', trim($value)) ?: [];

        return collect($parts)
            ->map(fn ($alias) => trim($alias))
            ->filter()
            ->unique(fn ($alias) => $this->key($alias))
            ->values()
            ->all();
    }

    private function primaryMainCategory(array $item): ?string
    {
        return collect($item['rows'])
            ->pluck('main_category')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->first();
    }

    private function inventoryType(array $item): string
    {
        $natures = collect($item['rows'])
            ->pluck('item_nature')
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique();

        if ($natures->contains(fn ($value) => str_contains($value, 'TOOL'))) {
            return 'Tool';
        }

        if ($natures->contains(fn ($value) => str_contains($value, 'ASSET'))) {
            return 'Asset';
        }

        return 'Material';
    }

    private function productCode(array $item): string
    {
        return 'RMB-' . strtoupper(substr($item['source_key'], 0, 12));
    }

    private function productRemarks(array $item): string
    {
        $trade = collect($item['rows'])
            ->pluck('trade_category')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->implode(' | ');

        $main = collect($item['rows'])
            ->pluck('main_category')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->implode(' | ');

        $sub = collect($item['rows'])
            ->pluck('sub_category')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->implode(' | ');

        $units = implode(' | ', $item['unit_codes']);

        return $this->limitText(
            self::SOURCE_LABEL .
            "; Source Key: {$item['source_key']}" .
            "; Trade: {$trade}" .
            "; Main: {$main}" .
            "; Sub: {$sub}" .
            "; Bible Units: {$units}"
        );
    }

    private function variantRemarks(array $row): string
    {
        $parts = [
            self::SOURCE_LABEL,
            'Trade: ' . trim($row['trade_category']),
            'Main: ' . trim($row['main_category']),
            'Sub: ' . trim($row['sub_category']),
            'Nature: ' . trim($row['item_nature']),
            'Application: ' . trim($row['application']),
            'Fast Moving: ' . trim($row['fast_moving']),
            'Controlled: ' . trim($row['controlled']),
        ];

        if (!empty($row['_unit']['alternative_codes'])) {
            $parts[] = 'Alternative Units: ' .
                implode('|', $row['_unit']['alternative_codes']);
        }

        return $this->limitText(
            implode('; ', array_filter(
                $parts,
                fn ($value) => !str_ends_with($value, ': ')
            ))
        );
    }

    private function limitText(string $value, int $length = 4000): string
    {
        return mb_substr($value, 0, $length);
    }

    private function key(string $value): string
    {
        $value = mb_strtolower(trim($value));

        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function showSummary(bool $apply): void
    {
        $this->table(
            ['Phase 2 Check', 'Count'],
            [
                ['Source Rows', number_format($this->stats['source_rows'])],
                ['Distinct Items', number_format($this->stats['distinct_items'])],
                ['Products To Create', number_format($this->stats['products_to_create'])],
['Existing Products To Adopt', number_format($this->stats['products_to_adopt'])],
['Products Already Imported', number_format($this->stats['products_already_imported'])],
                ['Variants To Create', number_format($this->stats['variants_to_create'])],
                ['Variants Already Imported', number_format($this->stats['variants_already_imported'])],
                ['Search Aliases To Create', number_format($this->stats['aliases_to_create'])],
                ['Aliases Already Imported', number_format($this->stats['aliases_already_imported'])],
                ['Unit Errors', number_format($this->stats['unit_errors'])],
                ['Row Errors', number_format($this->stats['row_errors'])],
                ['Mode', $apply ? 'APPLY' : 'DRY RUN'],
            ]
        );
    }

    private function showDatabaseCounts(): void
    {
        $this->table(
            ['Database Check', 'Count'],
            [
                [
                    'Bible Product Master',
                    number_format(
                        DB::table('material_types')
                            ->where('catalogue_source_code', self::SOURCE_CODE)
                            ->count()
                    ),
                ],
                [
                    'Material Variants',
                    number_format(DB::table('material_variants')->count()),
                ],
                [
                    'Bible Search Aliases',
                    number_format(
                        DB::table('material_search_aliases')
                            ->where('source', self::SOURCE_CODE)
                            ->count()
                    ),
                ],
                [
                    'Total Product Master',
                    number_format(DB::table('material_types')->count()),
                ],
            ]
        );
    }
}