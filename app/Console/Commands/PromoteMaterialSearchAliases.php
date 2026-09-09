<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PromoteMaterialSearchAliases extends Command
{
    protected $signature = 'materials:catalog-promote-aliases
                            {batch-code : Validated catalogue import batch code}
                            {--apply : Persist the alias promotion. Without this option the command is dry-run only.}';

    protected $description = 'Promote search aliases from a validated Ravion Materials catalogue staging batch into the canonical material_search_aliases table.';

    private const SOURCE_LABEL = 'Ravion Materials ERP Catalogue V1';

    public function handle(): int
    {
        $batchCode = trim((string) $this->argument('batch-code'));
        $apply = (bool) $this->option('apply');

        $this->newLine();
        $this->info('Ravion Material Search Alias Promotion');
        $this->line('Batch: '.$batchCode);
        $this->line('Mode: '.($apply ? 'APPLY' : 'DRY RUN'));
        $this->newLine();

        try {
            $this->guardRequiredTables();

            $batch = DB::table('material_catalog_import_batches')
                ->where('batch_code', $batchCode)
                ->first();

            if (! $batch) {
                $this->error("Import batch [{$batchCode}] was not found.");

                return self::FAILURE;
            }

            if ((string) $batch->status !== 'Validated') {
                $this->error(
                    "Import batch [{$batchCode}] must have status [Validated]. "
                    ."Current status: [{$batch->status}]."
                );

                return self::FAILURE;
            }

            $columns = $this->resolveStagingColumns();

            $preflight = $this->buildPreflight((int) $batch->id, $columns);
            $this->renderPreflight($preflight);

            if ($preflight['blocking_conflicts'] > 0) {
                $this->newLine();
                $this->error('Alias promotion stopped because blocking conflicts were found.');
                $this->line('No canonical alias rows were modified.');

                return self::FAILURE;
            }

            if (! $apply) {
                $this->newLine();
                $this->comment('Dry-run only. No canonical alias rows were modified.');
                $this->line(
                    'When the report is acceptable, run: php artisan '
                    .$this->getName().' '.$batchCode.' --apply'
                );

                return self::SUCCESS;
            }

            $result = DB::transaction(function () use ($batch, $columns) {
                return $this->applyPromotion((int) $batch->id, $columns);
            }, 3);

            $this->newLine();
            $this->info('Search alias promotion completed successfully.');

            $this->table(
                ['Result', 'Count'],
                [
                    ['Aliases created', number_format($result['created'])],
                    ['Aliases already canonical', number_format($result['existing'])],
                    ['Duplicate staged aliases skipped', number_format($result['duplicate_staging'])],
                    ['Review-product aliases left staged', number_format($result['review_left_staged'])],
                    ['Excluded/error-product aliases left staged', number_format($result['non_ready_left_staged'])],
                ]
            );

            $this->line(
                'Canonical search aliases now present: '
                .number_format(DB::table('material_search_aliases')->count())
            );

            $this->newLine();
            $this->comment(
                'Only aliases belonging to Ready catalogue products were promoted. '
                .'Review-product aliases remain staged for later approval.'
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('Search alias promotion failed.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function guardRequiredTables(): void
    {
        $required = [
            'material_catalog_import_batches',
            'material_catalog_product_staging',
            'material_catalog_search_alias_staging',
            'material_types',
            'material_search_aliases',
        ];

        foreach ($required as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Required table [{$table}] does not exist.");
            }
        }

        $canonicalRequired = [
            'id',
            'material_type_id',
            'material_variant_id',
            'alias',
            'normalized_alias',
            'sort_order',
            'is_active',
            'source',
            'remarks',
            'created_at',
            'updated_at',
        ];

        $canonicalColumns = Schema::getColumnListing('material_search_aliases');

        foreach ($canonicalRequired as $column) {
            if (! in_array($column, $canonicalColumns, true)) {
                throw new RuntimeException(
                    "Canonical table [material_search_aliases] is missing required column [{$column}]."
                );
            }
        }
    }

    /**
     * Resolve staging column names defensively so this command remains compatible
     * with the exact Phase 2B staging migration already installed locally.
     */
    private function resolveStagingColumns(): array
    {
        $columns = Schema::getColumnListing('material_catalog_search_alias_staging');

        return [
            'product_code' => $this->firstExistingColumn(
                $columns,
                ['source_product_code', 'product_code', 'v3_product_code'],
                'staged Product Code'
            ),
            'alias' => $this->firstExistingColumn(
                $columns,
                ['search_alias', 'alias', 'alias_keyword', 'source_alias'],
                'staged Search Alias'
            ),
            'alias_type' => $this->firstExistingColumn(
                $columns,
                ['alias_type', 'source_alias_type'],
                'staged Alias Type',
                false
            ),
            'source_status' => $this->firstExistingColumn(
                $columns,
                ['source_status', 'status'],
                'staged source Status',
                false
            ),
            'staging_status' => $this->firstExistingColumn(
                $columns,
                ['staging_status'],
                'staging status',
                false
            ),
        ];
    }

    private function firstExistingColumn(
        array $available,
        array $candidates,
        string $label,
        bool $required = true
    ): ?string {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $available, true)) {
                return $candidate;
            }
        }

        if ($required) {
            throw new RuntimeException(
                "Could not resolve {$label} column in [material_catalog_search_alias_staging]. "
                .'Available columns: '.implode(', ', $available)
            );
        }

        return null;
    }

    private function buildPreflight(int $batchId, array $columns): array
    {
        $rows = $this->loadStagedAliasRows($batchId, $columns);

        $readyRows = $rows->where('product_staging_status', 'Ready')->values();
        $reviewRows = $rows->where('product_staging_status', 'Review')->values();
        $nonReadyRows = $rows
            ->reject(fn ($row) => in_array($row->product_staging_status, ['Ready', 'Review'], true))
            ->values();

        $missingProducts = [];
        $sourceCodeConflicts = [];
        $emptyAliases = [];
        $duplicateStaging = [];
        $planned = [];
        $seen = [];

        $productMap = DB::table('material_types')
            ->whereNotNull('catalogue_source_code')
            ->get([
                'id',
                'material_type_name',
                'catalogue_source_code',
                'is_legacy',
                'master_status',
                'is_active',
            ])
            ->keyBy(fn ($row) => $this->normalizeKey($row->catalogue_source_code));

        $existingAliases = DB::table('material_search_aliases')
            ->whereNull('material_variant_id')
            ->get(['id', 'material_type_id', 'normalized_alias'])
            ->groupBy('material_type_id');

        foreach ($readyRows as $row) {
            $sourceCode = trim((string) $row->source_product_code);
            $alias = trim((string) $row->search_alias);
            $sourceKey = $this->normalizeKey($sourceCode);

            if ($alias === '') {
                $emptyAliases[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                ];
                continue;
            }

            $product = $productMap->get($sourceKey);

            if (! $product) {
                $missingProducts[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'alias' => $alias,
                ];
                continue;
            }

            if (
                (string) $product->master_status !== 'Approved'
                || (int) $product->is_legacy === 1
                || (int) $product->is_active !== 1
            ) {
                $sourceCodeConflicts[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'alias' => $alias,
                    'reason' => 'Resolved canonical product is not an active Approved catalogue product.',
                ];
                continue;
            }

            $normalizedAlias = $this->normalizeAlias($alias);
            $dedupeKey = $product->id.'|'.$normalizedAlias;

            if (isset($seen[$dedupeKey])) {
                $duplicateStaging[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'alias' => $alias,
                ];
                continue;
            }

            $seen[$dedupeKey] = true;

            $existingForProduct = $existingAliases->get($product->id, collect());

            $alreadyExists = $existingForProduct->contains(
                fn ($existing) => $this->normalizeAlias((string) $existing->normalized_alias) === $normalizedAlias
            );

            $planned[] = [
                'material_type_id' => (int) $product->id,
                'source_product_code' => $sourceCode,
                'product_name' => (string) $product->material_type_name,
                'alias' => $alias,
                'normalized_alias' => $normalizedAlias,
                'alias_type' => $row->alias_type,
                'source_status' => $row->source_status,
                'already_exists' => $alreadyExists,
            ];
        }

        $plannedCollection = collect($planned);

        return [
            'staged_total' => $rows->count(),
            'ready_alias_rows' => $readyRows->count(),
            'review_alias_rows' => $reviewRows->count(),
            'non_ready_alias_rows' => $nonReadyRows->count(),
            'ready_products_with_aliases' => $readyRows->pluck('source_product_code')->unique()->count(),
            'review_products_with_aliases' => $reviewRows->pluck('source_product_code')->unique()->count(),
            'planned_create' => $plannedCollection->where('already_exists', false)->count(),
            'already_canonical' => $plannedCollection->where('already_exists', true)->count(),
            'duplicate_staging' => $duplicateStaging,
            'missing_products' => $missingProducts,
            'source_code_conflicts' => $sourceCodeConflicts,
            'empty_aliases' => $emptyAliases,
            'blocking_conflicts' => count($missingProducts)
                + count($sourceCodeConflicts)
                + count($emptyAliases),
        ];
    }

    private function renderPreflight(array $preflight): void
    {
        $this->table(
            ['Check', 'Count'],
            [
                ['Staged alias rows', number_format($preflight['staged_total'])],
                ['Ready-product alias rows', number_format($preflight['ready_alias_rows'])],
                ['Review-product aliases left staged', number_format($preflight['review_alias_rows'])],
                ['Other non-ready aliases left staged', number_format($preflight['non_ready_alias_rows'])],
                ['Ready products represented', number_format($preflight['ready_products_with_aliases'])],
                ['Review products represented', number_format($preflight['review_products_with_aliases'])],
                ['Aliases already canonical', number_format($preflight['already_canonical'])],
                ['Aliases planned for creation', number_format($preflight['planned_create'])],
                ['Duplicate staged aliases skipped', number_format(count($preflight['duplicate_staging']))],
                ['Missing canonical products', number_format(count($preflight['missing_products']))],
                ['Canonical product conflicts', number_format(count($preflight['source_code_conflicts']))],
                ['Empty aliases', number_format(count($preflight['empty_aliases']))],
                ['Blocking conflicts', number_format($preflight['blocking_conflicts'])],
            ]
        );

        if (! empty($preflight['duplicate_staging'])) {
            $this->newLine();
            $this->comment('Duplicate aliases inside the same staged Product + Alias pair will be safely skipped.');

            $this->table(
                ['Product Code', 'Product', 'Alias'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['alias'],
                    ],
                    array_slice($preflight['duplicate_staging'], 0, 20)
                )
            );
        }

        if (! empty($preflight['missing_products'])) {
            $this->newLine();
            $this->error('Aliases whose Ready product is missing from the canonical Product Master:');

            $this->table(
                ['Product Code', 'Product', 'Alias'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['alias'],
                    ],
                    array_slice($preflight['missing_products'], 0, 30)
                )
            );
        }

        if (! empty($preflight['source_code_conflicts'])) {
            $this->newLine();
            $this->error('Aliases resolving to a non-eligible canonical product:');

            $this->table(
                ['Product Code', 'Product', 'Alias', 'Reason'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['alias'],
                        $row['reason'],
                    ],
                    array_slice($preflight['source_code_conflicts'], 0, 30)
                )
            );
        }

        if (! empty($preflight['empty_aliases'])) {
            $this->newLine();
            $this->error('Empty staged aliases:');

            $this->table(
                ['Product Code', 'Product'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                    ],
                    array_slice($preflight['empty_aliases'], 0, 30)
                )
            );
        }
    }

    private function applyPromotion(int $batchId, array $columns): array
    {
        $rows = $this->loadStagedAliasRows($batchId, $columns)
            ->where('product_staging_status', 'Ready')
            ->values();

        $productMap = DB::table('material_types')
            ->whereNotNull('catalogue_source_code')
            ->where('master_status', 'Approved')
            ->where('is_legacy', false)
            ->where('is_active', true)
            ->get([
                'id',
                'catalogue_source_code',
            ])
            ->keyBy(fn ($row) => $this->normalizeKey($row->catalogue_source_code));

        $existingAliases = DB::table('material_search_aliases')
            ->whereNull('material_variant_id')
            ->get(['id', 'material_type_id', 'normalized_alias'])
            ->groupBy('material_type_id');

        $created = 0;
        $existing = 0;
        $duplicateStaging = 0;
        $seen = [];
        $sortOrderByProduct = [];

        foreach ($rows as $row) {
            $sourceCode = trim((string) $row->source_product_code);
            $alias = trim((string) $row->search_alias);

            if ($alias === '') {
                throw new RuntimeException(
                    "Encountered empty alias during APPLY for Product Code [{$sourceCode}]."
                );
            }

            $product = $productMap->get($this->normalizeKey($sourceCode));

            if (! $product) {
                throw new RuntimeException(
                    "Could not resolve active Approved canonical Product [{$sourceCode}] during alias APPLY."
                );
            }

            $normalizedAlias = $this->normalizeAlias($alias);
            $dedupeKey = $product->id.'|'.$normalizedAlias;

            if (isset($seen[$dedupeKey])) {
                $duplicateStaging++;
                continue;
            }

            $seen[$dedupeKey] = true;

            $existingForProduct = $existingAliases->get($product->id, collect());

            if ($existingForProduct->contains(
                fn ($item) => $this->normalizeAlias((string) $item->normalized_alias) === $normalizedAlias
            )) {
                $existing++;
                continue;
            }

            if (! isset($sortOrderByProduct[$product->id])) {
                $currentMax = (int) DB::table('material_search_aliases')
                    ->where('material_type_id', $product->id)
                    ->whereNull('material_variant_id')
                    ->max('sort_order');

                $sortOrderByProduct[$product->id] = $currentMax > 0
                    ? (int) (ceil($currentMax / 10) * 10) + 10
                    : 10;
            }

            $remarks = $this->makeRemarks($row->alias_type, $row->source_status);

            DB::table('material_search_aliases')->insert([
                'material_type_id' => (int) $product->id,
                'material_variant_id' => null,
                'alias' => $alias,
                'normalized_alias' => $normalizedAlias,
                'sort_order' => $sortOrderByProduct[$product->id],
                'is_active' => $this->sourceStatusIsActive($row->source_status),
                'source' => self::SOURCE_LABEL,
                'remarks' => $remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sortOrderByProduct[$product->id] += 10;
            $created++;
        }

        $reviewLeftStaged = DB::table('material_catalog_search_alias_staging as a')
            ->join('material_catalog_product_staging as p', function ($join) use ($batchId, $columns) {
                $join->on(
                    'p.source_product_code',
                    '=',
                    'a.'.$columns['product_code']
                )
                ->where('p.import_batch_id', '=', $batchId);
            })
            ->where('a.import_batch_id', $batchId)
            ->where('p.staging_status', 'Review')
            ->count();

        $nonReadyLeftStaged = DB::table('material_catalog_search_alias_staging as a')
            ->join('material_catalog_product_staging as p', function ($join) use ($batchId, $columns) {
                $join->on(
                    'p.source_product_code',
                    '=',
                    'a.'.$columns['product_code']
                )
                ->where('p.import_batch_id', '=', $batchId);
            })
            ->where('a.import_batch_id', $batchId)
            ->whereNotIn('p.staging_status', ['Ready', 'Review'])
            ->count();

        return [
            'created' => $created,
            'existing' => $existing,
            'duplicate_staging' => $duplicateStaging,
            'review_left_staged' => $reviewLeftStaged,
            'non_ready_left_staged' => $nonReadyLeftStaged,
        ];
    }

    private function loadStagedAliasRows(int $batchId, array $columns): Collection
    {
        $select = [
            'a.id',
            'a.'.$columns['product_code'].' as source_product_code',
            'a.'.$columns['alias'].' as search_alias',
            'p.product_name',
            'p.staging_status as product_staging_status',
        ];

        if ($columns['alias_type']) {
            $select[] = 'a.'.$columns['alias_type'].' as alias_type';
        } else {
            $select[] = DB::raw('NULL as alias_type');
        }

        if ($columns['source_status']) {
            $select[] = 'a.'.$columns['source_status'].' as source_status';
        } else {
            $select[] = DB::raw('NULL as source_status');
        }

        $query = DB::table('material_catalog_search_alias_staging as a')
            ->join('material_catalog_product_staging as p', function ($join) use ($batchId, $columns) {
                $join->on(
                    'p.source_product_code',
                    '=',
                    'a.'.$columns['product_code']
                )
                ->where('p.import_batch_id', '=', $batchId);
            })
            ->where('a.import_batch_id', $batchId);

        if ($columns['staging_status']) {
            $query->where(function ($builder) use ($columns) {
                $builder
                    ->whereNull('a.'.$columns['staging_status'])
                    ->orWhereNotIn('a.'.$columns['staging_status'], ['Error', 'Excluded']);
            });
        }

        return $query
            ->orderBy('a.id')
            ->get($select);
    }

    private function sourceStatusIsActive(?string $status): bool
    {
        $normalized = $this->normalizeKey($status);

        if ($normalized === '') {
            return true;
        }

        return ! in_array($normalized, [
            'inactive',
            'disabled',
            'excluded',
            'error',
            'rejected',
        ], true);
    }

    private function makeRemarks(?string $aliasType, ?string $sourceStatus): ?string
    {
        $parts = [];

        if (filled($aliasType)) {
            $parts[] = 'Alias Type: '.trim((string) $aliasType);
        }

        if (filled($sourceStatus)) {
            $parts[] = 'Source Status: '.trim((string) $sourceStatus);
        }

        $parts[] = 'Promoted from validated catalogue search-alias staging.';

        return implode(' | ', $parts);
    }

    private function normalizeKey(?string $value): string
    {
        return Str::lower(trim((string) $value));
    }

    private function normalizeAlias(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = str_replace(
            ['–', '—', '−', '/', '\\', '&', '+'],
            ['-', '-', '-', ' ', ' ', ' and ', ' '],
            $value
        );

        return trim(preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? $value);
    }
}
