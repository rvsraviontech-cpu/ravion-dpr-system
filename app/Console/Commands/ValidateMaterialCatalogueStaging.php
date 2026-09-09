<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ValidateMaterialCatalogueStaging extends Command
{
    protected $signature = 'materials:catalog-validate
                            {batch-code : Catalogue import batch code}
                            {--apply : Persist validation decisions back to staging tables}';

    protected $description =
        'Validate staged Ravion Materials catalogue data before canonical Product Master promotion.';

    public function handle(): int
    {
        $batchCode = trim((string) $this->argument('batch-code'));

        $batch = DB::table('material_catalog_import_batches')
            ->where('batch_code', $batchCode)
            ->first();

        if (! $batch) {
            $this->error("Import batch not found: {$batchCode}");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Ravion Materials Catalogue Validation');
        $this->line("Batch: {$batch->batch_code}");
        $this->line("Status: {$batch->status}");
        $this->newLine();

        $productStats = $this->validateProducts($batch->id);
        $unitStats = $this->validateUnits($batch->id);
        $childStats = $this->validateChildDatasets($batch->id);
        $specialStats = $this->validateSpecialGroups($batch->id);

        $this->displayProductSummary($productStats);
        $this->displayUnitSummary($unitStats);
        $this->displayChildSummary($childStats);
        $this->displaySpecialSummary($specialStats);

        if ($this->option('apply')) {
            $this->applyValidationDecisions(
                $batch->id,
                $productStats,
                $unitStats,
                $childStats,
                $specialStats
            );

            $this->newLine();
            $this->info('Validation decisions written back to staging.');
        } else {
            $this->newLine();
            $this->comment(
                'Dry-run only. No staging rows were modified. '
                . 'Use --apply only after reviewing this report.'
            );
        }

        return self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | Product Validation
    |--------------------------------------------------------------------------
    */

    private function validateProducts(int $batchId): array
    {
        $products = DB::table('material_catalog_product_staging')
            ->where('import_batch_id', $batchId)
            ->get();

        $duplicateSourceCodes = $products
            ->groupBy('source_product_code')
            ->filter(fn (Collection $rows) => $rows->count() > 1);

        $duplicateNormalizedNames = $products
            ->filter(
                fn ($row) =>
                    $row->normalized_product_name !== null
                    && trim($row->normalized_product_name) !== ''
            )
            ->groupBy('normalized_product_name')
            ->filter(fn (Collection $rows) => $rows->count() > 1);

        $missingGroup = $products
            ->filter(
                fn ($row) =>
                    $row->product_group === null
                    || trim((string) $row->product_group) === ''
            );

        $missingType = $products
            ->filter(
                fn ($row) =>
                    $row->product_type === null
                    || trim((string) $row->product_type) === ''
            );

        $missingUnit = $products
            ->filter(
                fn ($row) =>
                    $row->default_unit === null
                    || trim((string) $row->default_unit) === ''
            );

        $missingInventoryType = $products
            ->filter(
                fn ($row) =>
                    $row->inventory_type === null
                    || trim((string) $row->inventory_type) === ''
            );

        $groups = $products
            ->pluck('product_group')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $types = $products
            ->map(fn ($row) => [
                'group' => $row->product_group,
                'type' => $row->product_type,
            ])
            ->filter(
                fn ($row) =>
                    ! empty($row['group'])
                    && ! empty($row['type'])
            )
            ->unique(
                fn ($row) =>
                    Str::lower(trim($row['group']))
                    . '|'
                    . Str::lower(trim($row['type']))
            )
            ->values();

        $inventoryTypes = $products
            ->pluck('inventory_type')
            ->filter()
            ->countBy()
            ->sortDesc();

        $sourceStatuses = $products
            ->pluck('source_master_status')
            ->map(
                fn ($value) =>
                    $value === null || trim((string) $value) === ''
                        ? '(blank)'
                        : trim((string) $value)
            )
            ->countBy()
            ->sortDesc();

        return [
            'total' => $products->count(),

            'ready' =>
                $products
                    ->where('staging_status', 'Ready')
                    ->count(),

            'review' =>
                $products
                    ->where('staging_status', 'Review')
                    ->count(),

            'errors' =>
                $products
                    ->where('staging_status', 'Error')
                    ->count(),

            'excluded' =>
                $products
                    ->where('staging_status', 'Excluded')
                    ->count(),

            'duplicate_source_codes' =>
                $duplicateSourceCodes,

            'duplicate_normalized_names' =>
                $duplicateNormalizedNames,

            'missing_group' => $missingGroup,
            'missing_type' => $missingType,
            'missing_unit' => $missingUnit,
            'missing_inventory_type' => $missingInventoryType,

            'group_count' => $groups->count(),
            'type_count' => $types->count(),

            'groups' => $groups,
            'inventory_types' => $inventoryTypes,
            'source_statuses' => $sourceStatuses,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Unit Validation
    |--------------------------------------------------------------------------
    */

    private function validateUnits(int $batchId): array
    {
        $stagedUnits = DB::table(
            'material_catalog_unit_mapping_staging'
        )
            ->where('import_batch_id', $batchId)
            ->get();

        $masterUnits = DB::table('unit_masters')
            ->get();

        $exact = collect();
        $probable = collect();
        $unresolved = collect();

        foreach ($stagedUnits as $staged) {
            $sourceName = $this->normalize(
                $staged->source_unit_name
            );

            $sourceCode = $this->normalize(
                $staged->source_unit_code
            );

            $sourceSymbol = $this->normalize(
                $staged->source_unit_symbol
            );

            /*
|--------------------------------------------------------------------------
| Catalogue Unit Aliases
|--------------------------------------------------------------------------
|
| The finalized catalogue may use common site abbreviations that differ
| from the canonical Ravion Unit Master naming.
|
| These aliases are explicit and controlled. We do not use fuzzy matching
| for unit conversion because quantities must never be silently assigned
| to the wrong unit.
|
*/

$unitAliases = [
    'mtr' => 'mtr',
];

if (
    $sourceName !== null
    && isset($unitAliases[$sourceName])
) {
    $sourceCode = $unitAliases[$sourceName];
}

            $exactMatch = $masterUnits->first(function ($unit) use (
                $sourceName,
                $sourceCode,
                $sourceSymbol
            ) {
                $masterName = $this->normalize(
                    $unit->unit_name ?? null
                );

                $masterCode = $this->normalize(
                    $unit->unit_code ?? null
                );

                $masterSymbol = $this->normalize(
                    $unit->symbol ?? null
                );

                return (
                    $sourceName !== null
                    && $sourceName === $masterName
                ) || (
                    $sourceCode !== null
                    && $sourceCode === $masterCode
                ) || (
                    $sourceSymbol !== null
                    && $sourceSymbol === $masterSymbol
                );
            });

            if ($exactMatch) {
                $exact->push([
                    'staged' => $staged,
                    'unit_master_id' => $exactMatch->id,
                    'unit_master_name' => $exactMatch->unit_name,
                ]);

                continue;
            }

            $probableMatch = $masterUnits->first(function ($unit) use (
                $sourceName
            ) {
                if ($sourceName === null) {
                    return false;
                }

                $masterName = $this->normalize(
                    $unit->unit_name ?? null
                );

                if ($masterName === null) {
                    return false;
                }

                return str_contains($sourceName, $masterName)
                    || str_contains($masterName, $sourceName);
            });

            if ($probableMatch) {
                $probable->push([
                    'staged' => $staged,
                    'unit_master_id' => $probableMatch->id,
                    'unit_master_name' => $probableMatch->unit_name,
                ]);

                continue;
            }

            $unresolved->push($staged);
        }

        return [
            'total' => $stagedUnits->count(),
            'exact' => $exact,
            'probable' => $probable,
            'unresolved' => $unresolved,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Child Dataset Validation
    |--------------------------------------------------------------------------
    */

    private function validateChildDatasets(int $batchId): array
    {
        $productCodes = DB::table(
            'material_catalog_product_staging'
        )
            ->where('import_batch_id', $batchId)
            ->pluck('source_product_code')
            ->filter()
            ->flip();

        $specifications = DB::table(
            'material_catalog_specification_staging'
        )
            ->where('import_batch_id', $batchId)
            ->get();

        $usageMappings = DB::table(
            'material_catalog_usage_mapping_staging'
        )
            ->where('import_batch_id', $batchId)
            ->get();

        $aliases = DB::table(
            'material_catalog_search_alias_staging'
        )
            ->where('import_batch_id', $batchId)
            ->get();

        $audit = DB::table(
            'material_catalog_import_audit_staging'
        )
            ->where('import_batch_id', $batchId)
            ->get();

        $orphanSpecifications = $specifications
            ->filter(
                fn ($row) =>
                    ! $productCodes->has(
                        $row->source_product_code
                    )
            );

        $orphanUsage = $usageMappings
            ->filter(
                fn ($row) =>
                    ! $productCodes->has(
                        $row->source_product_code
                    )
            );

        $orphanAliases = $aliases
            ->filter(
                fn ($row) =>
                    ! $productCodes->has(
                        $row->source_product_code
                    )
            );

        $usageDivisionCount = $usageMappings
            ->pluck('division_code')
            ->filter()
            ->unique()
            ->count();

        $usageCodeCount = $usageMappings
            ->pluck('usage_code')
            ->filter()
            ->unique()
            ->count();

        $usageNameCount = $usageMappings
            ->pluck('usage_name')
            ->filter()
            ->unique()
            ->count();

        $productsWithUsage = $usageMappings
            ->pluck('source_product_code')
            ->filter()
            ->unique()
            ->count();

        $productsWithAliases = $aliases
            ->pluck('source_product_code')
            ->filter()
            ->unique()
            ->count();

        return [
            'specifications_total' => $specifications->count(),
            'orphan_specifications' => $orphanSpecifications,

            'usage_total' => $usageMappings->count(),
            'orphan_usage' => $orphanUsage,

            'aliases_total' => $aliases->count(),
            'orphan_aliases' => $orphanAliases,

            'audit_total' => $audit->count(),

            'usage_division_count' => $usageDivisionCount,
            'usage_code_count' => $usageCodeCount,
            'usage_name_count' => $usageNameCount,
            'products_with_usage' => $productsWithUsage,
            'products_with_aliases' => $productsWithAliases,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Special / Exclusion Review
    |--------------------------------------------------------------------------
    */

    private function validateSpecialGroups(int $batchId): array
    {
        $products = DB::table(
            'material_catalog_product_staging'
        )
            ->where('import_batch_id', $batchId)
            ->get();

        $closeoutDocumentation = $products
            ->filter(
                fn ($row) =>
                    $this->normalize($row->product_group)
                    === 'closeout documentation'
            );

        $handoverSpares = $products
            ->filter(
                fn ($row) =>
                    $this->normalize($row->product_group)
                    === 'handover spares'
            );

        /*
        |--------------------------------------------------------------------------
        | Suspected non-physical records
        |--------------------------------------------------------------------------
        |
        | These are recommendations for review only.
        |
        | Nothing is excluded automatically in dry-run mode.
        |
        */

        $nonPhysicalKeywords = [
            'warranty',
            'certificate',
            'training',
            'manual',
            'document',
            'documentation',
            'report',
            'handover file',
            'test report',
            'operation manual',
            'maintenance manual',
            'o&m',
            'as built',
            'as-built',
        ];

        $suspectedNonPhysical = $products->filter(
            function ($row) use ($nonPhysicalKeywords) {
                $haystack = $this->normalize(
                    implode(' ', [
                        $row->product_group ?? '',
                        $row->product_type ?? '',
                        $row->product_name ?? '',
                    ])
                );

                if ($haystack === null) {
                    return false;
                }

                foreach ($nonPhysicalKeywords as $keyword) {
                    if (
                        str_contains(
                            $haystack,
                            $this->normalize($keyword)
                        )
                    ) {
                        return true;
                    }
                }

                return false;
            }
        );

        return [
            'closeout_documentation' =>
                $closeoutDocumentation,

            'handover_spares' =>
                $handoverSpares,

            'suspected_non_physical' =>
                $suspectedNonPhysical,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Decisions
    |--------------------------------------------------------------------------
    */

    private function applyValidationDecisions(
        int $batchId,
        array $productStats,
        array $unitStats,
        array $childStats,
        array $specialStats
    ): void {
        DB::transaction(function () use (
            $batchId,
            $productStats,
            $unitStats,
            $childStats,
            $specialStats
        ) {
            /*
            |--------------------------------------------------------------------------
            | Product errors
            |--------------------------------------------------------------------------
            */

            foreach (
                $productStats['duplicate_source_codes']
                as $sourceCode => $rows
            ) {
                DB::table(
                    'material_catalog_product_staging'
                )
                    ->where('import_batch_id', $batchId)
                    ->where(
                        'source_product_code',
                        $sourceCode
                    )
                    ->update([
                        'staging_status' => 'Error',
                        'validation_errors' =>
                            json_encode(
                                [
                                    'Duplicate source Product Code',
                                ],
                                JSON_UNESCAPED_UNICODE
                            ),
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Missing required classification fields
            |--------------------------------------------------------------------------
            */

            DB::table('material_catalog_product_staging')
                ->where('import_batch_id', $batchId)
                ->where(function ($query) {
                    $query
                        ->whereNull('product_group')
                        ->orWhere('product_group', '')
                        ->orWhereNull('product_type')
                        ->orWhere('product_type', '')
                        ->orWhereNull('default_unit')
                        ->orWhere('default_unit', '');
                })
                ->update([
                    'staging_status' => 'Review',
                    'review_reason' =>
                        'Missing Product Group, Product Type or Default Unit.',
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Unit exact matches
            |--------------------------------------------------------------------------
            */

            foreach ($unitStats['exact'] as $match) {
                DB::table(
                    'material_catalog_unit_mapping_staging'
                )
                    ->where(
                        'id',
                        $match['staged']->id
                    )
                    ->update([
                        'unit_master_id' =>
                            $match['unit_master_id'],

                        'staging_status' =>
                            'Ready',

                        'review_reason' => null,
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Probable Unit Matches
            |--------------------------------------------------------------------------
            */

            foreach ($unitStats['probable'] as $match) {
                DB::table(
                    'material_catalog_unit_mapping_staging'
                )
                    ->where(
                        'id',
                        $match['staged']->id
                    )
                    ->update([
                        'unit_master_id' =>
                            $match['unit_master_id'],

                        'staging_status' =>
                            'Review',

                        'review_reason' =>
                            'Probable Unit Master match. Manual confirmation required.',

                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Unresolved Units
            |--------------------------------------------------------------------------
            */

            foreach ($unitStats['unresolved'] as $row) {
                DB::table(
                    'material_catalog_unit_mapping_staging'
                )
                    ->where('id', $row->id)
                    ->update([
                        'unit_master_id' => null,
                        'staging_status' => 'Review',
                        'review_reason' =>
                            'No existing Ravion Unit Master match found.',
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Orphan child rows
            |--------------------------------------------------------------------------
            */

            foreach (
                $childStats['orphan_specifications']
                as $row
            ) {
                DB::table(
                    'material_catalog_specification_staging'
                )
                    ->where('id', $row->id)
                    ->update([
                        'staging_status' => 'Error',
                        'validation_errors' =>
                            json_encode(
                                [
                                    'Source Product Code not found in Product staging.',
                                ],
                                JSON_UNESCAPED_UNICODE
                            ),
                        'updated_at' => now(),
                    ]);
            }

            foreach (
                $childStats['orphan_usage']
                as $row
            ) {
                DB::table(
                    'material_catalog_usage_mapping_staging'
                )
                    ->where('id', $row->id)
                    ->update([
                        'staging_status' => 'Error',
                        'validation_errors' =>
                            json_encode(
                                [
                                    'Source Product Code not found in Product staging.',
                                ],
                                JSON_UNESCAPED_UNICODE
                            ),
                        'updated_at' => now(),
                    ]);
            }

            foreach (
                $childStats['orphan_aliases']
                as $row
            ) {
                DB::table(
                    'material_catalog_search_alias_staging'
                )
                    ->where('id', $row->id)
                    ->update([
                        'staging_status' => 'Error',
                        'validation_errors' =>
                            json_encode(
                                [
                                    'Source Product Code not found in Product staging.',
                                ],
                                JSON_UNESCAPED_UNICODE
                            ),
                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Special product groups
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | These are not automatically Excluded.
            |
            | We deliberately downgrade them to Review because some items inside
            | these groups may be genuine physical materials/spares.
            |
            */

            

            

            

            /*
            |--------------------------------------------------------------------------
            | Recalculate Batch Counts
            |--------------------------------------------------------------------------
            */

            $ready = DB::table(
                'material_catalog_product_staging'
            )
                ->where('import_batch_id', $batchId)
                ->where('staging_status', 'Ready')
                ->count();

            $review = DB::table(
                'material_catalog_product_staging'
            )
                ->where('import_batch_id', $batchId)
                ->where('staging_status', 'Review')
                ->count();

            $excluded = DB::table(
                'material_catalog_product_staging'
            )
                ->where('import_batch_id', $batchId)
                ->where('staging_status', 'Excluded')
                ->count();

            $errors = DB::table(
                'material_catalog_product_staging'
            )
                ->where('import_batch_id', $batchId)
                ->where('staging_status', 'Error')
                ->count();

            DB::table(
                'material_catalog_import_batches'
            )
                ->where('id', $batchId)
                ->update([
                    'status' => 'Validated',
                    'ready_product_rows' => $ready,
                    'review_product_rows' => $review,
                    'excluded_product_rows' => $excluded,
                    'error_product_rows' => $errors,
                    'updated_at' => now(),
                ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    */

    private function displayProductSummary(
        array $stats
    ): void {
        $this->info('Product Master Integrity');

        $this->table(
            ['Check', 'Count'],
            [
                ['Products', number_format($stats['total'])],
                ['Ready', number_format($stats['ready'])],
                ['Review', number_format($stats['review'])],
                ['Errors', number_format($stats['errors'])],
                ['Excluded', number_format($stats['excluded'])],

                [
                    'Product Groups',
                    number_format($stats['group_count']),
                ],

                [
                    'Product Types',
                    number_format($stats['type_count']),
                ],

                [
                    'Duplicate Source Codes',
                    number_format(
                        $stats['duplicate_source_codes']->count()
                    ),
                ],

                [
                    'Duplicate Normalized Names',
                    number_format(
                        $stats['duplicate_normalized_names']->count()
                    ),
                ],

                [
                    'Missing Product Group',
                    number_format(
                        $stats['missing_group']->count()
                    ),
                ],

                [
                    'Missing Product Type',
                    number_format(
                        $stats['missing_type']->count()
                    ),
                ],

                [
                    'Missing Default Unit',
                    number_format(
                        $stats['missing_unit']->count()
                    ),
                ],

                [
                    'Missing Inventory Type',
                    number_format(
                        $stats['missing_inventory_type']->count()
                    ),
                ],
            ]
        );

        $this->newLine();

        $this->line('Source Master Statuses');

        $this->table(
            ['Status', 'Products'],
            $stats['source_statuses']
                ->map(
                    fn ($count, $status) =>
                        [$status, number_format($count)]
                )
                ->values()
                ->all()
        );

        $this->newLine();

        $this->line('Inventory Types');

        $this->table(
            ['Inventory Type', 'Products'],
            $stats['inventory_types']
                ->map(
                    fn ($count, $type) =>
                        [$type, number_format($count)]
                )
                ->values()
                ->all()
        );

        if (
            $stats['duplicate_normalized_names']->isNotEmpty()
        ) {
            $this->newLine();
            $this->warn(
                'Duplicate normalized Product Names exist. '
                . 'They are not automatically treated as duplicates because '
                . 'distinct catalogue products may legitimately share a normalized name.'
            );
        }
    }

    private function displayUnitSummary(
        array $stats
    ): void {
        $this->newLine();
        $this->info('Unit Mapping Validation');

        $this->table(
            ['Result', 'Count'],
            [
                [
                    'Staged Units',
                    number_format($stats['total']),
                ],
                [
                    'Exact Existing Matches',
                    number_format(
                        $stats['exact']->count()
                    ),
                ],
                [
                    'Probable Matches',
                    number_format(
                        $stats['probable']->count()
                    ),
                ],
                [
                    'Unresolved Units',
                    number_format(
                        $stats['unresolved']->count()
                    ),
                ],
            ]
        );

        if ($stats['unresolved']->isNotEmpty()) {
            $this->newLine();
            $this->warn('Unresolved Unit Names');

            $this->table(
                ['Code', 'Unit', 'Symbol'],
                $stats['unresolved']
                    ->take(50)
                    ->map(
                        fn ($row) => [
                            $row->source_unit_code,
                            $row->source_unit_name,
                            $row->source_unit_symbol,
                        ]
                    )
                    ->values()
                    ->all()
            );
        }
    }

    private function displayChildSummary(
        array $stats
    ): void {
        $this->newLine();
        $this->info('Child Dataset Integrity');

        $this->table(
            ['Dataset / Check', 'Count'],
            [
                [
                    'Specifications',
                    number_format(
                        $stats['specifications_total']
                    ),
                ],
                [
                    'Orphan Specifications',
                    number_format(
                        $stats['orphan_specifications']->count()
                    ),
                ],
                [
                    'Usage Mappings',
                    number_format($stats['usage_total']),
                ],
                [
                    'Orphan Usage Mappings',
                    number_format(
                        $stats['orphan_usage']->count()
                    ),
                ],
                [
                    'Usage Divisions',
                    number_format(
                        $stats['usage_division_count']
                    ),
                ],
                [
                    'Unique Usage Codes',
                    number_format(
                        $stats['usage_code_count']
                    ),
                ],
                [
                    'Unique Usage Names',
                    number_format(
                        $stats['usage_name_count']
                    ),
                ],
                [
                    'Products With Usage',
                    number_format(
                        $stats['products_with_usage']
                    ),
                ],
                [
                    'Search Aliases',
                    number_format(
                        $stats['aliases_total']
                    ),
                ],
                [
                    'Orphan Search Aliases',
                    number_format(
                        $stats['orphan_aliases']->count()
                    ),
                ],
                [
                    'Products With Aliases',
                    number_format(
                        $stats['products_with_aliases']
                    ),
                ],
                [
                    'Import Audit Rows',
                    number_format(
                        $stats['audit_total']
                    ),
                ],
            ]
        );
    }

    private function displaySpecialSummary(
        array $stats
    ): void {
        $this->newLine();
        $this->info('Special Review Groups');

        $this->table(
            ['Review Area', 'Products'],
            [
                [
                    'Closeout Documentation',
                    number_format(
                        $stats['closeout_documentation']->count()
                    ),
                ],
                [
                    'Handover Spares',
                    number_format(
                        $stats['handover_spares']->count()
                    ),
                ],
                [
                    'Suspected Non-Physical',
                    number_format(
                        $stats['suspected_non_physical']->count()
                    ),
                ],
            ]
        );

        if (
            $stats['suspected_non_physical']->isNotEmpty()
        ) {
            $this->newLine();

            $this->warn(
                'Sample products requiring physical/non-physical review'
            );

            $this->table(
                [
                    'Product Code',
                    'Group',
                    'Type',
                    'Product',
                    'Current Status',
                ],
                $stats['suspected_non_physical']
                    ->take(30)
                    ->map(
                        fn ($row) => [
                            $row->source_product_code,
                            Str::limit(
                                $row->product_group ?? '',
                                30
                            ),
                            Str::limit(
                                $row->product_type ?? '',
                                30
                            ),
                            Str::limit(
                                $row->product_name ?? '',
                                60
                            ),
                            $row->staging_status,
                        ]
                    )
                    ->values()
                    ->all()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function normalize(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = Str::lower(trim($value));

        if ($value === '') {
            return null;
        }

        $value = str_replace(
            [
                '–',
                '—',
                '−',
                '/',
                '\\',
                '&',
                '+',
            ],
            [
                '-',
                '-',
                '-',
                ' ',
                ' ',
                ' and ',
                ' ',
            ],
            $value
        );

        $value = preg_replace(
            '/[^a-z0-9]+/u',
            ' ',
            $value
        ) ?? $value;

        $value = preg_replace(
            '/\s+/u',
            ' ',
            $value
        ) ?? $value;

        return trim($value);
    }
}