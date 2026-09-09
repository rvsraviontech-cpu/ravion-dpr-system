<?php

namespace App\Console\Commands;

use App\Models\MaterialCatalogItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ReviewMaterialCatalog extends Command
{
    protected $signature = 'materials:catalog-review
                            {--status=all : all, exact_match, probable_variant, new_candidate, review}
                            {--limit=0 : Maximum number of detail rows; 0 means all}';

    protected $description = 'Export the analysed Material Catalogue staging rows to CSV review files.';

    public function handle(): int
    {
        if (MaterialCatalogItem::query()->doesntExist()) {
            $this->error(
                'No staged catalogue items found. Run materials:catalog-import first.'
            );

            return self::FAILURE;
        }

        $allowedStatuses = [
            'all',
            MaterialCatalogItem::STATUS_EXACT_MATCH,
            MaterialCatalogItem::STATUS_PROBABLE_VARIANT,
            MaterialCatalogItem::STATUS_NEW_CANDIDATE,
            MaterialCatalogItem::STATUS_REVIEW,
        ];

        $status = (string) $this->option('status');

        if (! in_array($status, $allowedStatuses, true)) {
            $this->error(
                'Invalid --status. Allowed: '.implode(', ', $allowedStatuses)
            );

            return self::FAILURE;
        }

        $limit = max(0, (int) $this->option('limit'));

        $directory = storage_path('app/material-catalog-review');
        File::ensureDirectoryExists($directory);

        $timestamp = now()->format('Ymd_His');

        $detailPath = $directory
            .DIRECTORY_SEPARATOR
            ."material_catalog_review_{$timestamp}.csv";

        $summaryPath = $directory
            .DIRECTORY_SEPARATOR
            ."material_catalog_summary_{$timestamp}.csv";

        $duplicatePath = $directory
            .DIRECTORY_SEPARATOR
            ."material_catalog_duplicate_names_{$timestamp}.csv";

        $query = MaterialCatalogItem::query()
            ->with([
                'category:id,code,name',
                'subcategory:id,material_catalog_category_id,code,name',
                'matchedMaterialType:id,material_group,material_type_name,material_type_code,unit_master_id',
                'suggestedUnit:id,unit_name,unit_code,symbol',
            ])
            ->where('is_active', true)
            ->orderBy('normalized_name')
            ->orderBy('material_catalog_category_id')
            ->orderBy('material_catalog_subcategory_id')
            ->orderBy('source_sequence');

        if ($status !== 'all') {
            $query->where('match_status', $status);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->warn('No catalogue rows matched the requested review filter.');

            return self::SUCCESS;
        }

        $this->writeDetailCsv($detailPath, $items);
        $this->writeSummaryCsv($summaryPath);
        $this->writeDuplicateCsv($duplicatePath);

        $counts = MaterialCatalogItem::query()
            ->where('is_active', true)
            ->selectRaw('match_status, COUNT(*) as row_count')
            ->groupBy('match_status')
            ->pluck('row_count', 'match_status');

        $this->newLine();
        $this->info('Material Catalogue review files created successfully.');
        $this->newLine();

        $this->table(
            ['Analysis Result', 'Rows'],
            [
                [
                    'Exact existing matches',
                    (int) ($counts[MaterialCatalogItem::STATUS_EXACT_MATCH] ?? 0),
                ],
                [
                    'Probable variants / related existing products',
                    (int) ($counts[MaterialCatalogItem::STATUS_PROBABLE_VARIANT] ?? 0),
                ],
                [
                    'New product candidates',
                    (int) ($counts[MaterialCatalogItem::STATUS_NEW_CANDIDATE] ?? 0),
                ],
                [
                    'Manual review',
                    (int) ($counts[MaterialCatalogItem::STATUS_REVIEW] ?? 0),
                ],
            ]
        );

        $this->line('Detail review:');
        $this->line($detailPath);

        $this->newLine();
        $this->line('Summary:');
        $this->line($summaryPath);

        $this->newLine();
        $this->line('Repeated catalogue names across categories/families:');
        $this->line($duplicatePath);

        $this->newLine();
        $this->comment(
            'Review/export only: no Material Types, units, specifications, '
            .'grades, or catalogue mapping pivots were modified.'
        );

        return self::SUCCESS;
    }

    private function writeDetailCsv(
        string $path,
        Collection $items
    ): void {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException("Unable to create {$path}");
        }

        // UTF-8 BOM makes the file open cleanly in Microsoft Excel on Windows.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Staging ID',
            'Category Code',
            'Category',
            'Subcategory Code',
            'Subcategory / Family',
            'Source Item',
            'Normalized Name',
            'Suggested Base Product',
            'Variant / Remaining Text',
            'Suggested Unit',
            'Match Status',
            'Match Confidence',
            'Existing Material Type ID',
            'Existing Material Group',
            'Existing Material Type',
            'Existing Material Code',
            'Analysis Notes',
        ]);

        foreach ($items as $item) {
            fputcsv($handle, [
                $item->id,
                $item->category?->code,
                $item->category?->name,
                $item->subcategory?->code,
                $item->subcategory?->name,
                $item->source_item_name,
                $item->normalized_name,
                $item->suggested_base_name,
                $item->variant_text,
                $item->suggestedUnit?->unit_code,
                $item->match_status,
                $item->match_confidence,
                $item->matched_material_type_id,
                $item->matchedMaterialType?->material_group,
                $item->matchedMaterialType?->material_type_name,
                $item->matchedMaterialType?->material_type_code,
                $item->analysis_notes,
            ]);
        }

        fclose($handle);
    }

    private function writeSummaryCsv(string $path): void
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException("Unable to create {$path}");
        }

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Match Status',
            'Rows',
            'Unique Normalized Names',
            'With Suggested Unit',
            'Without Suggested Unit',
        ]);

        $summary = MaterialCatalogItem::query()
            ->where('is_active', true)
            ->selectRaw(
                'match_status,
                 COUNT(*) as row_count,
                 COUNT(DISTINCT normalized_name) as unique_count,
                 SUM(CASE WHEN suggested_unit_master_id IS NOT NULL THEN 1 ELSE 0 END) as with_unit,
                 SUM(CASE WHEN suggested_unit_master_id IS NULL THEN 1 ELSE 0 END) as without_unit'
            )
            ->groupBy('match_status')
            ->orderBy('match_status')
            ->get();

        foreach ($summary as $row) {
            fputcsv($handle, [
                $row->match_status,
                $row->row_count,
                $row->unique_count,
                $row->with_unit,
                $row->without_unit,
            ]);
        }

        fclose($handle);
    }

    private function writeDuplicateCsv(string $path): void
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException("Unable to create {$path}");
        }

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Normalized Name',
            'Occurrences',
            'Source Names',
            'Categories',
            'Subcategories / Families',
            'Statuses',
        ]);

        $duplicates = MaterialCatalogItem::query()
            ->where('is_active', true)
            ->select([
                'normalized_name',
                DB::raw('COUNT(*) as occurrences'),
            ])
            ->groupBy('normalized_name')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('occurrences')
            ->orderBy('normalized_name')
            ->get();

        foreach ($duplicates as $duplicate) {
            $rows = MaterialCatalogItem::query()
                ->with([
                    'category:id,name',
                    'subcategory:id,name',
                ])
                ->where('is_active', true)
                ->where('normalized_name', $duplicate->normalized_name)
                ->get();

            fputcsv($handle, [
                $duplicate->normalized_name,
                $duplicate->occurrences,
                $rows->pluck('source_item_name')->unique()->implode(' | '),
                $rows->pluck('category.name')->filter()->unique()->implode(' | '),
                $rows->pluck('subcategory.name')->filter()->unique()->implode(' | '),
                $rows->pluck('match_status')->unique()->implode(' | '),
            ]);
        }

        fclose($handle);
    }
}
