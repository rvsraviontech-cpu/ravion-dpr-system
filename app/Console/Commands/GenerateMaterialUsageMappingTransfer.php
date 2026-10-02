<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class GenerateMaterialUsageMappingTransfer extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'materials:generate-usage-mapping-transfer';

    /**
     * The console command description.
     */
    protected $description = 'Generate production-safe SQL for Construction Work Packages and Product Usage Mappings';

    /**
     * Expected catalogue counts established during the Product Master migration.
     */
    private const EXPECTED_PRODUCTION_PRODUCTS = 12651;
    private const EXPECTED_WORK_PACKAGES = 180;
    private const EXPECTED_USAGE_MAPPINGS = 9513;
    private const EXPECTED_ACTIVITY_DIVISIONS = 25;
    private const EXPECTED_ACTIVITIES = 533;

    /**
     * Output directory.
     */
    private string $outputDirectory;

    /**
     * Fresh production Product Master snapshot.
     */
    private string $productionProductSnapshot;

    /**
     * @var array<int, array{id:int, code:string, name:string}>
     */
    private array $productionProductsByCode = [];

    /**
     * @var array<int, string>
     */
    private array $localDivisionCodes = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->outputDirectory = storage_path('app/product-master-transfer');

        $this->productionProductSnapshot = $this->outputDirectory
            . DIRECTORY_SEPARATOR
            . 'production-material-products-final.txt';

        try {
            $this->newLine();
            $this->info('Ravion Materials ERP');
            $this->info('Construction Work Package + Product Usage Mapping Transfer Generator');
            $this->line(str_repeat('-', 72));

            $this->validateRequiredFiles();
            $this->loadProductionProductSnapshot();
            $this->validateLocalMasterCounts();

            $workPackages = $this->loadAndValidateWorkPackages();
            $usageMappings = $this->loadAndValidateUsageMappings();

            $sql = $this->buildTransferSql($workPackages, $usageMappings);

            $sqlPath = $this->outputDirectory
                . DIRECTORY_SEPARATOR
                . 'material-usage-mapping-transfer.sql';

            File::put($sqlPath, $sql);

            $reportPath = $this->outputDirectory
                . DIRECTORY_SEPARATOR
                . 'material-usage-mapping-transfer-report.txt';

            File::put(
                $reportPath,
                $this->buildReport($workPackages, $usageMappings)
            );

            $this->newLine();
            $this->info('TRANSFER PACKAGE GENERATED SUCCESSFULLY');
            $this->line(str_repeat('-', 72));

            $this->table(
                ['Check', 'Result'],
                [
                    ['Production products', self::EXPECTED_PRODUCTION_PRODUCTS],
['Unique non-empty product codes', count($this->productionProductsByCode)],
                    ['Local activity divisions', DB::table('activity_divisions')->count()],
                    ['Local activities', DB::table('activities')->count()],
                    ['Construction work packages', count($workPackages)],
                    ['Product usage mappings', count($usageMappings)],
                    [
                        'Mappings with Activity',
                        collect($usageMappings)
                            ->whereNotNull('activity_id')
                            ->count(),
                    ],
                    [
                        'Mappings with Work Package',
                        collect($usageMappings)
                            ->whereNotNull('construction_work_package_id')
                            ->count(),
                    ],
                    [
                        'Mappings with Variant',
                        collect($usageMappings)
                            ->whereNotNull('material_variant_id')
                            ->count(),
                    ],
                ]
            );

            $this->newLine();
            $this->line('SQL:');
            $this->line($sqlPath);

            $this->newLine();
            $this->line('Report:');
            $this->line($reportPath);

            $this->newLine();
            $this->warn('Nothing has been changed in the local or production database.');
            $this->warn('Do NOT execute the SQL on production yet.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('TRANSFER GENERATION STOPPED');
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn('No production database changes were made.');

            return self::FAILURE;
        }
    }

    /**
     * Validate files required by this generator.
     */
    private function validateRequiredFiles(): void
    {
        if (! File::isDirectory($this->outputDirectory)) {
            File::makeDirectory($this->outputDirectory, 0755, true);
        }

        if (! File::exists($this->productionProductSnapshot)) {
            throw new RuntimeException(
                'Missing production product snapshot: '
                . $this->productionProductSnapshot
            );
        }
    }

    /**
     * Load the fresh production Product Master snapshot.
     *
     * Format:
     * production_id|material_type_code|material_type_name
     */
    private function loadProductionProductSnapshot(): void
    {
        $lines = preg_split(
            '/\r\n|\r|\n/',
            trim(File::get($this->productionProductSnapshot))
        );

        $rowCount = 0;
        $duplicateCodes = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts = explode('|', $line, 3);

            if (count($parts) !== 3) {
                throw new RuntimeException(
                    'Invalid production product snapshot row: ' . $line
                );
            }

            [$id, $code, $name] = $parts;

            $id = (int) trim($id);
            $code = trim($code);
            $name = trim($name);

            $rowCount++;

            /*
             * Some historical production products have blank or duplicate
             * legacy codes. Those are allowed to remain in production.
             *
             * For this transfer we only care about the codes referenced by
             * the finalized local usage mappings.
             */
            if ($code === '') {
                continue;
            }

            if (isset($this->productionProductsByCode[$code])) {
                $duplicateCodes[$code] = true;

                /*
                 * Do not silently overwrite the first product.
                 * We will fail later only if a usage mapping actually tries
                 * to use a duplicated production code.
                 */
                continue;
            }

            $this->productionProductsByCode[$code] = [
                'id' => $id,
                'code' => $code,
                'name' => $name,
            ];
        }

        if ($rowCount !== self::EXPECTED_PRODUCTION_PRODUCTS) {
            throw new RuntimeException(
                'Production product snapshot count mismatch. Expected '
                . self::EXPECTED_PRODUCTION_PRODUCTS
                . ', found '
                . $rowCount
                . '.'
            );
        }

        /*
         * Store duplicate production codes separately so referenced
         * products can be rejected safely.
         */
        foreach (array_keys($duplicateCodes) as $code) {
            $this->productionProductsByCode[$code]['duplicate'] = true;
        }

        $this->info(
            'Production Product Master snapshot validated: '
            . number_format($rowCount)
            . ' products.'
        );
    }

    /**
     * Validate basic local master counts.
     */
    private function validateLocalMasterCounts(): void
    {
        $divisionCount = DB::table('activity_divisions')->count();
        $activityCount = DB::table('activities')->count();

        if ($divisionCount !== self::EXPECTED_ACTIVITY_DIVISIONS) {
            throw new RuntimeException(
                'Local Activity Division count mismatch. Expected '
                . self::EXPECTED_ACTIVITY_DIVISIONS
                . ', found '
                . $divisionCount
                . '.'
            );
        }

        if ($activityCount !== self::EXPECTED_ACTIVITIES) {
            throw new RuntimeException(
                'Local Activity count mismatch. Expected '
                . self::EXPECTED_ACTIVITIES
                . ', found '
                . $activityCount
                . '.'
            );
        }

        $divisions = DB::table('activity_divisions')
            ->select('id', 'code', 'name')
            ->orderBy('id')
            ->get();

        $seenCodes = [];

        foreach ($divisions as $division) {
            $code = trim((string) $division->code);

            if ($code === '') {
                throw new RuntimeException(
                    'Activity Division ID '
                    . $division->id
                    . ' has no code.'
                );
            }

            $normalizedCode = mb_strtolower($code, 'UTF-8');

            if (isset($seenCodes[$normalizedCode])) {
                throw new RuntimeException(
                    'Duplicate Activity Division code found locally: '
                    . $code
                );
            }

            $seenCodes[$normalizedCode] = true;
            $this->localDivisionCodes[(int) $division->id] = $code;
        }

        /*
         * Activities do not have their own code.
         * Their stable identity is:
         *
         * Activity Division code + Activity name.
         */
        $activities = DB::table('activities')
            ->select('id', 'activity_division_id', 'activity_name')
            ->orderBy('id')
            ->get();

        $seenActivityKeys = [];

        foreach ($activities as $activity) {
            $divisionId = (int) $activity->activity_division_id;

            if (! isset($this->localDivisionCodes[$divisionId])) {
                throw new RuntimeException(
                    'Activity ID '
                    . $activity->id
                    . ' references missing Activity Division ID '
                    . $divisionId
                    . '.'
                );
            }

            $activityName = trim((string) $activity->activity_name);

            if ($activityName === '') {
                throw new RuntimeException(
                    'Activity ID '
                    . $activity->id
                    . ' has no activity name.'
                );
            }

            $key = mb_strtolower(
                $this->localDivisionCodes[$divisionId]
                . '|'
                . $activityName,
                'UTF-8'
            );

            if (isset($seenActivityKeys[$key])) {
                throw new RuntimeException(
                    'Duplicate Activity identity found locally: '
                    . $this->localDivisionCodes[$divisionId]
                    . ' / '
                    . $activityName
                );
            }

            $seenActivityKeys[$key] = true;
        }

        $this->info(
            'Local Activity masters validated: '
            . number_format($divisionCount)
            . ' divisions / '
            . number_format($activityCount)
            . ' activities.'
        );
    }

    /**
     * Load and validate Construction Work Packages.
     *
     * Production currently has zero packages, so we preserve the local
     * package IDs. This safely preserves parent_id and allows the 14
     * usage mappings referencing packages to retain their relationships.
     */
    private function loadAndValidateWorkPackages(): array
    {
        $rows = DB::table('construction_work_packages')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        if (count($rows) !== self::EXPECTED_WORK_PACKAGES) {
            throw new RuntimeException(
                'Construction Work Package count mismatch. Expected '
                . self::EXPECTED_WORK_PACKAGES
                . ', found '
                . count($rows)
                . '.'
            );
        }

        $byId = [];
        $seenCodes = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $code = trim((string) ($row['code'] ?? ''));

            if ($code === '') {
                throw new RuntimeException(
                    'Construction Work Package ID '
                    . $id
                    . ' has no code.'
                );
            }

            $normalizedCode = mb_strtolower($code, 'UTF-8');

            if (isset($seenCodes[$normalizedCode])) {
                throw new RuntimeException(
                    'Duplicate Construction Work Package code: '
                    . $code
                );
            }

            $seenCodes[$normalizedCode] = true;
            $byId[$id] = $row;

            if ($row['activity_division_id'] !== null) {
    $divisionId = (int) $row['activity_division_id'];

    if (! isset($this->localDivisionCodes[$divisionId])) {
        throw new RuntimeException(
            'Construction Work Package '
            . $code
            . ' references missing Activity Division ID '
            . $divisionId
            . '.'
        );
    }
}
        }

        foreach ($rows as $row) {
            if ($row['parent_id'] === null) {
                continue;
            }

            $parentId = (int) $row['parent_id'];

            if (! isset($byId[$parentId])) {
                throw new RuntimeException(
                    'Construction Work Package '
                    . $row['code']
                    . ' references missing parent ID '
                    . $parentId
                    . '.'
                );
            }
        }

        $rootCount = collect($rows)
            ->whereNull('parent_id')
            ->count();

        $childCount = count($rows) - $rootCount;

        if ($rootCount !== 23 || $childCount !== 157) {
            throw new RuntimeException(
                'Unexpected Work Package hierarchy. Expected 23 roots + '
                . '157 children; found '
                . $rootCount
                . ' roots + '
                . $childCount
                . ' children.'
            );
        }

        $this->info(
            'Construction Work Packages validated: 180 packages '
            . '(23 roots / 157 children).'
        );

        return $rows;
    }

    /**
     * Load and validate Product Usage Mappings.
     */
    private function loadAndValidateUsageMappings(): array
    {
        $rows = DB::table('material_product_usage_mappings')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        if (count($rows) !== self::EXPECTED_USAGE_MAPPINGS) {
            throw new RuntimeException(
                'Product Usage Mapping count mismatch. Expected '
                . self::EXPECTED_USAGE_MAPPINGS
                . ', found '
                . count($rows)
                . '.'
            );
        }

        $workPackageIds = DB::table('construction_work_packages')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->all();

        $activities = DB::table('activities')
            ->select('id', 'activity_division_id', 'activity_name')
            ->get()
            ->keyBy('id');

        $localProducts = DB::table('material_types')
            ->select(
                'id',
                'material_type_code',
                'material_type_name'
            )
            ->get()
            ->keyBy('id');

        $variantCount = 0;
        $activityCount = 0;
        $packageCount = 0;

        foreach ($rows as &$row) {
            $mappingId = (int) $row['id'];
            $localProductId = (int) $row['material_type_id'];

            if (! $localProducts->has($localProductId)) {
                throw new RuntimeException(
                    'Usage Mapping ID '
                    . $mappingId
                    . ' references missing local Product ID '
                    . $localProductId
                    . '.'
                );
            }

            $localProduct = $localProducts->get($localProductId);

            $productCode = trim(
                (string) $localProduct->material_type_code
            );

            if ($productCode === '') {
                throw new RuntimeException(
                    'Usage Mapping ID '
                    . $mappingId
                    . ' references Product ID '
                    . $localProductId
                    . ' without a canonical product code.'
                );
            }

            if (! isset($this->productionProductsByCode[$productCode])) {
                throw new RuntimeException(
                    'Usage Mapping ID '
                    . $mappingId
                    . ' references product '
                    . $productCode
                    . ' which was not found in the fresh production '
                    . 'Product Master snapshot.'
                );
            }

            if (
                isset(
                    $this->productionProductsByCode[$productCode]['duplicate']
                )
            ) {
                throw new RuntimeException(
                    'Usage Mapping ID '
                    . $mappingId
                    . ' references duplicated production product code '
                    . $productCode
                    . '. Automatic mapping is unsafe.'
                );
            }

            $row['_product_code'] = $productCode;
            $row['_production_product_id'] =
                $this->productionProductsByCode[$productCode]['id'];

            $divisionId = (int) $row['activity_division_id'];

            if (! isset($this->localDivisionCodes[$divisionId])) {
                throw new RuntimeException(
                    'Usage Mapping ID '
                    . $mappingId
                    . ' references missing Activity Division ID '
                    . $divisionId
                    . '.'
                );
            }

            $row['_division_code'] =
                $this->localDivisionCodes[$divisionId];

            if ($row['material_variant_id'] !== null) {
                $variantCount++;
            }

            if ($row['activity_id'] !== null) {
                $activityCount++;

                $activityId = (int) $row['activity_id'];

                if (! $activities->has($activityId)) {
                    throw new RuntimeException(
                        'Usage Mapping ID '
                        . $mappingId
                        . ' references missing Activity ID '
                        . $activityId
                        . '.'
                    );
                }

                $activity = $activities->get($activityId);

                if (
                    (int) $activity->activity_division_id
                    !== $divisionId
                ) {
                    throw new RuntimeException(
                        'Usage Mapping ID '
                        . $mappingId
                        . ' has an Activity belonging to a different '
                        . 'Activity Division.'
                    );
                }

                $row['_activity_name'] =
                    trim((string) $activity->activity_name);
            } else {
                $row['_activity_name'] = null;
            }

            if ($row['construction_work_package_id'] !== null) {
                $packageCount++;

                $packageId =
                    (int) $row['construction_work_package_id'];

                if (! isset($workPackageIds[$packageId])) {
                    throw new RuntimeException(
                        'Usage Mapping ID '
                        . $mappingId
                        . ' references missing Construction Work Package ID '
                        . $packageId
                        . '.'
                    );
                }
            }
        }

        unset($row);

        if ($variantCount !== 0) {
            throw new RuntimeException(
                'Expected zero variant-level usage mappings, found '
                . $variantCount
                . '. Generator stopped for safety.'
            );
        }

        if ($activityCount !== 75) {
            throw new RuntimeException(
                'Expected 75 mappings with Activity, found '
                . $activityCount
                . '.'
            );
        }

        if ($packageCount !== 14) {
            throw new RuntimeException(
                'Expected 14 mappings with Work Package, found '
                . $packageCount
                . '.'
            );
        }

        $this->info(
            'Product Usage Mappings validated: '
            . number_format(count($rows))
            . ' mappings.'
        );

        $this->info(
            'Dependencies validated: 75 Activity mappings / '
            . '14 Work Package mappings / 0 Variant mappings.'
        );

        return $rows;
    }

    /**
     * Build complete production transfer SQL.
     */
    private function buildTransferSql(
        array $workPackages,
        array $usageMappings
    ): string {
        $lines = [];

        $lines[] = '-- ============================================================';
        $lines[] = '-- Ravion Materials ERP';
        $lines[] = '-- Construction Work Packages + Product Usage Mapping Transfer';
        $lines[] = '-- Generated: ' . now()->format('Y-m-d H:i:s');
        $lines[] = '-- ============================================================';
        $lines[] = '';
        $lines[] = '-- IMPORTANT:';
        $lines[] = '-- 1. Production Product Master must contain 12,651 products.';
        $lines[] = '-- 2. construction_work_packages must still be empty.';
        $lines[] = '-- 3. material_product_usage_mappings must still be empty.';
        $lines[] = '-- 4. Take a production DB backup before execution.';
        $lines[] = '-- 5. Do not execute until production identity validation passes.';
        $lines[] = '';
        $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $lines[] = 'START TRANSACTION;';
        $lines[] = '';

        /*
         * Work packages are inserted with their local IDs because the
         * production table is confirmed empty and the hierarchy uses
         * parent_id. Activity Division is resolved by stable code.
         */
        $lines[] = '-- ============================================================';
        $lines[] = '-- CONSTRUCTION WORK PACKAGES';
        $lines[] = '-- Expected inserts: 180';
        $lines[] = '-- ============================================================';
        $lines[] = '';

        foreach ($workPackages as $row) {
            $divisionCode = null;

if ($row['activity_division_id'] !== null) {
    $divisionCode =
        $this->localDivisionCodes[
            (int) $row['activity_division_id']
        ];
}

            $lines[] =
                'INSERT INTO `construction_work_packages` '
                . '(`id`,`parent_id`,`code`,`name`,'
                . '`activity_division_id`,`sort_order`,`is_active`,'
                . '`remarks`,`created_at`,`updated_at`) '
                . 'VALUES ('
                . $this->sqlValue((int) $row['id']) . ','
                . $this->sqlValue(
                    $row['parent_id'] === null
                        ? null
                        : (int) $row['parent_id']
                ) . ','
                . $this->sqlValue($row['code']) . ','
                . $this->sqlValue($row['name']) . ','
                . (
    $divisionCode === null
        ? 'NULL,'
        : '(SELECT `id` FROM `activity_divisions` '
            . 'WHERE `code` = '
            . $this->sqlValue($divisionCode)
            . ' LIMIT 1),'
)
                . $this->sqlValue($row['sort_order']) . ','
                . $this->sqlValue($row['is_active']) . ','
                . $this->sqlValue($row['remarks']) . ','
                . $this->sqlValue($row['created_at']) . ','
                . $this->sqlValue($row['updated_at'])
                . ');';
        }

        $lines[] = '';
        $lines[] = '-- ============================================================';
        $lines[] = '-- PRODUCT USAGE MAPPINGS';
        $lines[] = '-- Expected inserts: 9,513';
        $lines[] = '-- ============================================================';
        $lines[] = '';

        foreach ($usageMappings as $row) {
            $divisionLookup =
                '(SELECT `id` FROM `activity_divisions` '
                . 'WHERE `code` = '
                . $this->sqlValue($row['_division_code'])
                . ' LIMIT 1)';

            if ($row['activity_id'] !== null) {
                $activityLookup =
                    '(SELECT a.`id` '
                    . 'FROM `activities` a '
                    . 'INNER JOIN `activity_divisions` d '
                    . 'ON d.`id` = a.`activity_division_id` '
                    . 'WHERE d.`code` = '
                    . $this->sqlValue($row['_division_code'])
                    . ' AND a.`activity_name` = '
                    . $this->sqlValue($row['_activity_name'])
                    . ' LIMIT 1)';
            } else {
                $activityLookup = 'NULL';
            }

            /*
             * material_variant_id is intentionally NULL because validation
             * has already proved all 9,513 mappings are product-level.
             */
            $lines[] =
                'INSERT INTO `material_product_usage_mappings` '
                . '(`material_type_id`,`material_variant_id`,'
                . '`activity_division_id`,`activity_id`,'
                . '`construction_work_package_id`,`usage_type`,'
                . '`is_primary`,`sort_order`,`is_active`,'
                . '`source_code`,`source_name`,`source`,`remarks`,'
                . '`created_at`,`updated_at`) '
                . 'VALUES ('
                . $this->sqlValue(
                    $row['_production_product_id']
                ) . ','
                . 'NULL,'
                . $divisionLookup . ','
                . $activityLookup . ','
                . $this->sqlValue(
                    $row['construction_work_package_id'] === null
                        ? null
                        : (int) $row['construction_work_package_id']
                ) . ','
                . $this->sqlValue($row['usage_type']) . ','
                . $this->sqlValue($row['is_primary']) . ','
                . $this->sqlValue($row['sort_order']) . ','
                . $this->sqlValue($row['is_active']) . ','
                . $this->sqlValue($row['source_code']) . ','
                . $this->sqlValue($row['source_name']) . ','
                . $this->sqlValue($row['source']) . ','
                . $this->sqlValue($row['remarks']) . ','
                . $this->sqlValue($row['created_at']) . ','
                . $this->sqlValue($row['updated_at'])
                . ');';
        }

        $lines[] = '';
        $lines[] = '-- ============================================================';
        $lines[] = '-- PRE-COMMIT VERIFICATION';
        $lines[] = '-- ============================================================';
        $lines[] = '';
        $lines[] = 'SELECT COUNT(*) AS work_packages_after_transfer';
        $lines[] = 'FROM construction_work_packages;';
        $lines[] = '';
        $lines[] = 'SELECT COUNT(*) AS usage_mappings_after_transfer';
        $lines[] = 'FROM material_product_usage_mappings;';
        $lines[] = '';
        $lines[] = 'SELECT COUNT(*) AS usage_mappings_missing_product';
        $lines[] = 'FROM material_product_usage_mappings m';
        $lines[] = 'LEFT JOIN material_types p';
        $lines[] = '  ON p.id = m.material_type_id';
        $lines[] = 'WHERE p.id IS NULL;';
        $lines[] = '';
        $lines[] = 'SELECT COUNT(*) AS usage_mappings_missing_division';
        $lines[] = 'FROM material_product_usage_mappings m';
        $lines[] = 'LEFT JOIN activity_divisions d';
        $lines[] = '  ON d.id = m.activity_division_id';
        $lines[] = 'WHERE d.id IS NULL;';
        $lines[] = '';
        $lines[] = 'SELECT COUNT(*) AS usage_mappings_missing_activity';
        $lines[] = 'FROM material_product_usage_mappings m';
        $lines[] = 'LEFT JOIN activities a';
        $lines[] = '  ON a.id = m.activity_id';
        $lines[] = 'WHERE m.activity_id IS NOT NULL';
        $lines[] = '  AND a.id IS NULL;';
        $lines[] = '';
        $lines[] = 'SELECT COUNT(*) AS usage_mappings_missing_work_package';
        $lines[] = 'FROM material_product_usage_mappings m';
        $lines[] = 'LEFT JOIN construction_work_packages w';
        $lines[] = '  ON w.id = m.construction_work_package_id';
        $lines[] = 'WHERE m.construction_work_package_id IS NOT NULL';
        $lines[] = '  AND w.id IS NULL;';
        $lines[] = '';
        $lines[] = '-- COMMIT IS INTENTIONALLY NOT INCLUDED.';
        $lines[] = '-- Production execution will use a reviewed execution copy.';
        $lines[] = '-- Until COMMIT is explicitly issued, the transaction can';
        $lines[] = '-- be rolled back if any verification result is unexpected.';
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    /**
     * Generate human-readable validation report.
     */
    private function buildReport(
        array $workPackages,
        array $usageMappings
    ): string {
        $rootPackages = collect($workPackages)
            ->whereNull('parent_id')
            ->count();

        $childPackages = count($workPackages) - $rootPackages;

        $withActivity = collect($usageMappings)
            ->whereNotNull('activity_id')
            ->count();

        $withPackage = collect($usageMappings)
            ->whereNotNull('construction_work_package_id')
            ->count();

        $withVariant = collect($usageMappings)
            ->whereNotNull('material_variant_id')
            ->count();

        $uniqueProducts = collect($usageMappings)
            ->pluck('_product_code')
            ->unique()
            ->count();

        $uniqueDivisions = collect($usageMappings)
            ->pluck('_division_code')
            ->unique()
            ->count();

        return implode(PHP_EOL, [
            'RAVION MATERIALS ERP',
            'CONSTRUCTION WORK PACKAGE + PRODUCT USAGE MAPPING TRANSFER',
            str_repeat('=', 72),
            '',
            'Generated: ' . now()->format('Y-m-d H:i:s'),
            '',
            'PRODUCTION PRODUCT SNAPSHOT',
            'Products: ' . self::EXPECTED_PRODUCTION_PRODUCTS,
            '',
            'CONSTRUCTION WORK PACKAGES',
            'Total: ' . count($workPackages),
            'Root packages: ' . $rootPackages,
            'Child packages: ' . $childPackages,
            '',
            'PRODUCT USAGE MAPPINGS',
            'Total: ' . count($usageMappings),
            'Unique mapped products: ' . $uniqueProducts,
            'Unique mapped activity divisions: ' . $uniqueDivisions,
            'Mappings with Activity: ' . $withActivity,
            'Mappings with Work Package: ' . $withPackage,
            'Mappings with Variant: ' . $withVariant,
            '',
            'RESOLUTION STRATEGY',
            '- Product: canonical material_type_code -> production snapshot ID',
            '- Activity Division: activity_divisions.code',
            '- Activity: Activity Division code + activity_name',
            '- Work Package: preserved ID because production table is empty',
            '- Work Package hierarchy: preserved parent_id',
            '- Material Variant: NULL for all mappings',
            '',
            'SAFETY',
            '- No production database was modified by this generator.',
            '- No production Product IDs were copied from local.',
            '- Existing production products are untouched.',
            '- Existing brands/specifications/grades/variants are untouched.',
            '- Staging tables are untouched.',
            '- Generated SQL intentionally contains no COMMIT.',
            '',
        ]);
    }

    /**
     * Convert a PHP value into a safe SQL literal.
     */
    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        return DB::getPdo()->quote((string) $value);
    }
}