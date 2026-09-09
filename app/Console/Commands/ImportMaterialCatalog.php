<?php

namespace App\Console\Commands;

use App\Models\MaterialCatalogCategory;
use App\Models\MaterialCatalogItem;
use App\Models\MaterialCatalogSubcategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ImportMaterialCatalog extends Command
{
    protected $signature = 'materials:catalog-import
                            {--reset : Delete staging catalogue items before importing}';

    protected $description = 'Import the Ravion construction material catalogue into the staging table only.';

    public function handle(): int
    {
        $sourcePath = database_path('data/material_catalog_source.json');

        if (! is_file($sourcePath)) {
            $this->error("Source file not found: {$sourcePath}");

            return self::FAILURE;
        }

        $rows = json_decode(
            file_get_contents($sourcePath),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (! is_array($rows) || empty($rows)) {
            $this->error('The catalogue source file is empty or invalid.');

            return self::FAILURE;
        }

        $categories = MaterialCatalogCategory::query()
            ->get()
            ->keyBy('code');

        $subcategories = MaterialCatalogSubcategory::query()
            ->get()
            ->keyBy('code');

        if ($categories->isEmpty() || $subcategories->isEmpty()) {
            $this->error(
                'Material catalogue categories/subcategories are missing. '
                .'Run MaterialCatalogSeeder first.'
            );

            return self::FAILURE;
        }

        if ($this->option('reset')) {
            if (! $this->confirm(
                'This will delete only material_catalog_items staging rows. Continue?',
                false
            )) {
                $this->warn('Import cancelled.');

                return self::SUCCESS;
            }

            DB::table('material_catalog_items')->delete();
        }

        $processed = 0;
        $missing = 0;

        $this->output->progressStart(count($rows));

        DB::transaction(function () use (
            $rows,
            $categories,
            $subcategories,
            &$processed,
            &$missing
        ): void {
            foreach ($rows as $row) {
                $category = $categories->get($row['category_code'] ?? '');
                $subcategory = $subcategories->get($row['subcategory_code'] ?? '');

                if (
                    ! $category
                    || ! $subcategory
                    || (int) $subcategory->material_catalog_category_id !== (int) $category->id
                ) {
                    $missing++;
                    $this->output->progressAdvance();

                    continue;
                }

                $sourceName = trim((string) ($row['source_item_name'] ?? ''));

                if ($sourceName === '') {
                    $this->output->progressAdvance();

                    continue;
                }

                $normalized = $this->normalize($sourceName);

                $sourceKey = sha1(implode('|', [
                    $row['category_code'],
                    $row['subcategory_code'],
                    $normalized,
                ]));

                MaterialCatalogItem::query()->updateOrCreate(
                    ['source_key' => $sourceKey],
                    [
                        'material_catalog_category_id' => $category->id,
                        'material_catalog_subcategory_id' => $subcategory->id,
                        'source_item_name' => $sourceName,
                        'normalized_name' => $normalized,
                        'suggested_base_name' => null,
                        'variant_text' => null,
                        'suggested_unit_master_id' => null,
                        'matched_material_type_id' => null,
                        'match_status' => MaterialCatalogItem::STATUS_PENDING,
                        'match_confidence' => null,
                        'analysis_notes' => null,
                        'source_sequence' => (int) ($row['source_sequence'] ?? 0),
                        'is_active' => true,
                    ]
                );

                $processed++;
                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();

        $uniqueNames = MaterialCatalogItem::query()
            ->distinct('normalized_name')
            ->count('normalized_name');

        $this->newLine();
        $this->info("Imported/updated staging mappings: {$processed}");
        $this->info('Total staging rows: '.MaterialCatalogItem::query()->count());
        $this->info("Unique normalized source names: {$uniqueNames}");

        if ($missing > 0) {
            $this->warn(
                "{$missing} rows were skipped because their catalogue category/subcategory "
                .'could not be resolved.'
            );
        }

        $this->comment(
            'No rows were inserted into, renamed in, or deleted from material_types.'
        );

        return $missing === 0
            ? self::SUCCESS
            : self::FAILURE;
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = str_replace(
            ['–', '—', '−', '/', '\\', '&', '+'],
            ['-', '-', '-', ' ', ' ', ' and ', ' '],
            $value
        );

        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
