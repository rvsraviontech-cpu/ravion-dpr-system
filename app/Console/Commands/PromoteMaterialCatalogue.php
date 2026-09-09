<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PromoteMaterialCatalogue extends Command
{
    protected $signature = 'materials:catalog-promote
        {batch-code : Validated material catalogue import batch code}
        {--apply : Apply the promotion to the canonical Product Master}';

    protected $description = 'Dry-run or promote validated Ravion Materials catalogue products into the canonical Product Master.';

    public function handle(): int
    {
        $batchCode = trim((string) $this->argument('batch-code'));
        $apply = (bool) $this->option('apply');

        $batch = DB::table('material_catalog_import_batches')
            ->where('batch_code', $batchCode)
            ->first();

        if (! $batch) {
            $this->error("Catalogue batch [{$batchCode}] was not found.");
            return self::FAILURE;
        }

        if (($batch->status ?? null) !== 'Validated') {
            $this->error(
                "Catalogue batch [{$batchCode}] must be Validated before promotion. "
                ."Current status: ".($batch->status ?? 'Unknown')
            );
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Ravion Materials Catalogue Promotion');
        $this->line("Batch: {$batchCode}");
        $this->line('Mode: '.($apply ? 'APPLY' : 'DRY RUN'));
        $this->newLine();

        $preflight = $this->buildPreflight((int) $batch->id);
        $this->renderPreflight($preflight);

        if ($preflight['blocking_conflicts'] > 0) {
            $this->newLine();
            $this->error(
                'Promotion stopped because blocking conflicts were found. '
                .'No canonical rows were modified.'
            );
            return self::FAILURE;
        }

        if (! $apply) {
            $this->newLine();
            $this->warn('Dry-run only. No canonical rows were modified.');
            $this->line(
                "When the report is acceptable, run:\n"
                ."php artisan materials:catalog-promote {$batchCode} --apply"
            );
            return self::SUCCESS;
        }

        try {
            $result = DB::transaction(function () use ($batch): array {
                return $this->applyPromotion((int) $batch->id);
            }, 3);
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('Promotion failed and the transaction was rolled back.');
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Promotion completed successfully.');

        $this->table(
            ['Entity', 'Created', 'Existing', 'Skipped / Conflict'],
            [
                ['Product Groups', number_format($result['groups_created']), number_format($result['groups_existing']), '0'],
                ['Product Types', number_format($result['types_created']), number_format($result['types_existing']), '0'],
                ['Ready Products - New', number_format($result['products_created']), number_format($result['products_existing']), number_format($result['products_skipped'])],
                ['Legacy Products Adopted', number_format($result['legacy_adopted']), '-', '0'],
            ]
        );

        $this->newLine();
        $this->line('Canonical Product Master count: '.number_format(DB::table('material_types')->count()));
        $this->line('Legacy products preserved: '.number_format(DB::table('material_types')->where('is_legacy', true)->count()));
        $this->line('Catalogue products now present: '.number_format(DB::table('material_types')->whereNotNull('catalogue_source_code')->count()));
        $this->line('Legacy adoptions - same unit: '.number_format($result['legacy_same_unit']));
        $this->line('Legacy adoptions - filled missing unit: '.number_format($result['legacy_missing_unit']));
        $this->line('Legacy adoptions - preserved different legacy unit: '.number_format($result['legacy_different_unit']));

        $this->newLine();
        $this->comment(
            'Specifications, search aliases, usage mappings, brands, Work Packages, '
            .'and historical transaction rows were not modified by this command.'
        );

        return self::SUCCESS;
    }

    private function buildPreflight(int $batchId): array
    {
        $products = DB::table('material_catalog_product_staging')
            ->where('import_batch_id', $batchId)
            ->where('staging_status', 'Ready')
            ->orderBy('id')
            ->get([
                'id',
                'source_product_code',
                'product_name',
                'product_group',
                'product_type',
                'inventory_type',
                'default_unit',
                'source_master_status',
                'staging_status',
            ]);

        $reviewCount = DB::table('material_catalog_product_staging')
            ->where('import_batch_id', $batchId)
            ->where('staging_status', 'Review')
            ->count();

        $excludedCount = DB::table('material_catalog_product_staging')
            ->where('import_batch_id', $batchId)
            ->where('staging_status', 'Excluded')
            ->count();

        $errorCount = DB::table('material_catalog_product_staging')
            ->where('import_batch_id', $batchId)
            ->where('staging_status', 'Error')
            ->count();

        $unitMap = DB::table('material_catalog_unit_mapping_staging')
            ->where('import_batch_id', $batchId)
            ->whereNotNull('unit_master_id')
            ->get(['source_unit_name', 'unit_master_id', 'staging_status'])
            ->keyBy(fn ($row) => $this->normalizeKey($row->source_unit_name));

        $groupNames = $products
            ->pluck('product_group')
            ->filter()
            ->unique(fn ($value) => $this->normalizeKey($value))
            ->values();

        $typePairs = $products
            ->map(fn ($row) => [
                'group' => trim((string) $row->product_group),
                'type' => trim((string) $row->product_type),
            ])
            ->unique(fn ($row) => $this->normalizeKey($row['group']).'|'.$this->normalizeKey($row['type']))
            ->values();

        $existingGroups = DB::table('material_product_groups')
            ->get(['id', 'group_code', 'group_name'])
            ->keyBy(fn ($row) => $this->normalizeKey($row->group_name));

        $existingTypes = DB::table('material_product_types')
            ->join(
                'material_product_groups',
                'material_product_groups.id',
                '=',
                'material_product_types.material_product_group_id'
            )
            ->get([
                'material_product_types.id',
                'material_product_types.type_code',
                'material_product_types.type_name',
                'material_product_groups.group_name',
            ])
            ->keyBy(
                fn ($row) => $this->normalizeKey($row->group_name)
                    .'|'.$this->normalizeKey($row->type_name)
            );

        $existingBySourceCode = DB::table('material_types')
            ->whereNotNull('catalogue_source_code')
            ->get(['id', 'material_type_name', 'material_type_code', 'catalogue_source_code', 'is_legacy', 'unit_master_id'])
            ->keyBy(fn ($row) => $this->normalizeKey($row->catalogue_source_code));

        $existingByName = DB::table('material_types')
            ->get(['id', 'material_type_name', 'material_type_code', 'catalogue_source_code', 'is_legacy', 'unit_master_id'])
            ->groupBy(fn ($row) => $this->normalizeKey($row->material_type_name));

        $existingByMaterialCode = DB::table('material_types')
            ->get(['id', 'material_type_name', 'material_type_code', 'catalogue_source_code', 'is_legacy', 'unit_master_id'])
            ->keyBy(fn ($row) => $this->normalizeKey($row->material_type_code));

        $missingUnits = [];
        $blockingNameConflicts = [];
        $codeConflicts = [];
        $legacyAdoptions = [];
        $legacySameUnit = 0;
        $legacyMissingUnit = 0;
        $legacyDifferentUnit = 0;
        $existingCatalogueProducts = 0;

        foreach ($products as $product) {
            $sourceCodeKey = $this->normalizeKey($product->source_product_code);
            $nameKey = $this->normalizeKey($product->product_name);
            $unitKey = $this->normalizeKey($product->default_unit);

            $resolvedUnit = $unitMap->get($unitKey);

            if (! $resolvedUnit) {
                $missingUnits[] = [
                    'source_product_code' => $product->source_product_code,
                    'product_name' => $product->product_name,
                    'default_unit' => $product->default_unit,
                ];
            }

            if ($existingBySourceCode->has($sourceCodeKey)) {
                $existing = $existingBySourceCode->get($sourceCodeKey);

                if ($this->normalizeKey($existing->material_type_name) !== $nameKey) {
                    $codeConflicts[] = [
                        'source_product_code' => $product->source_product_code,
                        'staged_product' => $product->product_name,
                        'existing_product' => $existing->material_type_name,
                        'reason' => 'Catalogue source code already belongs to a different canonical product name.',
                    ];
                } else {
                    $existingCatalogueProducts++;
                }

                continue;
            }

            $matchedLegacy = null;

            if ($existingByName->has($nameKey)) {
                $rows = $existingByName->get($nameKey);

                if ($rows->count() > 1) {
                    $blockingNameConflicts[] = [
                        'source_product_code' => $product->source_product_code,
                        'staged_product' => $product->product_name,
                        'reason' => 'Multiple canonical products share the same normalized name.',
                    ];
                } else {
                    $existing = $rows->first();

                    if ((int) $existing->is_legacy === 1 && empty($existing->catalogue_source_code)) {
                        $matchedLegacy = $existing;
                    } else {
                        $blockingNameConflicts[] = [
                            'source_product_code' => $product->source_product_code,
                            'staged_product' => $product->product_name,
                            'reason' => 'Name matches a non-legacy or already-catalogued canonical product.',
                        ];
                    }
                }
            }

            if ($matchedLegacy) {
                $resolvedUnitId = $resolvedUnit ? (int) $resolvedUnit->unit_master_id : null;
                $legacyUnitId = $matchedLegacy->unit_master_id !== null
                    ? (int) $matchedLegacy->unit_master_id
                    : null;

                $unitDecision = 'Different Unit';

                if ($legacyUnitId === null) {
                    $legacyMissingUnit++;
                    $unitDecision = 'Missing Legacy Unit';
                } elseif ($resolvedUnitId !== null && $legacyUnitId === $resolvedUnitId) {
                    $legacySameUnit++;
                    $unitDecision = 'Same Unit';
                } else {
                    $legacyDifferentUnit++;
                }

                $legacyAdoptions[] = [
                    'legacy_id' => (int) $matchedLegacy->id,
                    'source_product_code' => $product->source_product_code,
                    'product_name' => $product->product_name,
                    'legacy_unit_master_id' => $legacyUnitId,
                    'catalogue_unit_master_id' => $resolvedUnitId,
                    'unit_decision' => $unitDecision,
                ];
            }

            if ($existingByMaterialCode->has($sourceCodeKey)) {
                $existing = $existingByMaterialCode->get($sourceCodeKey);

                if ($this->normalizeKey($existing->catalogue_source_code) !== $sourceCodeKey) {
                    $codeConflicts[] = [
                        'source_product_code' => $product->source_product_code,
                        'staged_product' => $product->product_name,
                        'existing_product' => $existing->material_type_name,
                        'reason' => 'Source product code conflicts with an existing material_type_code.',
                    ];
                }
            }
        }

        $legacyAdoptionCount = count($legacyAdoptions);

        return [
            'ready_products' => $products->count(),
            'review_products' => $reviewCount,
            'excluded_products' => $excludedCount,
            'error_products' => $errorCount,
            'unique_groups' => $groupNames->count(),
            'unique_types' => $typePairs->count(),

            'groups_existing' => $groupNames
                ->filter(fn ($name) => $existingGroups->has($this->normalizeKey($name)))
                ->count(),

            'groups_to_create' => $groupNames
                ->reject(fn ($name) => $existingGroups->has($this->normalizeKey($name)))
                ->count(),

            'types_existing' => $typePairs
                ->filter(fn ($pair) => $existingTypes->has(
                    $this->normalizeKey($pair['group']).'|'.$this->normalizeKey($pair['type'])
                ))
                ->count(),

            'types_to_create' => $typePairs
                ->reject(fn ($pair) => $existingTypes->has(
                    $this->normalizeKey($pair['group']).'|'.$this->normalizeKey($pair['type'])
                ))
                ->count(),

            'existing_catalogue_products' => $existingCatalogueProducts,
            'legacy_adoptions' => $legacyAdoptions,
            'legacy_adoption_count' => $legacyAdoptionCount,
            'legacy_same_unit' => $legacySameUnit,
            'legacy_missing_unit' => $legacyMissingUnit,
            'legacy_different_unit' => $legacyDifferentUnit,

            'products_to_create' => max(
                0,
                $products->count()
                - $existingCatalogueProducts
                - $legacyAdoptionCount
                - count($blockingNameConflicts)
                - count($codeConflicts)
            ),

            'missing_units' => $missingUnits,
            'blocking_name_conflicts' => $blockingNameConflicts,
            'code_conflicts' => $codeConflicts,

            'blocking_conflicts' => count($missingUnits)
                + count($blockingNameConflicts)
                + count($codeConflicts),
        ];
    }

    private function renderPreflight(array $preflight): void
    {
        $this->table(
            ['Check', 'Count'],
            [
                ['Ready staged products', number_format($preflight['ready_products'])],
                ['Review products left staged', number_format($preflight['review_products'])],
                ['Excluded products left staged', number_format($preflight['excluded_products'])],
                ['Error products', number_format($preflight['error_products'])],
                ['Unique Product Groups', number_format($preflight['unique_groups'])],
                ['Groups already canonical', number_format($preflight['groups_existing'])],
                ['Groups to create', number_format($preflight['groups_to_create'])],
                ['Unique Product Types', number_format($preflight['unique_types'])],
                ['Types already canonical', number_format($preflight['types_existing'])],
                ['Types to create', number_format($preflight['types_to_create'])],
                ['Catalogue products already canonical', number_format($preflight['existing_catalogue_products'])],
                ['Legacy products to adopt', number_format($preflight['legacy_adoption_count'])],
                ['Legacy adoption - same unit', number_format($preflight['legacy_same_unit'])],
                ['Legacy adoption - missing legacy unit', number_format($preflight['legacy_missing_unit'])],
                ['Legacy adoption - different unit', number_format($preflight['legacy_different_unit'])],
                ['New products planned for creation', number_format($preflight['products_to_create'])],
                ['Missing unit mappings', number_format(count($preflight['missing_units']))],
                ['Blocking name conflicts', number_format(count($preflight['blocking_name_conflicts']))],
                ['Canonical code conflicts', number_format(count($preflight['code_conflicts']))],
                ['Blocking conflicts', number_format($preflight['blocking_conflicts'])],
            ]
        );

        if (! empty($preflight['legacy_adoptions'])) {
            $this->newLine();
            $this->info('Legacy products eligible for controlled catalogue adoption:');

            $rows = array_map(
                fn ($row) => [
                    $row['legacy_id'],
                    $row['source_product_code'],
                    $row['product_name'],
                    $row['unit_decision'],
                    $row['legacy_unit_master_id'] ?? '-',
                    $row['catalogue_unit_master_id'] ?? '-',
                ],
                $preflight['legacy_adoptions']
            );

            $this->table(
                ['Legacy ID', 'Source Code', 'Product', 'Unit Decision', 'Legacy Unit ID', 'Catalogue Unit ID'],
                $rows
            );

            $this->comment(
                'Existing non-null legacy units will be preserved. '
                .'Catalogue unit is filled only when the legacy product has no unit.'
            );
        }

        if (! empty($preflight['missing_units'])) {
            $this->newLine();
            $this->error('Sample missing unit mappings:');

            $this->table(
                ['Product Code', 'Product', 'Unit'],
                array_slice($preflight['missing_units'], 0, 20)
            );
        }

        if (! empty($preflight['blocking_name_conflicts'])) {
            $this->newLine();
            $this->error('Blocking canonical product-name conflicts:');

            $rows = array_map(
                fn ($row) => [
                    $row['source_product_code'],
                    $row['staged_product'],
                    $row['reason'],
                ],
                array_slice($preflight['blocking_name_conflicts'], 0, 30)
            );

            $this->table(
                ['Source Code', 'Staged Product', 'Reason'],
                $rows
            );
        }

        if (! empty($preflight['code_conflicts'])) {
            $this->newLine();
            $this->error('Canonical code conflicts:');

            $rows = array_map(
                fn ($row) => [
                    $row['source_product_code'],
                    $row['staged_product'],
                    $row['existing_product'],
                    $row['reason'],
                ],
                array_slice($preflight['code_conflicts'], 0, 20)
            );

            $this->table(
                ['Source Code', 'Staged Product', 'Existing Product', 'Reason'],
                $rows
            );
        }
    }

    private function applyPromotion(int $batchId): array
    {
        $now = now();

        $products = DB::table('material_catalog_product_staging')
            ->where('import_batch_id', $batchId)
            ->where('staging_status', 'Ready')
            ->orderBy('id')
            ->get([
                'id',
                'source_product_code',
                'product_name',
                'product_group',
                'product_type',
                'inventory_type',
                'default_unit',
            ]);

        $unitMap = DB::table('material_catalog_unit_mapping_staging')
            ->where('import_batch_id', $batchId)
            ->whereNotNull('unit_master_id')
            ->get(['source_unit_name', 'unit_master_id'])
            ->keyBy(fn ($row) => $this->normalizeKey($row->source_unit_name));

        $groupNames = $products
            ->pluck('product_group')
            ->filter()
            ->unique(fn ($value) => $this->normalizeKey($value))
            ->values();

        $groupsCreated = 0;
        $groupsExisting = 0;

        foreach ($groupNames as $index => $groupName) {
            $groupName = trim((string) $groupName);

            $existing = DB::table('material_product_groups')
                ->whereRaw('LOWER(TRIM(group_name)) = ?', [$this->normalizeKey($groupName)])
                ->first();

            if ($existing) {
                $groupsExisting++;
                continue;
            }

            DB::table('material_product_groups')->insert([
                'group_code' => $this->makeUniqueGroupCode($groupName),
                'group_name' => $groupName,
                'sort_order' => ($index + 1) * 10,
                'is_active' => true,
                'remarks' => 'Imported from finalized Ravion Materials ERP catalogue.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $groupsCreated++;
        }

        $groups = DB::table('material_product_groups')
            ->get(['id', 'group_name'])
            ->keyBy(fn ($row) => $this->normalizeKey($row->group_name));

        $typePairs = $products
            ->map(fn ($row) => [
                'group' => trim((string) $row->product_group),
                'type' => trim((string) $row->product_type),
            ])
            ->unique(fn ($row) => $this->normalizeKey($row['group']).'|'.$this->normalizeKey($row['type']))
            ->values();

        $typesCreated = 0;
        $typesExisting = 0;

        foreach ($typePairs as $index => $pair) {
            $group = $groups->get($this->normalizeKey($pair['group']));

            if (! $group) {
                throw new RuntimeException(
                    "Cannot resolve Product Group [{$pair['group']}] while creating Product Type [{$pair['type']}]."
                );
            }

            $existing = DB::table('material_product_types')
                ->where('material_product_group_id', $group->id)
                ->whereRaw('LOWER(TRIM(type_name)) = ?', [$this->normalizeKey($pair['type'])])
                ->first();

            if ($existing) {
                $typesExisting++;
                continue;
            }

            DB::table('material_product_types')->insert([
                'material_product_group_id' => $group->id,
                'type_code' => $this->makeUniqueTypeCode($pair['group'], $pair['type']),
                'type_name' => $pair['type'],
                'sort_order' => ($index + 1) * 10,
                'is_active' => true,
                'remarks' => 'Imported from finalized Ravion Materials ERP catalogue.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $typesCreated++;
        }

        $types = DB::table('material_product_types')
            ->join(
                'material_product_groups',
                'material_product_groups.id',
                '=',
                'material_product_types.material_product_group_id'
            )
            ->get([
                'material_product_types.id',
                'material_product_types.type_name',
                'material_product_groups.group_name',
            ])
            ->keyBy(
                fn ($row) => $this->normalizeKey($row->group_name)
                    .'|'.$this->normalizeKey($row->type_name)
            );

        $maxSequence = (int) DB::table('material_types')->max('sequence');
        $nextSequence = $maxSequence > 0
            ? (int) (ceil($maxSequence / 10) * 10) + 10
            : 10;

        $productsCreated = 0;
        $productsExisting = 0;
        $productsSkipped = 0;
        $legacyAdopted = 0;
        $legacySameUnit = 0;
        $legacyMissingUnit = 0;
        $legacyDifferentUnit = 0;

        foreach ($products as $product) {
            $sourceCode = trim((string) $product->source_product_code);
            $productName = trim((string) $product->product_name);

            $existingCatalogue = DB::table('material_types')
                ->where('catalogue_source_code', $sourceCode)
                ->first();

            if ($existingCatalogue) {
                $productsExisting++;
                continue;
            }

            $group = $groups->get($this->normalizeKey($product->product_group));
            $type = $types->get(
                $this->normalizeKey($product->product_group)
                .'|'.$this->normalizeKey($product->product_type)
            );
            $unit = $unitMap->get($this->normalizeKey($product->default_unit));

            if (! $group || ! $type || ! $unit) {
                throw new RuntimeException(
                    "Cannot resolve hierarchy/unit for {$sourceCode} - {$productName}."
                );
            }

            $nameMatches = DB::table('material_types')
                ->whereRaw(
                    'LOWER(TRIM(material_type_name)) = ?',
                    [$this->normalizeKey($productName)]
                )
                ->get();

            if ($nameMatches->count() > 1) {
                throw new RuntimeException(
                    "Multiple canonical products share normalized name [{$productName}]."
                );
            }

            if ($nameMatches->count() === 1) {
                $legacy = $nameMatches->first();

                if ((int) $legacy->is_legacy !== 1 || ! empty($legacy->catalogue_source_code)) {
                    throw new RuntimeException(
                        "Product-name match [{$productName}] is not an untouched legacy record."
                    );
                }

                $catalogueUnitId = (int) $unit->unit_master_id;
                $legacyUnitId = $legacy->unit_master_id !== null
                    ? (int) $legacy->unit_master_id
                    : null;

                if ($legacyUnitId === null) {
                    $legacyMissingUnit++;
                } elseif ($legacyUnitId === $catalogueUnitId) {
                    $legacySameUnit++;
                } else {
                    $legacyDifferentUnit++;
                }

                DB::table('material_types')
                    ->where('id', $legacy->id)
                    ->update([
                        'material_product_group_id' => $group->id,
                        'material_product_type_id' => $type->id,
                        'material_group' => trim((string) $product->product_group),

                        // Preserve legacy material_type_name and material_type_code.
                        'catalogue_source_code' => $sourceCode,
                        'inventory_type' => trim((string) $product->inventory_type),
                        'master_status' => 'Approved',
                        'is_legacy' => false,

                        // Existing non-null legacy unit wins.
                        'unit_master_id' => $legacyUnitId ?? $catalogueUnitId,

                        'is_active' => true,
                        'remarks' => $this->mergeRemarks(
                            $legacy->remarks ?? null,
                            'Adopted into validated Ravion Materials ERP catalogue. '
                            .'Original legacy product ID and code preserved.'
                        ),
                        'updated_at' => $now,
                    ]);

                $legacyAdopted++;
                continue;
            }

            $materialCodeConflict = DB::table('material_types')
                ->where('material_type_code', $sourceCode)
                ->exists();

            if ($materialCodeConflict) {
                throw new RuntimeException(
                    "Source product code [{$sourceCode}] already exists as material_type_code."
                );
            }

            DB::table('material_types')->insert([
                'material_product_group_id' => $group->id,
                'material_product_type_id' => $type->id,
                'material_group' => trim((string) $product->product_group),
                'material_type_name' => $productName,
                'material_type_code' => $sourceCode,
                'catalogue_source_code' => $sourceCode,
                'inventory_type' => trim((string) $product->inventory_type),
                'master_status' => 'Approved',
                'is_legacy' => false,
                'unit_master_id' => (int) $unit->unit_master_id,
                'sequence' => $nextSequence,
                'is_active' => true,
                'remarks' => 'Promoted from validated Ravion Materials ERP catalogue.',
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $productsCreated++;
            $nextSequence += 10;
        }

        return [
            'groups_created' => $groupsCreated,
            'groups_existing' => $groupsExisting,
            'types_created' => $typesCreated,
            'types_existing' => $typesExisting,
            'products_created' => $productsCreated,
            'products_existing' => $productsExisting,
            'products_skipped' => $productsSkipped,
            'legacy_adopted' => $legacyAdopted,
            'legacy_same_unit' => $legacySameUnit,
            'legacy_missing_unit' => $legacyMissingUnit,
            'legacy_different_unit' => $legacyDifferentUnit,
        ];
    }

    private function makeUniqueGroupCode(string $name): string
    {
        $base = Str::upper(
            Str::substr(
                preg_replace('/[^A-Za-z0-9]+/', '-', Str::ascii($name)) ?? '',
                0,
                24
            )
        );

        $base = trim($base, '-');

        if ($base === '') {
            $base = 'GROUP';
        }

        $candidate = $base;

        if (! DB::table('material_product_groups')->where('group_code', $candidate)->exists()) {
            return $candidate;
        }

        $hash = strtoupper(substr(sha1($name), 0, 6));

        return Str::substr($base, 0, 17).'-'.$hash;
    }

    private function makeUniqueTypeCode(string $groupName, string $typeName): string
    {
        $base = Str::upper(
            Str::substr(
                preg_replace('/[^A-Za-z0-9]+/', '-', Str::ascii($typeName)) ?? '',
                0,
                24
            )
        );

        $base = trim($base, '-');

        if ($base === '') {
            $base = 'TYPE';
        }

        $candidate = $base;

        if (! DB::table('material_product_types')->where('type_code', $candidate)->exists()) {
            return $candidate;
        }

        $hash = strtoupper(substr(sha1($groupName.'|'.$typeName), 0, 6));
        $candidate = Str::substr($base, 0, 17).'-'.$hash;
        $counter = 2;

        while (DB::table('material_product_types')->where('type_code', $candidate)->exists()) {
            $suffix = '-'.$counter;
            $candidate = Str::substr($base, 0, max(1, 24 - strlen($suffix))).$suffix;
            $counter++;
        }

        return $candidate;
    }

    private function mergeRemarks(?string $existingRemarks, string $newRemark): string
    {
        $existingRemarks = trim((string) $existingRemarks);

        if ($existingRemarks === '') {
            return $newRemark;
        }

        if (Str::contains($existingRemarks, $newRemark)) {
            return $existingRemarks;
        }

        return $existingRemarks.' | '.$newRemark;
    }

    private function normalizeKey(?string $value): string
    {
        return Str::lower(
            trim(
                preg_replace('/\s+/', ' ', (string) $value) ?? ''
            )
        );
    }
}
