<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class StageMaterialCatalogueImport extends Command
{
    protected $signature = 'materials:catalog-stage
                            {zip : Full path to Ravion Materials ERP import ZIP}
                            {--batch-code= : Optional custom batch code}
                            {--replace : Replace an existing non-imported batch with the same batch code}';

    protected $description =
        'Stage the finalized Ravion Materials ERP catalogue ZIP without modifying the canonical Product Master.';

    private const PRODUCT_FILE = '01_Product_Master.csv';
    private const SPECIFICATION_FILE = '02_Product_Specification_Master.csv';
    private const BRAND_FILE = '03_Brand_Master.csv';
    private const PRODUCT_BRAND_FILE = '04_Product_Brand_Mapping.csv';
    private const UNIT_FILE = '05_Unit_Master_Mapping.csv';
    private const USAGE_FILE = '06_Product_Usage_Mapping.csv';
    private const ALIAS_FILE = '07_Search_Alias_Master.csv';
    private const AUDIT_FILE = '08_Import_Audit.csv';

    private const PACKAGE_VERSION = 'V1';

    private int $batchId;

    private array $counts = [
        'products' => 0,
        'specifications' => 0,
        'brands' => 0,
        'product_brands' => 0,
        'units' => 0,
        'usage' => 0,
        'aliases' => 0,
        'audit' => 0,

        'ready' => 0,
        'review' => 0,
        'excluded' => 0,
        'errors' => 0,
    ];

    public function handle(): int
    {
        $zipPath = $this->resolveZipPath(
            (string) $this->argument('zip')
        );

        if (! is_file($zipPath)) {
            $this->error("ZIP file not found: {$zipPath}");

            return self::FAILURE;
        }

        if (! class_exists(ZipArchive::class)) {
            $this->error(
                'PHP ZipArchive extension is not available. Enable the PHP zip extension first.'
            );

            return self::FAILURE;
        }

        $checksum = hash_file('sha256', $zipPath);

        if ($checksum === false) {
            $this->error('Unable to calculate ZIP checksum.');

            return self::FAILURE;
        }

        $batchCode = trim(
            (string) (
                $this->option('batch-code')
                ?: 'MAT-CAT-' . now()->format('Ymd-His')
            )
        );

        $zip = new ZipArchive();

        $openResult = $zip->open($zipPath);

        if ($openResult !== true) {
            $this->error(
                "Unable to open ZIP file. ZipArchive result: {$openResult}"
            );

            return self::FAILURE;
        }

        try {
            $this->validateRequiredFiles($zip);

            $this->prepareBatch(
                $batchCode,
                basename($zipPath),
                $checksum
            );

            $this->newLine();
            $this->info('Ravion Materials catalogue staging started.');
            $this->line("Batch: {$batchCode}");
            $this->line("ZIP: {$zipPath}");
            $this->line("SHA-256: {$checksum}");
            $this->newLine();

            /*
            |--------------------------------------------------------------------------
            | Stage Package
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | These methods write ONLY to catalogue staging tables.
            |
            | They do not create:
            | - material_types
            | - material_variants
            | - specifications
            | - brands
            | - aliases
            | - usage mappings
            |
            */

            DB::transaction(function () use ($zip): void {
                $this->stageProducts($zip);
                $this->stageSpecifications($zip);
                $this->stageUnits($zip);
                $this->stageUsageMappings($zip);
                $this->stageSearchAliases($zip);
                $this->stageImportAudit($zip);

                /*
                |--------------------------------------------------------------------------
                | Brand Files
                |--------------------------------------------------------------------------
                |
                | Current finalized package contains headers only.
                |
                | We still count them so the batch accurately represents the
                | source package.
                |
                */

                $this->counts['brands'] =
                    $this->countCsvDataRows(
                        $zip,
                        self::BRAND_FILE
                    );

                $this->counts['product_brands'] =
                    $this->countCsvDataRows(
                        $zip,
                        self::PRODUCT_BRAND_FILE
                    );

                $this->updateBatchCounts();
            });

            $this->displaySummary();

            $this->newLine();
            $this->info(
                'Staging completed successfully. Canonical Product Master was NOT modified.'
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            if (isset($this->batchId)) {
                DB::table('material_catalog_import_batches')
                    ->where('id', $this->batchId)
                    ->update([
                        'status' => 'Failed',
                        'error_message' => $exception->getMessage(),
                        'completed_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            $this->newLine();
            $this->error('Catalogue staging failed.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            $zip->close();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Batch
    |--------------------------------------------------------------------------
    */

    private function prepareBatch(
        string $batchCode,
        string $fileName,
        string $checksum
    ): void {
        $existing = DB::table('material_catalog_import_batches')
            ->where('batch_code', $batchCode)
            ->first();

        if ($existing) {
            if (! $this->option('replace')) {
                throw new RuntimeException(
                    "Batch code {$batchCode} already exists. "
                    . 'Use a different --batch-code or explicitly use --replace.'
                );
            }

            if ($existing->status === 'Imported') {
                throw new RuntimeException(
                    'An Imported batch cannot be replaced.'
                );
            }

            DB::table('material_catalog_import_batches')
                ->where('id', $existing->id)
                ->delete();
        }

        $duplicateChecksum = DB::table(
            'material_catalog_import_batches'
        )
            ->where('source_checksum', $checksum)
            ->whereNotIn('status', [
                'Failed',
                'Cancelled',
            ])
            ->first();

        if ($duplicateChecksum) {
            throw new RuntimeException(
                'This exact ZIP package has already been staged under batch '
                . $duplicateChecksum->batch_code
                . '. Duplicate import prevented.'
            );
        }

        $now = now();

        $this->batchId = DB::table(
            'material_catalog_import_batches'
        )->insertGetId([
            'batch_code' => $batchCode,
            'package_name' =>
                'Ravion Materials ERP Import Package',
            'package_version' => self::PACKAGE_VERSION,
            'source_file_name' => $fileName,
            'source_checksum' => $checksum,
            'status' => 'Staging',
            'started_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function updateBatchCounts(): void
    {
        DB::table('material_catalog_import_batches')
            ->where('id', $this->batchId)
            ->update([
                'status' => 'Staged',

                'total_product_rows' =>
                    $this->counts['products'],

                'total_specification_rows' =>
                    $this->counts['specifications'],

                'total_brand_rows' =>
                    $this->counts['brands'],

                'total_product_brand_rows' =>
                    $this->counts['product_brands'],

                'total_unit_mapping_rows' =>
                    $this->counts['units'],

                'total_usage_mapping_rows' =>
                    $this->counts['usage'],

                'total_search_alias_rows' =>
                    $this->counts['aliases'],

                'total_audit_rows' =>
                    $this->counts['audit'],

                'staged_product_rows' =>
                    $this->counts['products'],

                'ready_product_rows' =>
                    $this->counts['ready'],

                'review_product_rows' =>
                    $this->counts['review'],

                'excluded_product_rows' =>
                    $this->counts['excluded'],

                'error_product_rows' =>
                    $this->counts['errors'],

                'completed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    private function stageProducts(ZipArchive $zip): void
    {
        $this->info('1/6 Staging Product Master...');

        $rows = [];

        foreach (
            $this->csvRows($zip, self::PRODUCT_FILE)
            as $record
        ) {
            $productCode = $this->clean(
                $record['Product Code'] ?? null
            );

            $productName = $this->clean(
                $record['Product Name'] ?? null
            );

            $group = $this->clean(
                $record['Product Group'] ?? null
            );

            $type = $this->clean(
                $record['Product Type'] ?? null
            );

            $inventoryType = $this->clean(
                $record['Inventory Type'] ?? null
            );

            $defaultUnit = $this->clean(
                $record['Default Unit'] ?? null
            );

            $masterStatus = $this->clean(
                $record['Master Status'] ?? null
            );

            $sourceNotes = $this->clean(
                $record['Review / Specification Notes'] ?? null
            );

            $errors = [];

            if (! $productCode) {
                $errors[] = 'Missing Product Code';
            }

            if (! $productName) {
                $errors[] = 'Missing Product Name';
            }

            if ($errors !== []) {
                $stagingStatus = 'Error';
                $this->counts['errors']++;
            } elseif (
                strcasecmp(
                    (string) $masterStatus,
                    'Provisional Ready'
                ) === 0
            ) {
                $stagingStatus = 'Ready';
                $this->counts['ready']++;
            } else {
                /*
                | Anything not explicitly Provisional Ready is staged
                | conservatively for review.
                */
                $stagingStatus = 'Review';
                $this->counts['review']++;
            }

            $rows[] = [
                'import_batch_id' => $this->batchId,
                'source_row_number' =>
                    $record['_row_number'],

                'source_product_code' =>
                    $productCode ?? '',

                'product_group' => $group,
                'product_type' => $type,
                'product_name' => $productName ?? '',
                'inventory_type' => $inventoryType,
                'default_unit' => $defaultUnit,

                'source_master_status' =>
                    $masterStatus,

                'normalized_product_name' =>
                    $this->normalize($productName),

                'normalized_product_group' =>
                    $this->normalize($group),

                'normalized_product_type' =>
                    $this->normalize($type),

                'staging_status' => $stagingStatus,

                'review_reason' =>
                    $stagingStatus === 'Review'
                        ? ($sourceNotes ?: $masterStatus)
                        : null,

                'validation_errors' =>
                    $errors !== []
                        ? json_encode(
                            $errors,
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,

                'remarks' => $sourceNotes,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->counts['products']++;

            if (count($rows) >= 500) {
                $this->insertChunk(
                    'material_catalog_product_staging',
                    $rows
                );

                $rows = [];
            }
        }

        $this->insertChunk(
            'material_catalog_product_staging',
            $rows
        );

        $this->line(
            "    Products staged: {$this->counts['products']}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Specifications
    |--------------------------------------------------------------------------
    */

    private function stageSpecifications(
        ZipArchive $zip
    ): void {
        $this->info('2/6 Staging Specification candidates...');

        $rows = [];

        foreach (
            $this->csvRows(
                $zip,
                self::SPECIFICATION_FILE
            )
            as $record
        ) {
            $specCode = $this->clean(
                $record['Specification Code'] ?? null
            );

            $productCode = $this->clean(
                $record['Product Code'] ?? null
            );

            /*
            |--------------------------------------------------------------------------
            | Final catalogue specification structure
            |--------------------------------------------------------------------------
            |
            | Actual CSV fields are:
            |
            | Source Specification Field 1
            | Source Specification Field 2
            | Source Specification Field 3
            | Source Specification Field 4
            | Source Specification Field 5
            |
            | We preserve ALL five in remarks.
            |
            | Field 3 currently carries the useful raw technical
            | specification candidate in the finalized package.
            |
            | We deliberately DO NOT attempt aggressive automatic parsing
            | into Grade / Finish / Colour here.
            |
            */

            $field1 = $this->clean(
                $record['Source Specification Field 1'] ?? null
            );

            $field2 = $this->clean(
                $record['Source Specification Field 2'] ?? null
            );

            $field3 = $this->clean(
                $record['Source Specification Field 3'] ?? null
            );

            $field4 = $this->clean(
                $record['Source Specification Field 4'] ?? null
            );

            $field5 = $this->clean(
                $record['Source Specification Field 5'] ?? null
            );

            $errors = [];

            if (! $productCode) {
                $errors[] = 'Missing Product Code';
            }

            if (! $specCode) {
                $errors[] = 'Missing Specification Code';
            }

            if (! $field3) {
                $errors[] =
                    'Missing raw specification candidate';
            }

            $stagingStatus =
                $errors === [] ? 'Review' : 'Error';

            $rows[] = [
                'import_batch_id' => $this->batchId,
                'source_row_number' =>
                    $record['_row_number'],

                'source_product_code' =>
                    $productCode ?? '',

                'source_specification_code' =>
                    $specCode,

                'specification_name' => $field3,

                /*
                | Do not make assumptions yet.
                | Variant normalization happens after staging.
                */
                'size_dimension' => null,
                'grade_class' => null,
                'finish' => null,
                'colour_shade' => null,

                'source_status' => $field5,

                'normalized_specification_name' =>
                    $this->normalize($field3),

                'normalized_size_dimension' => null,
                'normalized_grade_class' => null,

                'staging_status' => $stagingStatus,

                'review_reason' =>
                    $field4 ?: 'Specification normalization required',

                'validation_errors' =>
                    $errors !== []
                        ? json_encode(
                            $errors,
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,

                'remarks' => json_encode([
                    'source_specification_field_1' => $field1,
                    'source_specification_field_2' => $field2,
                    'source_specification_field_3' => $field3,
                    'source_specification_field_4' => $field4,
                    'source_specification_field_5' => $field5,
                ], JSON_UNESCAPED_UNICODE),

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->counts['specifications']++;

            if (count($rows) >= 500) {
                $this->insertChunk(
                    'material_catalog_specification_staging',
                    $rows
                );

                $rows = [];
            }
        }

        $this->insertChunk(
            'material_catalog_specification_staging',
            $rows
        );

        $this->line(
            "    Specifications staged: {$this->counts['specifications']}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Units
    |--------------------------------------------------------------------------
    */

    private function stageUnits(ZipArchive $zip): void
    {
        $this->info('3/6 Staging Unit mappings...');

        $rows = [];

        foreach (
            $this->csvRows($zip, self::UNIT_FILE)
            as $record
        ) {
            $unitCode = $this->clean(
                $record['Unit Code'] ?? null
            );

            $unitName = $this->clean(
                $record['Unit Name'] ?? null
            );

            $symbol = $this->clean(
                $record['Unit Symbol / Source Name'] ?? null
            );

            $sourceStatus = $this->clean(
                $record['Status'] ?? null
            );

            $errors = [];

            if (! $unitName) {
                $errors[] = 'Missing Unit Name';
            }

            $rows[] = [
                'import_batch_id' => $this->batchId,
                'source_row_number' =>
                    $record['_row_number'],

                'source_unit_code' => $unitCode,
                'source_unit_name' => $unitName ?? '',
                'source_unit_symbol' => $symbol,

                'normalized_unit_name' =>
                    $this->normalize($unitName),

                'staging_status' =>
                    $errors === [] ? 'Pending' : 'Error',

                'review_reason' =>
                    'Reconcile against existing Ravion Unit Master',

                'validation_errors' =>
                    $errors !== []
                        ? json_encode(
                            $errors,
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,

                'remarks' => $sourceStatus
                    ? "Source status: {$sourceStatus}"
                    : null,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->counts['units']++;

            if (count($rows) >= 250) {
                $this->insertChunk(
                    'material_catalog_unit_mapping_staging',
                    $rows
                );

                $rows = [];
            }
        }

        $this->insertChunk(
            'material_catalog_unit_mapping_staging',
            $rows
        );

        $this->line(
            "    Unit mappings staged: {$this->counts['units']}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Usage Mappings
    |--------------------------------------------------------------------------
    */

    private function stageUsageMappings(
        ZipArchive $zip
    ): void {
        $this->info('4/6 Staging Product Usage mappings...');

        $rows = [];

        foreach (
            $this->csvRows($zip, self::USAGE_FILE)
            as $record
        ) {
            $productCode = $this->clean(
                $record['V3 Product Code'] ?? null
            );

            $legacyCanonicalProductId = $this->clean(
                $record['Legacy Canonical Product ID'] ?? null
            );

            $field1 = $this->clean(
                $record['Source Mapping Field 1'] ?? null
            );

            $field2 = $this->clean(
                $record['Source Mapping Field 2'] ?? null
            );

            $field3 = $this->clean(
                $record['Source Mapping Field 3'] ?? null
            );

            $field4 = $this->clean(
                $record['Source Mapping Field 4'] ?? null
            );

            $field5 = $this->clean(
                $record['Source Mapping Field 5'] ?? null
            );

            $field6 = $this->clean(
                $record['Source Mapping Field 6'] ?? null
            );

            $field7 = $this->clean(
                $record['Source Mapping Field 7'] ?? null
            );

            $errors = [];

            if (! $productCode) {
                $errors[] = 'Missing V3 Product Code';
            }

            /*
            |--------------------------------------------------------------------------
            | Final package interpretation
            |--------------------------------------------------------------------------
            |
            | Field 1 = Division code
            | Field 2 = legacy/backend mapping code
            | Field 3 = Division name
            | Field 4 = Usage code
            | Field 5 = Usage name
            | Field 6 = Usage description/context
            | Field 7 = Usage type (Primary, Closeout/Handover Usage, etc.)
            |
            */

            $rows[] = [
                'import_batch_id' => $this->batchId,
                'source_row_number' =>
                    $record['_row_number'],

                'source_product_code' =>
                    $productCode ?? '',

                'division_code' => $field1,
                'division_name' => $field3,

                'usage_code' => $field4,
                'usage_name' => $field5,
                'usage_type' => $field7,

                'is_primary' =>
                    strcasecmp(
                        (string) $field7,
                        'Primary'
                    ) === 0,

                'source_status' => null,

                'staging_status' =>
                    $errors === [] ? 'Pending' : 'Error',

                'review_reason' =>
                    'Resolve source usage into Activity Division / Activity / Work Package intelligence',

                'validation_errors' =>
                    $errors !== []
                        ? json_encode(
                            $errors,
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,

                'remarks' => json_encode([
                    'legacy_canonical_product_id' =>
                        $legacyCanonicalProductId,

                    'source_mapping_field_1' => $field1,
                    'source_mapping_field_2' => $field2,
                    'source_mapping_field_3' => $field3,
                    'source_mapping_field_4' => $field4,
                    'source_mapping_field_5' => $field5,
                    'source_mapping_field_6' => $field6,
                    'source_mapping_field_7' => $field7,
                ], JSON_UNESCAPED_UNICODE),

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->counts['usage']++;

            if (count($rows) >= 500) {
                $this->insertChunk(
                    'material_catalog_usage_mapping_staging',
                    $rows
                );

                $rows = [];
            }
        }

        $this->insertChunk(
            'material_catalog_usage_mapping_staging',
            $rows
        );

        $this->line(
            "    Usage mappings staged: {$this->counts['usage']}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search Aliases
    |--------------------------------------------------------------------------
    */

    private function stageSearchAliases(
        ZipArchive $zip
    ): void {
        $this->info('5/6 Staging Search aliases...');

        $rows = [];

        foreach (
            $this->csvRows($zip, self::ALIAS_FILE)
            as $record
        ) {
            $productCode = $this->clean(
                $record['Product Code'] ?? null
            );

            $alias = $this->clean(
                $record['Search Alias / Keyword'] ?? null
            );

            $aliasType = $this->clean(
                $record['Alias Type'] ?? null
            );

            $sourceStatus = $this->clean(
                $record['Status'] ?? null
            );

            $errors = [];

            if (! $productCode) {
                $errors[] = 'Missing Product Code';
            }

            if (! $alias) {
                $errors[] = 'Missing Search Alias';
            }

            $rows[] = [
                'import_batch_id' => $this->batchId,
                'source_row_number' =>
                    $record['_row_number'],

                'source_product_code' =>
                    $productCode ?? '',

                'alias' => $alias ?? '',
                'normalized_alias' =>
                    $this->normalize($alias),

                'alias_type' => $aliasType,

                'source' =>
                    'Ravion Materials ERP Import Package V1',

                'staging_status' =>
                    $errors === []
                        ? (
                            strcasecmp(
                                (string) $sourceStatus,
                                'Active'
                            ) === 0
                                ? 'Ready'
                                : 'Review'
                        )
                        : 'Error',

                'review_reason' =>
                    $sourceStatus
                        && strcasecmp(
                            $sourceStatus,
                            'Active'
                        ) !== 0
                            ? "Source status: {$sourceStatus}"
                            : null,

                'validation_errors' =>
                    $errors !== []
                        ? json_encode(
                            $errors,
                            JSON_UNESCAPED_UNICODE
                        )
                        : null,

                'remarks' => $sourceStatus
                    ? "Source status: {$sourceStatus}"
                    : null,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->counts['aliases']++;

            if (count($rows) >= 500) {
                $this->insertChunk(
                    'material_catalog_search_alias_staging',
                    $rows
                );

                $rows = [];
            }
        }

        $this->insertChunk(
            'material_catalog_search_alias_staging',
            $rows
        );

        $this->line(
            "    Search aliases staged: {$this->counts['aliases']}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Import Audit / Catalogue Lineage
    |--------------------------------------------------------------------------
    */

    private function stageImportAudit(
        ZipArchive $zip
    ): void {
        $this->info('6/6 Staging Import Audit / lineage...');

        $rows = [];

        foreach (
            $this->csvRows($zip, self::AUDIT_FILE)
            as $record
        ) {
            $originalProductId = $this->clean(
                $record['Original Product ID'] ?? null
            );

            $originalProductName = $this->clean(
                $record['Original Product Name'] ?? null
            );

            $phase2ProductId = $this->clean(
                $record['Phase 2 Product ID'] ?? null
            );

            $phase2ProductName = $this->clean(
                $record['Phase 2 Product Name'] ?? null
            );

            $v3ProductId = $this->clean(
                $record['V3 Product ID'] ?? null
            );

            $v3ProductName = $this->clean(
                $record['V3 Product Name'] ?? null
            );

            $phase2Specification = $this->clean(
                $record['Phase 2 Extracted Specification'] ?? null
            );

            $phase3Specification = $this->clean(
                $record['Phase 3 Extracted Specification'] ?? null
            );

            $payload = [
                'original_product_id' => $originalProductId,
                'original_product_name' => $originalProductName,
                'phase_2_product_id' => $phase2ProductId,
                'phase_2_product_name' => $phase2ProductName,
                'v3_product_id' => $v3ProductId,
                'v3_product_name' => $v3ProductName,
                'phase_2_extracted_specification' =>
                    $phase2Specification,
                'phase_3_extracted_specification' =>
                    $phase3Specification,
            ];

            $rows[] = [
                'import_batch_id' => $this->batchId,
                'source_row_number' =>
                    $record['_row_number'],

                'source_product_code' => $v3ProductId,

                'audit_type' => 'Catalogue Lineage',
                'audit_status' => 'Recorded',
                'severity' => null,
                'field_name' => null,

                'source_value' => json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                ),

                'message' =>
                    $v3ProductId
                        ? "Catalogue lineage for {$v3ProductId}"
                        : 'Catalogue lineage record',

                'recommended_action' => null,

                'staging_status' => 'Recorded',

                'remarks' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->counts['audit']++;

            if (count($rows) >= 500) {
                $this->insertChunk(
                    'material_catalog_import_audit_staging',
                    $rows
                );

                $rows = [];
            }
        }

        $this->insertChunk(
            'material_catalog_import_audit_staging',
            $rows
        );

        $this->line(
            "    Audit records staged: {$this->counts['audit']}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ZIP / CSV Helpers
    |--------------------------------------------------------------------------
    */

    private function validateRequiredFiles(
        ZipArchive $zip
    ): void {
        $required = [
            self::PRODUCT_FILE,
            self::SPECIFICATION_FILE,
            self::BRAND_FILE,
            self::PRODUCT_BRAND_FILE,
            self::UNIT_FILE,
            self::USAGE_FILE,
            self::ALIAS_FILE,
            self::AUDIT_FILE,
        ];

        foreach ($required as $file) {
            if ($this->findZipEntry($zip, $file) === null) {
                throw new RuntimeException(
                    "Required package file is missing: {$file}"
                );
            }
        }
    }

    private function csvRows(
        ZipArchive $zip,
        string $fileName
    ): \Generator {
        $entryName = $this->findZipEntry(
            $zip,
            $fileName
        );

        if ($entryName === null) {
            throw new RuntimeException(
                "CSV file not found in ZIP: {$fileName}"
            );
        }

        $stream = $zip->getStream($entryName);

        if ($stream === false) {
            throw new RuntimeException(
                "Unable to open CSV from ZIP: {$fileName}"
            );
        }

        try {
            $header = fgetcsv($stream);

            if ($header === false) {
                throw new RuntimeException(
                    "CSV has no header row: {$fileName}"
                );
            }

            $header = array_map(
                fn ($value) =>
                    $this->cleanCsvHeader(
                        (string) $value
                    ),
                $header
            );

            $rowNumber = 1;

            while (
                ($values = fgetcsv($stream)) !== false
            ) {
                $rowNumber++;

                if ($this->isBlankCsvRow($values)) {
                    continue;
                }

                /*
                | Pad short rows or trim excess columns safely.
                */
                $values = array_pad(
                    $values,
                    count($header),
                    null
                );

                $values = array_slice(
                    $values,
                    0,
                    count($header)
                );

                $record = array_combine(
                    $header,
                    $values
                );

                if ($record === false) {
                    throw new RuntimeException(
                        "Unable to parse {$fileName} row {$rowNumber}."
                    );
                }

                $record['_row_number'] = $rowNumber;

                yield $record;
            }
        } finally {
            fclose($stream);
        }
    }

    private function countCsvDataRows(
        ZipArchive $zip,
        string $fileName
    ): int {
        $count = 0;

        foreach (
            $this->csvRows($zip, $fileName)
            as $record
        ) {
            $count++;
        }

        return $count;
    }

    private function findZipEntry(
        ZipArchive $zip,
        string $expectedFileName
    ): ?string {
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if ($name === false) {
                continue;
            }

            if (
                strcasecmp(
                    basename($name),
                    $expectedFileName
                ) === 0
            ) {
                return $name;
            }
        }

        return null;
    }

    private function cleanCsvHeader(
        string $header
    ): string {
        /*
        | Remove UTF-8 BOM from first CSV header.
        */
        $header = preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $header
        ) ?? $header;

        return trim($header);
    }

    private function isBlankCsvRow(
        array $values
    ): bool {
        foreach ($values as $value) {
            if (
                trim((string) $value) !== ''
            ) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Data Helpers
    |--------------------------------------------------------------------------
    */

    private function insertChunk(
        string $table,
        array $rows
    ): void {
        if ($rows === []) {
            return;
        }

        DB::table($table)->insert($rows);
    }

    private function clean(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalize(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = Str::lower(trim($value));

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

    private function resolveZipPath(
        string $path
    ): string {
        $path = trim(
            $path,
            " \t\n\r\0\x0B\"'"
        );

        /*
        | Absolute Windows path:
        | C:\...
        */
        if (
            preg_match(
                '/^[A-Za-z]:[\\\\\/]/',
                $path
            ) === 1
        ) {
            return $path;
        }

        /*
        | Unix absolute path.
        */
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    private function displaySummary(): void
    {
        $this->newLine();

        $this->table(
            ['Dataset', 'Rows'],
            [
                [
                    'Product Master',
                    number_format(
                        $this->counts['products']
                    ),
                ],
                [
                    'Specification Candidates',
                    number_format(
                        $this->counts['specifications']
                    ),
                ],
                [
                    'Brand Master',
                    number_format(
                        $this->counts['brands']
                    ),
                ],
                [
                    'Product-Brand Mapping',
                    number_format(
                        $this->counts['product_brands']
                    ),
                ],
                [
                    'Unit Mapping',
                    number_format(
                        $this->counts['units']
                    ),
                ],
                [
                    'Usage Mapping',
                    number_format(
                        $this->counts['usage']
                    ),
                ],
                [
                    'Search Aliases',
                    number_format(
                        $this->counts['aliases']
                    ),
                ],
                [
                    'Import Audit',
                    number_format(
                        $this->counts['audit']
                    ),
                ],
            ]
        );

        $this->newLine();

        $this->table(
            ['Product staging decision', 'Rows'],
            [
                [
                    'Ready',
                    number_format(
                        $this->counts['ready']
                    ),
                ],
                [
                    'Review',
                    number_format(
                        $this->counts['review']
                    ),
                ],
                [
                    'Excluded',
                    number_format(
                        $this->counts['excluded']
                    ),
                ],
                [
                    'Errors',
                    number_format(
                        $this->counts['errors']
                    ),
                ],
            ]
        );
    }
}