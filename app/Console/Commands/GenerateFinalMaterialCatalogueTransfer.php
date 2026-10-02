<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerateFinalMaterialCatalogueTransfer extends Command
{
    protected $signature = 'materials:generate-final-catalogue-transfer';

    protected $description = 'Generate a guarded SQL package for transferring the finalized Material Product catalogue to production';

    public function handle(): int
    {
        $snapshotPath = storage_path(
            'app/product-master-transfer/production-material-products.txt'
        );

        $outputDir = storage_path('app/product-master-transfer');

        $sqlPath = $outputDir . '/final-material-catalogue-transfer.sql';
        $reportPath = $outputDir . '/final-material-catalogue-transfer-report.txt';

        if (! is_file($snapshotPath)) {
            $this->error("Production snapshot not found:");
            $this->line($snapshotPath);

            return self::FAILURE;
        }

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        /*
        |--------------------------------------------------------------------------
        | Read production snapshot
        |--------------------------------------------------------------------------
        |
        | Format:
        | id|material_type_code|material_type_name
        |
        */

        $productionRows = [];
        $productionByCode = [];
        $productionByName = [];

        $lines = file($snapshotPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $parts = explode('|', $line, 3);

            if (count($parts) !== 3) {
                throw new RuntimeException(
                    "Invalid production snapshot line: {$line}"
                );
            }

            [$id, $code, $name] = $parts;

            $row = [
                'id' => (int) $id,
                'code' => trim($code),
                'name' => trim($name),
            ];

            $productionRows[] = $row;

            if ($row['code'] !== '') {
                $productionByCode[$row['code']][] = $row;
            }

            $productionByName[mb_strtolower($row['name'], 'UTF-8')][] = $row;
        }

        /*
        |--------------------------------------------------------------------------
        | Load finalized local catalogue
        |--------------------------------------------------------------------------
        |
        | Only finalized classified products with a canonical product code.
        |
        | General Cleaning Sponge is automatically excluded because its
        | material_type_code is NULL.
        |
        */

        $catalogue = DB::table('material_types')
            ->whereNotNull('material_product_group_id')
            ->whereNotNull('material_product_type_id')
            ->whereNotNull('material_type_code')
            ->where('material_type_code', '<>', '')
            ->orderBy('id')
            ->get([
                'id',
                'material_product_group_id',
                'material_product_type_id',
                'material_group',
                'material_type_name',
                'material_type_code',
                'catalogue_source_code',
                'inventory_type',
                'master_status',
                'is_legacy',
                'unit_master_id',
                'sequence',
                'is_active',
                'remarks',
            ]);

        if ($catalogue->count() !== 9413) {
            throw new RuntimeException(
                'Expected 9413 finalized coded catalogue products, found ' .
                $catalogue->count() .
                '. SQL generation stopped.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate local catalogue uniqueness
        |--------------------------------------------------------------------------
        */

        $duplicateCodes = $catalogue
            ->groupBy('material_type_code')
            ->filter(fn ($rows) => $rows->count() > 1);

        if ($duplicateCodes->isNotEmpty()) {
            throw new RuntimeException(
                'Duplicate canonical material_type_code values detected. ' .
                'SQL generation stopped.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Classify catalogue rows
        |--------------------------------------------------------------------------
        |
        | A = Exact production code + name match
        | B = Code absent, but exactly one production row has same name
        | C = Neither code nor name exists in production -> new product
        |
        */

        $exactMatches = [];
        $nameMatches = [];
        $newProducts = [];
        $conflicts = [];

        foreach ($catalogue as $product) {
            $code = trim((string) $product->material_type_code);
            $name = trim((string) $product->material_type_name);

            $codeMatches = $productionByCode[$code] ?? [];
            $nameMatchesFound = $productionByName[mb_strtolower($name, 'UTF-8')] ?? [];

            if (count($codeMatches) > 0) {
                $sameName = array_values(array_filter(
                    $codeMatches,
                    fn ($row) => mb_strtolower($row['name'], 'UTF-8') === mb_strtolower($name, 'UTF-8')
                ));

                if (count($sameName) === 1) {
                    $exactMatches[] = [
                        'production' => $sameName[0],
                        'local' => $product,
                    ];

                    continue;
                }

                $conflicts[] = [
                    'reason' => 'Canonical code already exists in production with different or ambiguous name',
                    'code' => $code,
                    'name' => $name,
                ];

                continue;
            }

            if (count($nameMatchesFound) === 1) {
                $nameMatches[] = [
                    'production' => $nameMatchesFound[0],
                    'local' => $product,
                ];

                continue;
            }

            if (count($nameMatchesFound) > 1) {
                $conflicts[] = [
                    'reason' => 'Multiple production products have same name',
                    'code' => $code,
                    'name' => $name,
                ];

                continue;
            }

            $newProducts[] = $product;
        }

        /*
        |--------------------------------------------------------------------------
        | Hard safety gates
        |--------------------------------------------------------------------------
        */

        $expectedProductionRows = 3614;
        $expectedExactMatches = 22;
        $expectedNameMatches = 354;
$expectedNewProducts = 9037;
        if (count($productionRows) !== $expectedProductionRows) {
            throw new RuntimeException(
                'Expected production snapshot to contain ' .
                $expectedProductionRows .
                ' rows, found ' .
                count($productionRows) .
                '. SQL generation stopped.'
            );
        }

        if (count($exactMatches) !== $expectedExactMatches) {
            throw new RuntimeException(
                'Expected 22 exact matches, found ' .
                count($exactMatches) .
                '. SQL generation stopped.'
            );
        }

        if (count($nameMatches) !== $expectedNameMatches) {
            throw new RuntimeException(
                'Expected 354 unique name matches, found ' .
                count($nameMatches) .
                '. SQL generation stopped.'
            );
        }

        if (count($newProducts) !== $expectedNewProducts) {
            throw new RuntimeException(
                'Expected 9037 new products, found ' .
                count($newProducts) .
                '. SQL generation stopped.'
            );
        }

        if (count($conflicts) !== 0) {
            throw new RuntimeException(
                'Unexpected catalogue conflicts detected: ' .
                count($conflicts) .
                '. SQL generation stopped.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SQL helpers
        |--------------------------------------------------------------------------
        */

        $quote = function ($value): string {
            if ($value === null) {
                return 'NULL';
            }

            return "'" . str_replace(
                ["\\", "'", "\0", "\n", "\r", "\x1a"],
                ["\\\\", "\\'", "\\0", "\\n", "\\r", "\\Z"],
                (string) $value
            ) . "'";
        };

        $numberOrNull = function ($value): string {
            return $value === null ? 'NULL' : (string) ((int) $value);
        };

        /*
        |--------------------------------------------------------------------------
        | Build guarded SQL
        |--------------------------------------------------------------------------
        */

        $sql = [];

        $sql[] = '-- Ravion ERP Final Material Catalogue Transfer';
        $sql[] = '-- Generated locally. Review before production execution.';
        $sql[] = '-- Existing production IDs are preserved.';
        $sql[] = '-- Existing unmatched production products are untouched.';
        $sql[] = '-- New products are inserted without explicit IDs.';
        $sql[] = '';
        $sql[] = 'SET NAMES utf8mb4;';
        $sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $sql[] = 'START TRANSACTION;';
        $sql[] = '';
        $sql[] = '-- ---------------------------------------------------------';
        $sql[] = '-- PRECONDITION';
        $sql[] = '-- Production must still contain exactly 3614 products.';
        $sql[] = '-- ---------------------------------------------------------';
        $sql[] = 'SET @ravion_product_count_before := (SELECT COUNT(*) FROM material_types);';
        $sql[] = '';
        $sql[] = '-- ---------------------------------------------------------';
        $sql[] = '-- UPDATE 22 EXACT CODE + NAME MATCHES';
        $sql[] = '-- ---------------------------------------------------------';

        foreach ($exactMatches as $match) {
            $p = $match['local'];
            $production = $match['production'];

            $sql[] =
                'UPDATE material_types SET ' .
                'material_product_group_id=' . $numberOrNull($p->material_product_group_id) . ', ' .
                'material_product_type_id=' . $numberOrNull($p->material_product_type_id) . ', ' .
                'material_group=' . $quote($p->material_group) . ', ' .
                'catalogue_source_code=' . $quote($p->catalogue_source_code) . ', ' .
                'inventory_type=' . $quote($p->inventory_type) . ', ' .
                'master_status=' . $quote($p->master_status) . ', ' .
                'unit_master_id=' . $numberOrNull($p->unit_master_id) . ', ' .
                'sequence=' . $numberOrNull($p->sequence) . ', ' .
                'is_active=' . $numberOrNull($p->is_active) . ', ' .
                'updated_at=NOW() ' .
                'WHERE id=' . (int) $production['id'] .
                ' AND material_type_code=' . $quote($production['code']) .
                ' AND material_type_name=' . $quote($production['name']) .
                ' LIMIT 1;';
        }

        $sql[] = '';
        $sql[] = '-- ---------------------------------------------------------';
        $sql[] = '-- UPDATE 354 UNIQUE NAME MATCHES';
        $sql[] = '-- Preserve production ID; adopt finalized canonical code.';
        $sql[] = '-- ---------------------------------------------------------';

        foreach ($nameMatches as $match) {
            $p = $match['local'];
            $production = $match['production'];

            $sql[] =
                'UPDATE material_types SET ' .
                'material_product_group_id=' . $numberOrNull($p->material_product_group_id) . ', ' .
                'material_product_type_id=' . $numberOrNull($p->material_product_type_id) . ', ' .
                'material_group=' . $quote($p->material_group) . ', ' .
                'material_type_code=' . $quote($p->material_type_code) . ', ' .
                'catalogue_source_code=' . $quote($p->catalogue_source_code) . ', ' .
                'inventory_type=' . $quote($p->inventory_type) . ', ' .
                'master_status=' . $quote($p->master_status) . ', ' .
                'unit_master_id=' . $numberOrNull($p->unit_master_id) . ', ' .
                'sequence=' . $numberOrNull($p->sequence) . ', ' .
                'is_active=' . $numberOrNull($p->is_active) . ', ' .
                'updated_at=NOW() ' .
                'WHERE id=' . (int) $production['id'] .
                ' AND material_type_code=' . $quote($production['code']) .
                ' AND material_type_name=' . $quote($production['name']) .
                ' LIMIT 1;';
        }

        $sql[] = '';
        $sql[] = '-- ---------------------------------------------------------';
        $sql[] = '-- INSERT 9037 GENUINELY NEW PRODUCTS';
        $sql[] = '-- Production generates new IDs automatically.';
        $sql[] = '-- ---------------------------------------------------------';

        foreach ($newProducts as $p) {
            $sql[] =
                'INSERT INTO material_types (' .
                'material_product_group_id,' .
                'material_product_type_id,' .
                'material_group,' .
                'material_type_name,' .
                'material_type_code,' .
                'catalogue_source_code,' .
                'inventory_type,' .
                'master_status,' .
                'is_legacy,' .
                'unit_master_id,' .
                'sequence,' .
                'is_active,' .
                'remarks,' .
                'created_by,' .
                'created_at,' .
                'updated_at' .
                ') VALUES (' .
                $numberOrNull($p->material_product_group_id) . ',' .
                $numberOrNull($p->material_product_type_id) . ',' .
                $quote($p->material_group) . ',' .
                $quote($p->material_type_name) . ',' .
                $quote($p->material_type_code) . ',' .
                $quote($p->catalogue_source_code) . ',' .
                $quote($p->inventory_type) . ',' .
                $quote($p->master_status) . ',' .
                $numberOrNull($p->is_legacy) . ',' .
                $numberOrNull($p->unit_master_id) . ',' .
                $numberOrNull($p->sequence) . ',' .
                $numberOrNull($p->is_active) . ',' .
                $quote($p->remarks) . ',' .
                'NULL,' .
                'NOW(),' .
                'NOW()' .
                ');';
        }

        $sql[] = '';
        $sql[] = '-- ---------------------------------------------------------';
        $sql[] = '-- FINAL SAFETY CHECK';
        $sql[] = '-- Expected: 3614 + 9037 = 12651 production products.';
        $sql[] = '-- General Cleaning Sponge already exists separately.';
        $sql[] = '-- ---------------------------------------------------------';
        $sql[] = 'SET @ravion_product_count_after := (SELECT COUNT(*) FROM material_types);';
        $sql[] = '';
        $sql[] = 'SELECT @ravion_product_count_before AS products_before,';
        $sql[] = '       @ravion_product_count_after AS products_after;';
        $sql[] = '';
        $sql[] = '-- IMPORTANT: COMMIT intentionally omitted.';
        $sql[] = '-- Production execution will be performed under controlled review.';
        $sql[] = '-- Do not add COMMIT manually before validation.';
        $sql[] = '';

        file_put_contents($sqlPath, implode(PHP_EOL, $sql));

        /*
        |--------------------------------------------------------------------------
        | Human-readable report
        |--------------------------------------------------------------------------
        */

        $report = [];

        $report[] = 'RAVION FINAL MATERIAL CATALOGUE TRANSFER';
        $report[] = '=======================================';
        $report[] = '';
        $report[] = 'Production snapshot rows : ' . count($productionRows);
        $report[] = 'Finalized catalogue rows : ' . $catalogue->count();
        $report[] = 'Exact code + name matches : ' . count($exactMatches);
        $report[] = 'Unique name matches       : ' . count($nameMatches);
        $report[] = 'New products              : ' . count($newProducts);
        $report[] = 'Conflicts                 : ' . count($conflicts);
        $report[] = '';
        $report[] = 'Expected production total after transfer: 12651';
        $report[] = '';
        $report[] = 'Existing production IDs are preserved.';
        $report[] = 'Unmatched production products are untouched.';
        $report[] = 'New products receive production-generated IDs.';
        $report[] = 'General Cleaning Sponge is excluded from catalogue transfer.';
        $report[] = 'SQL does NOT contain COMMIT.';

        file_put_contents($reportPath, implode(PHP_EOL, $report));

        $this->newLine();
        $this->info('Final Material Catalogue transfer package generated.');
        $this->newLine();

        $this->table(
            ['Check', 'Result'],
            [
                ['Production snapshot', count($productionRows)],
                ['Final catalogue', $catalogue->count()],
                ['Exact matches', count($exactMatches)],
                ['Name matches', count($nameMatches)],
                ['New products', count($newProducts)],
                ['Conflicts', count($conflicts)],
            ]
        );

        $this->newLine();
        $this->line('SQL:');
        $this->line($sqlPath);
        $this->newLine();
        $this->line('Report:');
        $this->line($reportPath);

        return self::SUCCESS;
    }
}