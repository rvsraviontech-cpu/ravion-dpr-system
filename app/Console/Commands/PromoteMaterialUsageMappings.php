<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PromoteMaterialUsageMappings extends Command
{
    protected $signature = 'materials:catalog-promote-usage
                            {batch-code : Validated catalogue import batch code}
                            {--apply : Persist the usage-mapping promotion. Without this option the command is dry-run only.}';

    protected $description = 'Promote Product Usage Mapping rows from validated catalogue staging into material_product_usage_mappings.';

    private const SOURCE_LABEL = 'Ravion Materials ERP Catalogue V1';

    public function handle(): int
    {
        $batchCode = trim((string) $this->argument('batch-code'));
        $apply = (bool) $this->option('apply');

        $this->newLine();
        $this->info('Ravion Material Usage Mapping Promotion');
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

            $stagingColumns = $this->resolveStagingColumns();
            $canonicalColumns = $this->resolveCanonicalColumns();

            $preflight = $this->buildPreflight(
                (int) $batch->id,
                $stagingColumns,
                $canonicalColumns
            );

            $this->renderPreflight($preflight);

            if ($preflight['blocking_conflicts'] > 0) {
                $this->newLine();
                $this->error('Usage-mapping promotion stopped because blocking conflicts were found.');
                $this->line('No canonical usage-mapping rows were modified.');
                return self::FAILURE;
            }

            if (! $apply) {
                $this->newLine();
                $this->comment('Dry-run only. No canonical usage-mapping rows were modified.');
                $this->line(
                    'When the report is acceptable, run: php artisan '
                    .$this->getName().' '.$batchCode.' --apply'
                );
                return self::SUCCESS;
            }

            $result = DB::transaction(function () use ($batch, $stagingColumns, $canonicalColumns) {
                return $this->applyPromotion(
                    (int) $batch->id,
                    $stagingColumns,
                    $canonicalColumns
                );
            }, 3);

            $this->newLine();
            $this->info('Product Usage Mapping promotion completed successfully.');

            $this->table(
                ['Result', 'Count'],
                [
                    ['Mappings created', number_format($result['created'])],
                    ['Mappings already canonical', number_format($result['existing'])],
                    ['Duplicate staged mappings skipped', number_format($result['duplicate_staging'])],
                    ['Review-product mappings left staged', number_format($result['review_left_staged'])],
                    ['Other non-ready mappings left staged', number_format($result['non_ready_left_staged'])],
                ]
            );

            $this->line(
                'Canonical usage mappings now present: '
                .number_format(DB::table('material_product_usage_mappings')->count())
            );

            $this->newLine();
            $this->comment(
                'Only mappings belonging to Ready catalogue products were promoted. '
                .'Review-product mappings remain staged for later approval.'
            );

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('Product Usage Mapping promotion failed.');
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function guardRequiredTables(): void
    {
        $required = [
            'material_catalog_import_batches',
            'material_catalog_product_staging',
            'material_catalog_usage_mapping_staging',
            'material_types',
            'material_product_usage_mappings',
        ];

        foreach ($required as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Required table [{$table}] does not exist.");
            }
        }
    }

    private function resolveStagingColumns(): array
    {
        $columns = Schema::getColumnListing('material_catalog_usage_mapping_staging');

        return [
            'product_code' => $this->requiredColumn($columns, 'source_product_code'),
            'division_code' => $this->requiredColumn($columns, 'division_code'),
            'division_name' => $this->requiredColumn($columns, 'division_name'),
            'usage_code' => $this->requiredColumn($columns, 'usage_code'),
            'usage_name' => $this->requiredColumn($columns, 'usage_name'),
            'usage_type' => $this->requiredColumn($columns, 'usage_type'),
            'is_primary' => $this->requiredColumn($columns, 'is_primary'),
            'source_status' => $this->optionalColumn($columns, 'source_status'),
            'staging_status' => $this->requiredColumn($columns, 'staging_status'),
            'review_reason' => $this->optionalColumn($columns, 'review_reason'),
            'remarks' => $this->optionalColumn($columns, 'remarks'),
            'material_type_id' => $this->optionalColumn($columns, 'material_type_id'),
            'activity_division_id' => $this->requiredColumn($columns, 'activity_division_id'),
            'activity_id' => $this->requiredColumn($columns, 'activity_id'),
            'construction_work_package_id' => $this->requiredColumn($columns, 'construction_work_package_id'),
            'canonical_mapping_id' => $this->optionalColumn($columns, 'material_product_usage_mapping_id'),
        ];
    }

    private function resolveCanonicalColumns(): array
    {
        $columns = Schema::getColumnListing('material_product_usage_mappings');

        return [
            'material_type_id' => $this->requiredColumn($columns, 'material_type_id'),
            'material_variant_id' => $this->optionalColumn($columns, 'material_variant_id'),
            'activity_division_id' => $this->requiredColumn($columns, 'activity_division_id'),
            'activity_id' => $this->requiredColumn($columns, 'activity_id'),
            'construction_work_package_id' => $this->requiredColumn($columns, 'construction_work_package_id'),
            'usage_type' => $this->requiredColumn($columns, 'usage_type'),
            'is_primary' => $this->requiredColumn($columns, 'is_primary'),
            'sort_order' => $this->requiredColumn($columns, 'sort_order'),
            'is_active' => $this->requiredColumn($columns, 'is_active'),
            'source_code' => $this->requiredColumn($columns, 'source_code'),
            'source_name' => $this->requiredColumn($columns, 'source_name'),
            'source' => $this->requiredColumn($columns, 'source'),
            'remarks' => $this->optionalColumn($columns, 'remarks'),
            'created_at' => $this->optionalColumn($columns, 'created_at'),
            'updated_at' => $this->optionalColumn($columns, 'updated_at'),
        ];
    }

    private function requiredColumn(array $available, string $column): string
    {
        if (! in_array($column, $available, true)) {
            throw new RuntimeException(
                "Required column [{$column}] was not found. Available columns: ".implode(', ', $available)
            );
        }

        return $column;
    }

    private function optionalColumn(array $available, string $column): ?string
    {
        return in_array($column, $available, true) ? $column : null;
    }

    private function buildPreflight(
        int $batchId,
        array $stagingColumns,
        array $canonicalColumns
    ): array {
        $rows = $this->loadStagedUsageRows($batchId, $stagingColumns);

        $readyRows = $rows->where('product_staging_status', 'Ready')->values();
        $reviewRows = $rows->where('product_staging_status', 'Review')->values();
        $nonReadyRows = $rows
            ->reject(fn ($row) => in_array($row->product_staging_status, ['Ready', 'Review'], true))
            ->values();

        $productMap = DB::table('material_types')
            ->whereNotNull('catalogue_source_code')
            ->get([
                'id',
                'material_type_name',
                'catalogue_source_code',
                'master_status',
                'is_legacy',
                'is_active',
            ])
            ->keyBy(fn ($row) => $this->normalizeKey($row->catalogue_source_code));

        $missingProducts = [];
        $productConflicts = [];
        $missingUsageIds = [];
        $orphanReferenceIds = [];
        $emptyUsageIdentity = [];
        $duplicateStaging = [];
        $planned = [];
        $seen = [];

        $divisionIds = $readyRows->pluck('activity_division_id')->filter()->unique()->values();
        $activityIds = $readyRows->pluck('activity_id')->filter()->unique()->values();
        $workPackageIds = $readyRows->pluck('construction_work_package_id')->filter()->unique()->values();

        $validDivisionIds = $this->existingIdsIfTableExists('activity_divisions', $divisionIds);
        $validActivityIds = $this->existingIdsIfTableExists('activities', $activityIds);
        $validWorkPackageIds = $this->existingIdsIfTableExists('construction_work_packages', $workPackageIds);

        foreach ($readyRows as $row) {
            $sourceCode = trim((string) $row->source_product_code);
            $product = $productMap->get($this->normalizeKey($sourceCode));

            if (! $product) {
                $missingProducts[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'usage_code' => $row->usage_code,
                    'usage_name' => $row->usage_name,
                ];
                continue;
            }

            if (
                (string) $product->master_status !== 'Approved'
                || (int) $product->is_legacy === 1
                || (int) $product->is_active !== 1
            ) {
                $productConflicts[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'usage_code' => $row->usage_code,
                    'usage_name' => $row->usage_name,
                    'reason' => 'Resolved canonical product is not an active Approved catalogue product.',
                ];
                continue;
            }

            if (
                blank($row->activity_division_id)
                && blank($row->activity_id)
                && blank($row->construction_work_package_id)
            ) {
                $missingUsageIds[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'division_code' => $row->division_code,
                    'usage_code' => $row->usage_code,
                    'usage_name' => $row->usage_name,
                ];
                continue;
            }

            $invalidRefs = [];

            if (
                filled($row->activity_division_id)
                && $validDivisionIds !== null
                && ! $validDivisionIds->contains((int) $row->activity_division_id)
            ) {
                $invalidRefs[] = 'activity_division_id='.$row->activity_division_id;
            }

            if (
                filled($row->activity_id)
                && $validActivityIds !== null
                && ! $validActivityIds->contains((int) $row->activity_id)
            ) {
                $invalidRefs[] = 'activity_id='.$row->activity_id;
            }

            if (
                filled($row->construction_work_package_id)
                && $validWorkPackageIds !== null
                && ! $validWorkPackageIds->contains((int) $row->construction_work_package_id)
            ) {
                $invalidRefs[] = 'construction_work_package_id='.$row->construction_work_package_id;
            }

            if ($invalidRefs !== []) {
                $orphanReferenceIds[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'usage_code' => $row->usage_code,
                    'invalid_refs' => implode(', ', $invalidRefs),
                ];
                continue;
            }

            if (
                $this->normalizeKey($row->usage_code) === ''
                || $this->normalizeKey($row->usage_name) === ''
                || $this->normalizeKey($row->usage_type) === ''
            ) {
                $emptyUsageIdentity[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'usage_code' => $row->usage_code,
                    'usage_name' => $row->usage_name,
                    'usage_type' => $row->usage_type,
                ];
                continue;
            }

            $identity = $this->mappingIdentity(
                (int) $product->id,
                $row->activity_division_id,
                $row->activity_id,
                $row->construction_work_package_id,
                $row->usage_type,
                $row->usage_code
            );

            if (isset($seen[$identity])) {
                $duplicateStaging[] = [
                    'source_product_code' => $sourceCode,
                    'product_name' => $row->product_name,
                    'usage_code' => $row->usage_code,
                    'usage_name' => $row->usage_name,
                    'usage_type' => $row->usage_type,
                ];
                continue;
            }

            $seen[$identity] = true;

            $alreadyExists = $this->canonicalMappingExists(
                (int) $product->id,
                $row,
                $canonicalColumns
            );

            $planned[] = [
                'material_type_id' => (int) $product->id,
                'source_product_code' => $sourceCode,
                'product_name' => (string) $product->material_type_name,
                'usage_code' => $row->usage_code,
                'usage_name' => $row->usage_name,
                'usage_type' => $row->usage_type,
                'already_exists' => $alreadyExists,
            ];
        }

        $plannedCollection = collect($planned);

        return [
            'staged_total' => $rows->count(),
            'ready_mapping_rows' => $readyRows->count(),
            'review_mapping_rows' => $reviewRows->count(),
            'non_ready_mapping_rows' => $nonReadyRows->count(),
            'ready_products_represented' => $readyRows->pluck('source_product_code')->unique()->count(),
            'review_products_represented' => $reviewRows->pluck('source_product_code')->unique()->count(),
            'ready_divisions' => $readyRows->pluck('division_code')->filter()->unique()->count(),
            'ready_usage_codes' => $readyRows->pluck('usage_code')->filter()->unique()->count(),
            'ready_usage_names' => $readyRows->pluck('usage_name')->filter()->unique()->count(),
            'planned_create' => $plannedCollection->where('already_exists', false)->count(),
            'already_canonical' => $plannedCollection->where('already_exists', true)->count(),
            'duplicate_staging' => $duplicateStaging,
            'missing_products' => $missingProducts,
            'product_conflicts' => $productConflicts,
            'missing_usage_ids' => $missingUsageIds,
            'orphan_reference_ids' => $orphanReferenceIds,
            'empty_usage_identity' => $emptyUsageIdentity,
            'blocking_conflicts' => count($missingProducts)
                + count($productConflicts)
                + count($missingUsageIds)
                + count($orphanReferenceIds)
                + count($emptyUsageIdentity),
        ];
    }

    private function renderPreflight(array $preflight): void
    {
        $this->table(
            ['Check', 'Count'],
            [
                ['Staged usage-mapping rows', number_format($preflight['staged_total'])],
                ['Ready-product mapping rows', number_format($preflight['ready_mapping_rows'])],
                ['Review-product mappings left staged', number_format($preflight['review_mapping_rows'])],
                ['Other non-ready mappings left staged', number_format($preflight['non_ready_mapping_rows'])],
                ['Ready products represented', number_format($preflight['ready_products_represented'])],
                ['Review products represented', number_format($preflight['review_products_represented'])],
                ['Ready-set divisions represented', number_format($preflight['ready_divisions'])],
                ['Ready-set unique usage codes', number_format($preflight['ready_usage_codes'])],
                ['Ready-set unique usage names', number_format($preflight['ready_usage_names'])],
                ['Mappings already canonical', number_format($preflight['already_canonical'])],
                ['Mappings planned for creation', number_format($preflight['planned_create'])],
                ['Duplicate staged mappings skipped', number_format(count($preflight['duplicate_staging']))],
                ['Missing canonical products', number_format(count($preflight['missing_products']))],
                ['Canonical product conflicts', number_format(count($preflight['product_conflicts']))],
                ['Mappings missing all usage IDs', number_format(count($preflight['missing_usage_ids']))],
                ['Orphan usage reference IDs', number_format(count($preflight['orphan_reference_ids']))],
                ['Incomplete usage identities', number_format(count($preflight['empty_usage_identity']))],
                ['Blocking conflicts', number_format($preflight['blocking_conflicts'])],
            ]
        );

        if ($preflight['duplicate_staging'] !== []) {
            $this->newLine();
            $this->comment('Duplicate staged usage mappings will be safely skipped.');
            $this->table(
                ['Product Code', 'Product', 'Usage Code', 'Usage Name', 'Type'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['usage_code'],
                        $row['usage_name'],
                        $row['usage_type'],
                    ],
                    array_slice($preflight['duplicate_staging'], 0, 20)
                )
            );
        }

        if ($preflight['missing_products'] !== []) {
            $this->newLine();
            $this->error('Ready mappings whose canonical Product could not be resolved:');
            $this->table(
                ['Product Code', 'Product', 'Usage Code', 'Usage Name'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['usage_code'],
                        $row['usage_name'],
                    ],
                    array_slice($preflight['missing_products'], 0, 30)
                )
            );
        }

        if ($preflight['product_conflicts'] !== []) {
            $this->newLine();
            $this->error('Mappings resolving to non-eligible canonical Products:');
            $this->table(
                ['Product Code', 'Product', 'Usage Code', 'Usage Name', 'Reason'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['usage_code'],
                        $row['usage_name'],
                        $row['reason'],
                    ],
                    array_slice($preflight['product_conflicts'], 0, 30)
                )
            );
        }

        if ($preflight['missing_usage_ids'] !== []) {
            $this->newLine();
            $this->error('Mappings with no resolved Activity Division / Activity / Work Package IDs:');
            $this->table(
                ['Product Code', 'Product', 'Division', 'Usage Code', 'Usage Name'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['division_code'],
                        $row['usage_code'],
                        $row['usage_name'],
                    ],
                    array_slice($preflight['missing_usage_ids'], 0, 30)
                )
            );
        }

        if ($preflight['orphan_reference_ids'] !== []) {
            $this->newLine();
            $this->error('Mappings containing orphan usage-reference IDs:');
            $this->table(
                ['Product Code', 'Product', 'Usage Code', 'Invalid References'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['usage_code'],
                        $row['invalid_refs'],
                    ],
                    array_slice($preflight['orphan_reference_ids'], 0, 30)
                )
            );
        }

        if ($preflight['empty_usage_identity'] !== []) {
            $this->newLine();
            $this->error('Mappings with incomplete source usage identity:');
            $this->table(
                ['Product Code', 'Product', 'Usage Code', 'Usage Name', 'Type'],
                array_map(
                    fn ($row) => [
                        $row['source_product_code'],
                        $row['product_name'],
                        $row['usage_code'],
                        $row['usage_name'],
                        $row['usage_type'],
                    ],
                    array_slice($preflight['empty_usage_identity'], 0, 30)
                )
            );
        }
    }

    private function applyPromotion(
        int $batchId,
        array $stagingColumns,
        array $canonicalColumns
    ): array {
        $rows = $this->loadStagedUsageRows($batchId, $stagingColumns)
            ->where('product_staging_status', 'Ready')
            ->values();

        $productMap = DB::table('material_types')
            ->whereNotNull('catalogue_source_code')
            ->where('master_status', 'Approved')
            ->where('is_legacy', false)
            ->where('is_active', true)
            ->get(['id', 'catalogue_source_code'])
            ->keyBy(fn ($row) => $this->normalizeKey($row->catalogue_source_code));

        $created = 0;
        $existing = 0;
        $duplicateStaging = 0;
        $seen = [];
        $sortOrderByProduct = [];

        foreach ($rows as $row) {
            $sourceCode = trim((string) $row->source_product_code);
            $product = $productMap->get($this->normalizeKey($sourceCode));

            if (! $product) {
                throw new RuntimeException(
                    "Could not resolve active Approved canonical Product [{$sourceCode}] during usage APPLY."
                );
            }

            if (
                blank($row->activity_division_id)
                && blank($row->activity_id)
                && blank($row->construction_work_package_id)
            ) {
                throw new RuntimeException(
                    "No resolved usage IDs found during APPLY for Product [{$sourceCode}], Usage [{$row->usage_code}]."
                );
            }

            if (
                $this->normalizeKey($row->usage_code) === ''
                || $this->normalizeKey($row->usage_name) === ''
                || $this->normalizeKey($row->usage_type) === ''
            ) {
                throw new RuntimeException(
                    "Incomplete usage identity encountered during APPLY for Product [{$sourceCode}]."
                );
            }

            $identity = $this->mappingIdentity(
                (int) $product->id,
                $row->activity_division_id,
                $row->activity_id,
                $row->construction_work_package_id,
                $row->usage_type,
                $row->usage_code
            );

            if (isset($seen[$identity])) {
                $duplicateStaging++;
                continue;
            }

            $seen[$identity] = true;

            if ($this->canonicalMappingExists((int) $product->id, $row, $canonicalColumns)) {
                $existing++;
                continue;
            }

            if (! isset($sortOrderByProduct[$product->id])) {
                $currentMax = (int) DB::table('material_product_usage_mappings')
                    ->where('material_type_id', $product->id)
                    ->max('sort_order');

                $sortOrderByProduct[$product->id] = $currentMax > 0
                    ? (int) (ceil($currentMax / 10) * 10) + 10
                    : 10;
            }

            $insert = [
                'material_type_id' => (int) $product->id,
                'activity_division_id' => $this->nullableInt($row->activity_division_id),
                'activity_id' => $this->nullableInt($row->activity_id),
                'construction_work_package_id' => $this->nullableInt($row->construction_work_package_id),
                'usage_type' => trim((string) $row->usage_type),
                'is_primary' => (bool) $row->staged_is_primary,
                'sort_order' => $sortOrderByProduct[$product->id],
                'is_active' => true,
                'source_code' => trim((string) $row->usage_code),
                'source_name' => trim((string) $row->usage_name),
                'source' => self::SOURCE_LABEL,
            ];

            if ($canonicalColumns['material_variant_id']) {
                $insert['material_variant_id'] = null;
            }

            if ($canonicalColumns['remarks']) {
                $parts = ['Promoted from validated Product Usage Mapping staging.'];

                if (filled($row->division_code)) {
                    $parts[] = 'Division: '.trim((string) $row->division_code)
                        .(filled($row->division_name) ? ' - '.trim((string) $row->division_name) : '');
                }

                if (filled($row->source_status)) {
                    $parts[] = 'Source Status: '.trim((string) $row->source_status);
                }

                if (filled($row->review_reason)) {
                    $parts[] = 'Review Reason: '.trim((string) $row->review_reason);
                }

                if (filled($row->staging_remarks)) {
                    $parts[] = trim((string) $row->staging_remarks);
                }

                $insert['remarks'] = implode(' | ', $parts);
            }

            if ($canonicalColumns['created_at']) {
                $insert['created_at'] = now();
            }

            if ($canonicalColumns['updated_at']) {
                $insert['updated_at'] = now();
            }

            DB::table('material_product_usage_mappings')->insert($insert);

            $sortOrderByProduct[$product->id] += 10;
            $created++;
        }

        return [
            'created' => $created,
            'existing' => $existing,
            'duplicate_staging' => $duplicateStaging,
            'review_left_staged' => $this->countMappingsForStatus($batchId, $stagingColumns, ['Review']),
            'non_ready_left_staged' => $this->countMappingsNotInStatuses($batchId, $stagingColumns, ['Ready', 'Review']),
        ];
    }

    private function loadStagedUsageRows(int $batchId, array $columns): Collection
    {
        $select = [
            'u.id',
            'u.source_product_code',
            'u.division_code',
            'u.division_name',
            'u.usage_code',
            'u.usage_name',
            'u.usage_type',
            'u.is_primary as staged_is_primary',
            'u.activity_division_id',
            'u.activity_id',
            'u.construction_work_package_id',
            'p.product_name',
            'p.staging_status as product_staging_status',
        ];

        if ($columns['source_status']) {
            $select[] = 'u.source_status';
        } else {
            $select[] = DB::raw('NULL as source_status');
        }

        if ($columns['review_reason']) {
            $select[] = 'u.review_reason';
        } else {
            $select[] = DB::raw('NULL as review_reason');
        }

        if ($columns['remarks']) {
            $select[] = 'u.remarks as staging_remarks';
        } else {
            $select[] = DB::raw('NULL as staging_remarks');
        }

        if ($columns['canonical_mapping_id']) {
            $select[] = 'u.material_product_usage_mapping_id as staged_canonical_mapping_id';
        } else {
            $select[] = DB::raw('NULL as staged_canonical_mapping_id');
        }

        return DB::table('material_catalog_usage_mapping_staging as u')
            ->join('material_catalog_product_staging as p', function ($join) use ($batchId) {
                $join->on('p.source_product_code', '=', 'u.source_product_code')
                    ->where('p.import_batch_id', '=', $batchId);
            })
            ->where('u.import_batch_id', $batchId)
            ->where(function ($builder) {
                $builder
                    ->whereNull('u.staging_status')
                    ->orWhereNotIn('u.staging_status', ['Error', 'Excluded']);
            })
            ->orderBy('u.id')
            ->get($select);
    }

    private function canonicalMappingExists(
        int $materialTypeId,
        object $row,
        array $columns
    ): bool {
        $query = DB::table('material_product_usage_mappings')
            ->where('material_type_id', $materialTypeId)
            ->where('usage_type', trim((string) $row->usage_type))
            ->where('source_code', trim((string) $row->usage_code));

        $this->whereNullableInt($query, 'activity_division_id', $row->activity_division_id);
        $this->whereNullableInt($query, 'activity_id', $row->activity_id);
        $this->whereNullableInt($query, 'construction_work_package_id', $row->construction_work_package_id);

        if ($columns['material_variant_id']) {
            $query->whereNull('material_variant_id');
        }

        return $query->exists();
    }

    private function whereNullableInt($query, string $column, $value): void
    {
        if (blank($value)) {
            $query->whereNull($column);
        } else {
            $query->where($column, (int) $value);
        }
    }

    private function existingIdsIfTableExists(string $table, Collection $ids): ?Collection
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::table($table)
            ->whereIn('id', $ids->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id);
    }

    private function countMappingsForStatus(int $batchId, array $columns, array $statuses): int
    {
        return DB::table('material_catalog_usage_mapping_staging as u')
            ->join('material_catalog_product_staging as p', function ($join) use ($batchId) {
                $join->on('p.source_product_code', '=', 'u.source_product_code')
                    ->where('p.import_batch_id', '=', $batchId);
            })
            ->where('u.import_batch_id', $batchId)
            ->whereIn('p.staging_status', $statuses)
            ->where(function ($builder) {
                $builder
                    ->whereNull('u.staging_status')
                    ->orWhereNotIn('u.staging_status', ['Error', 'Excluded']);
            })
            ->count();
    }

    private function countMappingsNotInStatuses(int $batchId, array $columns, array $statuses): int
    {
        return DB::table('material_catalog_usage_mapping_staging as u')
            ->join('material_catalog_product_staging as p', function ($join) use ($batchId) {
                $join->on('p.source_product_code', '=', 'u.source_product_code')
                    ->where('p.import_batch_id', '=', $batchId);
            })
            ->where('u.import_batch_id', $batchId)
            ->whereNotIn('p.staging_status', $statuses)
            ->where(function ($builder) {
                $builder
                    ->whereNull('u.staging_status')
                    ->orWhereNotIn('u.staging_status', ['Error', 'Excluded']);
            })
            ->count();
    }

    private function mappingIdentity(
        int $materialTypeId,
        $activityDivisionId,
        $activityId,
        $workPackageId,
        ?string $usageType,
        ?string $sourceCode
    ): string {
        return implode('|', [
            $materialTypeId,
            $this->nullableIdentityInt($activityDivisionId),
            $this->nullableIdentityInt($activityId),
            $this->nullableIdentityInt($workPackageId),
            $this->normalizeKey($usageType),
            $this->normalizeKey($sourceCode),
        ]);
    }

    private function nullableIdentityInt($value): string
    {
        return blank($value) ? 'NULL' : (string) ((int) $value);
    }

    private function nullableInt($value): ?int
    {
        return blank($value) ? null : (int) $value;
    }

    private function normalizeKey(?string $value): string
    {
        return Str::lower(trim((string) $value));
    }
}
