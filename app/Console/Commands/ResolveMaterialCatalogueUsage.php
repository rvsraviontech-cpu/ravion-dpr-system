<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ResolveMaterialCatalogueUsage extends Command
{
    protected $signature = 'materials:catalog-resolve-usage
                            {batch-code : Catalogue import batch code}
                            {--apply : Persist resolved usage-reference IDs back into staging. Without this option the command is dry-run only.}';

    protected $description = 'Resolve staged Product Usage Mapping rows against Ravion Activity Divisions, Activities and Construction Work Packages.';

    public function handle(): int
    {
        $batchCode = trim((string) $this->argument('batch-code'));
        $apply = (bool) $this->option('apply');

        $this->newLine();
        $this->info('Ravion Material Catalogue Usage Resolver');
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

            if (! in_array((string) $batch->status, ['Staged', 'Validated'], true)) {
                $this->error(
                    "Import batch [{$batchCode}] must have status [Staged] or [Validated]. "
                    ."Current status: [{$batch->status}]."
                );
                return self::FAILURE;
            }

            $report = $this->resolveBatch((int) $batch->id, $apply);

            $this->renderReport($report);

            if ($report['blocking_conflicts'] > 0) {
                $this->newLine();
                $this->error('Usage resolution found blocking conflicts.');
                $this->line($apply
                    ? 'Only safely resolvable rows were updated. Conflicting rows remain unresolved.'
                    : 'No staging rows were modified because this was a dry-run.'
                );

                return self::FAILURE;
            }

            $this->newLine();

            if ($apply) {
                $this->info('Usage resolution completed successfully.');
                $this->comment(
                    'Resolved IDs were written back to material_catalog_usage_mapping_staging. '
                    .'You can now re-run the Product Usage Mapping promotion dry-run.'
                );
            } else {
                $this->comment('Dry-run only. No staging rows were modified.');
                $this->line(
                    'When the report is acceptable, run: php artisan '
                    .$this->getName().' '.$batchCode.' --apply'
                );
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('Material Catalogue Usage resolution failed.');
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
            'activity_divisions',
            'activities',
            'construction_work_packages',
        ];

        foreach ($required as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("Required table [{$table}] does not exist.");
            }
        }

        $stagingColumns = Schema::getColumnListing('material_catalog_usage_mapping_staging');

        foreach ([
            'import_batch_id',
            'source_product_code',
            'division_code',
            'division_name',
            'usage_code',
            'usage_name',
            'usage_type',
            'is_primary',
            'staging_status',
            'activity_division_id',
            'activity_id',
            'construction_work_package_id',
        ] as $column) {
            if (! in_array($column, $stagingColumns, true)) {
                throw new RuntimeException(
                    "Required staging column [{$column}] does not exist."
                );
            }
        }
    }

    private function resolveBatch(int $batchId, bool $apply): array
    {
        $rows = DB::table('material_catalog_usage_mapping_staging as u')
            ->join('material_catalog_product_staging as p', function ($join) use ($batchId) {
                $join->on('p.source_product_code', '=', 'u.source_product_code')
                    ->where('p.import_batch_id', '=', $batchId);
            })
            ->where('u.import_batch_id', $batchId)
            ->where(function ($query) {
                $query
                    ->whereNull('u.staging_status')
                    ->orWhereNotIn('u.staging_status', ['Error', 'Excluded']);
            })
            ->orderBy('u.id')
            ->get([
                'u.id',
                'u.source_product_code',
                'u.division_code',
                'u.division_name',
                'u.usage_code',
                'u.usage_name',
                'u.usage_type',
                'u.is_primary',
                'u.activity_division_id',
                'u.activity_id',
                'u.construction_work_package_id',
                'p.product_name',
                'p.staging_status as product_staging_status',
            ]);

        $activityDivisions = DB::table('activity_divisions')
            ->where('is_active', true)
            ->get(['id', 'code', 'name'])
            ->keyBy(fn ($row) => $this->divisionPrefix($row->code));

        $activitiesByDivision = DB::table('activities')
            ->where('is_active', true)
            ->get(['id', 'activity_division_id', 'activity_name'])
            ->groupBy('activity_division_id');

        $workPackagesByDivision = DB::table('construction_work_packages')
            ->where('is_active', true)
            ->whereNotNull('activity_division_id')
            ->get(['id', 'parent_id', 'code', 'name', 'activity_division_id'])
            ->groupBy('activity_division_id');

        $stats = [
            'rows_total' => $rows->count(),
            'ready_rows' => 0,
            'review_rows' => 0,
            'other_rows' => 0,
            'division_resolved' => 0,
            'division_already_resolved' => 0,
            'division_unresolved' => 0,
            'activity_resolved' => 0,
            'activity_already_resolved' => 0,
            'activity_left_null' => 0,
            'work_package_resolved' => 0,
            'work_package_already_resolved' => 0,
            'work_package_left_null' => 0,
            'rows_updated' => 0,
            'division_conflicts' => [],
            'activity_conflicts' => [],
            'work_package_conflicts' => [],
            'blocking_conflicts' => 0,
        ];

        foreach ($rows as $row) {
            if ($row->product_staging_status === 'Ready') {
                $stats['ready_rows']++;
            } elseif ($row->product_staging_status === 'Review') {
                $stats['review_rows']++;
            } else {
                $stats['other_rows']++;
            }

            $updates = [];

            // 1) Resolve Activity Division by source division number -> Ravion code prefix.
            $division = null;

            if ($row->activity_division_id) {
                $division = DB::table('activity_divisions')
                    ->where('id', (int) $row->activity_division_id)
                    ->where('is_active', true)
                    ->first(['id', 'code', 'name']);

                if ($division) {
                    $stats['division_already_resolved']++;
                } else {
                    $stats['division_conflicts'][] = [
                        'product_code' => $row->source_product_code,
                        'product' => $row->product_name,
                        'division_code' => $row->division_code,
                        'usage_code' => $row->usage_code,
                        'reason' => 'Existing activity_division_id is invalid or inactive.',
                    ];
                    $stats['blocking_conflicts']++;
                    continue;
                }
            } else {
                $prefix = $this->normalizeDivisionCode($row->division_code);
                $division = $activityDivisions->get($prefix);

                if (! $division) {
                    $stats['division_unresolved']++;
                    $stats['division_conflicts'][] = [
                        'product_code' => $row->source_product_code,
                        'product' => $row->product_name,
                        'division_code' => $row->division_code,
                        'usage_code' => $row->usage_code,
                        'reason' => 'No active Ravion Activity Division matched source division.',
                    ];
                    $stats['blocking_conflicts']++;
                    continue;
                }

                $updates['activity_division_id'] = (int) $division->id;
                $stats['division_resolved']++;
            }

            // 2) Resolve Activity only when there is one exact normalized name match.
            if ($row->activity_id) {
                $activity = DB::table('activities')
                    ->where('id', (int) $row->activity_id)
                    ->where('is_active', true)
                    ->first(['id', 'activity_division_id', 'activity_name']);

                if (
                    ! $activity
                    || (int) $activity->activity_division_id !== (int) $division->id
                ) {
                    $stats['activity_conflicts'][] = [
                        'product_code' => $row->source_product_code,
                        'product' => $row->product_name,
                        'usage_code' => $row->usage_code,
                        'usage_name' => $row->usage_name,
                        'reason' => 'Existing activity_id is invalid, inactive or belongs to another division.',
                    ];
                    $stats['blocking_conflicts']++;
                    continue;
                }

                $stats['activity_already_resolved']++;
            } else {
                $activityMatches = collect($activitiesByDivision->get((int) $division->id, collect()))
                    ->filter(function ($activity) use ($row) {
                        return $this->normalizeText($activity->activity_name)
                            === $this->normalizeText($row->usage_name);
                    })
                    ->values();

                if ($activityMatches->count() === 1) {
                    $updates['activity_id'] = (int) $activityMatches->first()->id;
                    $stats['activity_resolved']++;
                } elseif ($activityMatches->count() > 1) {
                    $stats['activity_conflicts'][] = [
                        'product_code' => $row->source_product_code,
                        'product' => $row->product_name,
                        'usage_code' => $row->usage_code,
                        'usage_name' => $row->usage_name,
                        'reason' => 'Multiple exact Activity name matches found in resolved division.',
                    ];
                    $stats['blocking_conflicts']++;
                    continue;
                } else {
                    $stats['activity_left_null']++;
                }
            }

            // 3) Resolve Work Package only when there is one exact normalized name match.
            if ($row->construction_work_package_id) {
                $workPackage = DB::table('construction_work_packages')
                    ->where('id', (int) $row->construction_work_package_id)
                    ->where('is_active', true)
                    ->first(['id', 'activity_division_id', 'name']);

                if (
                    ! $workPackage
                    || (
                        $workPackage->activity_division_id !== null
                        && (int) $workPackage->activity_division_id !== (int) $division->id
                    )
                ) {
                    $stats['work_package_conflicts'][] = [
                        'product_code' => $row->source_product_code,
                        'product' => $row->product_name,
                        'usage_code' => $row->usage_code,
                        'usage_name' => $row->usage_name,
                        'reason' => 'Existing Work Package is invalid, inactive or belongs to another division.',
                    ];
                    $stats['blocking_conflicts']++;
                    continue;
                }

                $stats['work_package_already_resolved']++;
            } else {
                $workPackageMatches = collect($workPackagesByDivision->get((int) $division->id, collect()))
                    ->filter(function ($package) use ($row) {
                        return $this->normalizeText($package->name)
                            === $this->normalizeText($row->usage_name);
                    })
                    ->values();

                if ($workPackageMatches->count() === 1) {
                    $updates['construction_work_package_id'] = (int) $workPackageMatches->first()->id;
                    $stats['work_package_resolved']++;
                } elseif ($workPackageMatches->count() > 1) {
                    $stats['work_package_conflicts'][] = [
                        'product_code' => $row->source_product_code,
                        'product' => $row->product_name,
                        'usage_code' => $row->usage_code,
                        'usage_name' => $row->usage_name,
                        'reason' => 'Multiple exact Work Package name matches found in resolved division.',
                    ];
                    $stats['blocking_conflicts']++;
                    continue;
                } else {
                    $stats['work_package_left_null']++;
                }
            }

            if ($apply && $updates !== []) {
                $updates['updated_at'] = now();

                DB::table('material_catalog_usage_mapping_staging')
                    ->where('id', $row->id)
                    ->update($updates);

                $stats['rows_updated']++;
            } elseif (! $apply && $updates !== []) {
                $stats['rows_updated']++;
            }
        }

        return $stats;
    }

    private function renderReport(array $report): void
    {
        $this->table(
            ['Check', 'Count'],
            [
                ['Usage staging rows evaluated', number_format($report['rows_total'])],
                ['Ready-product rows', number_format($report['ready_rows'])],
                ['Review-product rows', number_format($report['review_rows'])],
                ['Other rows', number_format($report['other_rows'])],
                ['Activity Divisions resolved now', number_format($report['division_resolved'])],
                ['Activity Divisions already resolved', number_format($report['division_already_resolved'])],
                ['Activity Divisions unresolved', number_format($report['division_unresolved'])],
                ['Activities resolved by exact name', number_format($report['activity_resolved'])],
                ['Activities already resolved', number_format($report['activity_already_resolved'])],
                ['Activities safely left NULL', number_format($report['activity_left_null'])],
                ['Work Packages resolved by exact name', number_format($report['work_package_resolved'])],
                ['Work Packages already resolved', number_format($report['work_package_already_resolved'])],
                ['Work Packages safely left NULL', number_format($report['work_package_left_null'])],
                ['Rows that would be / were updated', number_format($report['rows_updated'])],
                ['Division conflicts', number_format(count($report['division_conflicts']))],
                ['Activity conflicts', number_format(count($report['activity_conflicts']))],
                ['Work Package conflicts', number_format(count($report['work_package_conflicts']))],
                ['Blocking conflicts', number_format($report['blocking_conflicts'])],
            ]
        );

        if ($report['division_conflicts'] !== []) {
            $this->newLine();
            $this->error('Division resolution conflicts:');
            $this->table(
                ['Product Code', 'Product', 'Division', 'Usage', 'Reason'],
                array_map(
                    fn ($row) => [
                        $row['product_code'],
                        $row['product'],
                        $row['division_code'],
                        $row['usage_code'],
                        $row['reason'],
                    ],
                    array_slice($report['division_conflicts'], 0, 30)
                )
            );
        }

        if ($report['activity_conflicts'] !== []) {
            $this->newLine();
            $this->error('Activity resolution conflicts:');
            $this->table(
                ['Product Code', 'Product', 'Usage', 'Usage Name', 'Reason'],
                array_map(
                    fn ($row) => [
                        $row['product_code'],
                        $row['product'],
                        $row['usage_code'],
                        $row['usage_name'],
                        $row['reason'],
                    ],
                    array_slice($report['activity_conflicts'], 0, 30)
                )
            );
        }

        if ($report['work_package_conflicts'] !== []) {
            $this->newLine();
            $this->error('Work Package resolution conflicts:');
            $this->table(
                ['Product Code', 'Product', 'Usage', 'Usage Name', 'Reason'],
                array_map(
                    fn ($row) => [
                        $row['product_code'],
                        $row['product'],
                        $row['usage_code'],
                        $row['usage_name'],
                        $row['reason'],
                    ],
                    array_slice($report['work_package_conflicts'], 0, 30)
                )
            );
        }
    }

    private function divisionPrefix(?string $code): string
    {
        $code = trim((string) $code);

        if (preg_match('/^(\d{2})-/', $code, $matches)) {
            return $matches[1];
        }

        return $this->normalizeDivisionCode($code);
    }

    private function normalizeDivisionCode(?string $code): string
    {
        $code = trim((string) $code);

        if ($code === '') {
            return '';
        }

        if (ctype_digit($code)) {
            return str_pad($code, 2, '0', STR_PAD_LEFT);
        }

        if (preg_match('/^(\d{1,2})/', $code, $matches)) {
            return str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    private function normalizeText(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/[&\/,+\-]+/u', ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]/u', '', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim((string) $value);
    }
}
