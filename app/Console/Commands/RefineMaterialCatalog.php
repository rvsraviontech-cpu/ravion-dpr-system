<?php

namespace App\Console\Commands;

use App\Models\MaterialCatalogItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RefineMaterialCatalog extends Command
{
    protected $signature = 'materials:catalog-refine
                            {--limit=0 : Maximum staging rows to analyse; 0 means all}';

    protected $description = 'Classify catalogue staging rows into existing bases, new bases, base-plus-variant candidates, and true manual review.';

    public function handle(): int
    {
        if (MaterialCatalogItem::query()->doesntExist()) {
            $this->error('No staged catalogue rows found.');

            return self::FAILURE;
        }

        $limit = max(0, (int) $this->option('limit'));

        $query = MaterialCatalogItem::query()
            ->with([
                'category:id,code,name',
                'subcategory:id,material_catalog_category_id,code,name',
                'matchedMaterialType:id,material_group,material_type_name,material_type_code,unit_master_id',
                'suggestedUnit:id,unit_name,unit_code,symbol',
            ])
            ->where('is_active', true)
            ->orderBy('material_catalog_category_id')
            ->orderBy('material_catalog_subcategory_id')
            ->orderBy('source_sequence');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $items = $query->get();

        $rows = $items->map(fn (MaterialCatalogItem $item): array => $this->classify($item));

        $directory = storage_path('app/material-catalog-review');
        File::ensureDirectoryExists($directory);
        $timestamp = now()->format('Ymd_His');

        $detailPath = $directory.DIRECTORY_SEPARATOR."material_catalog_refined_{$timestamp}.csv";
        $basePath = $directory.DIRECTORY_SEPARATOR."material_catalog_refined_base_products_{$timestamp}.csv";
        $variantPath = $directory.DIRECTORY_SEPARATOR."material_catalog_refined_variants_{$timestamp}.csv";
        $reviewPath = $directory.DIRECTORY_SEPARATOR."material_catalog_refined_manual_review_{$timestamp}.csv";

        $this->writeDetails($detailPath, $rows);
        $this->writeBases($basePath, $rows);
        $this->writeVariants($variantPath, $rows);
        $this->writeReviews($reviewPath, $rows);

        $this->newLine();
        $this->info('Material Catalogue refinement review created successfully.');

        $this->table(
            ['Classification', 'Rows', 'Unique proposed bases'],
            collect([
                'EXISTING_BASE',
                'NEW_BASE',
                'BASE_PLUS_VARIANT',
                'MANUAL_REVIEW',
            ])->map(function (string $classification) use ($rows): array {
                $subset = $rows->where('classification', $classification);

                return [
                    $classification,
                    $subset->count(),
                    $subset->pluck('base_key')->filter()->unique()->count(),
                ];
            })->all()
        );

        $this->newLine();
        $this->line('Complete refined catalogue:');
        $this->line($detailPath);
        $this->newLine();
        $this->line('Base-product review:');
        $this->line($basePath);
        $this->newLine();
        $this->line('Variant review:');
        $this->line($variantPath);
        $this->newLine();
        $this->line('True manual-review queue:');
        $this->line($reviewPath);

        $this->newLine();
        $this->comment(
            'Refinement only: no Material Types, Material Variants, specifications, '
            .'grades, brands, units, or catalogue pivots were modified.'
        );

        return self::SUCCESS;
    }

    private function classify(MaterialCatalogItem $item): array
    {
        $source = $this->clean($item->source_item_name);

        if ($item->matchedMaterialType) {
            $base = $item->matchedMaterialType->material_type_name;
            $attributes = $this->extractAttributes(
                $source,
                $base,
                $item->variant_text
            );

            $hasVariant = $this->hasVariant($attributes);

            return $this->row(
                $item,
                $hasVariant ? 'BASE_PLUS_VARIANT' : 'EXISTING_BASE',
                $base,
                $attributes,
                $hasVariant
                    ? 'Existing Material Type with variant/specification attributes.'
                    : 'Existing Material Type match.',
                $hasVariant ? 95 : 100
            );
        }

        $attributes = $this->extractAttributes(
            $source,
            null,
            $item->variant_text
        );

        $hasVariant = $this->hasVariant($attributes);

        if ($hasVariant && filled($attributes['base_name'])) {
            return $this->row(
                $item,
                'BASE_PLUS_VARIANT',
                $attributes['base_name'],
                $attributes,
                'Construction-specific variant attributes extracted conservatively.',
                $attributes['confidence']
            );
        }

        if ($this->isAmbiguous($source)) {
            return $this->row(
                $item,
                'MANUAL_REVIEW',
                $source,
                $attributes,
                'Description is too generic or ambiguous for safe automatic master creation.',
                25
            );
        }

        return $this->row(
            $item,
            'NEW_BASE',
            $source,
            $attributes,
            'Valid standalone catalogue product; no variant is required.',
            80
        );
    }

    private function extractAttributes(
        string $source,
        ?string $knownBase = null,
        ?string $existingVariant = null
    ): array {
        $working = $existingVariant
            ? $this->clean($existingVariant)
            : $source;

        if ($knownBase && ! $existingVariant) {
            $working = $this->removeFirst($working, $knownBase);
        }

        $working = trim($working, " \t\n\r\0\x0B-–—,:;/|()[]");

        $attributes = [
            'specification' => null,
            'size_dimension' => null,
            'grade_class' => null,
            'finish' => null,
            'colour_shade' => null,
            'capacity_rating' => null,
            'packaging' => null,
        ];

        $attributes['size_dimension'] = $this->first($working, [
            '/\b\d+(?:\.\d+)?\s*(?:mm|cm|m|mtr|meter|metre|inch|inches|ft|feet)\b(?:\s*[x×]\s*\d+(?:\.\d+)?\s*(?:mm|cm|m|mtr|meter|metre|inch|inches|ft|feet)\b){0,2}/iu',
            '/\b\d+(?:\.\d+)?\s*[x×]\s*\d+(?:\.\d+)?(?:\s*[x×]\s*\d+(?:\.\d+)?)?\s*(?:mm|cm|m|mtr|meter|metre|inch|inches|ft|feet)?\b/iu',
            '/\b\d+(?:\.\d+)?\s*(?:nb|dia|diameter|thick|thickness)\b/iu',
        ]);

        $attributes['grade_class'] = $this->first($working, [
            '/\bfe\s*\d+[a-z]?\b/iu',
            '/\bm\s*\d+\b/iu',
            '/\b(?:grade|class|schedule|sch|sdr|pn|duty)\s*[-:]?\s*[a-z0-9.\-\/]+\b/iu',
            '/\b(?:light|medium|heavy)\s+duty\b/iu',
        ]);

        $attributes['capacity_rating'] = $this->first($working, [
            '/\b\d+(?:\.\d+)?\s*(?:w|kw|mw|hp|va|kva|v|kv|amp|amps|a|mah|ah|bar|psi|ton|tr)\b/iu',
            '/\b\d+(?:\.\d+)?\s*(?:ltr|litre|litres|ml|kg|gm|g)\b/iu',
        ]);

        $attributes['finish'] = $this->first($working, [
            '/\b(?:polished|matte|matt|glossy|satin|textured|brushed|mirror|natural|honed|flamed|antique|powder\s*coated|galvanized|galvanised|chrome|chromed)\b/iu',
            '/\bfinish\s*[-:]?\s*[a-z0-9][a-z0-9 \-\/]+\b/iu',
        ]);

        $attributes['colour_shade'] = $this->first($working, [
            '/\b(?:colour|color|shade)\s*[-:]?\s*[a-z0-9][a-z0-9 \-\/]+\b/iu',
        ]);

        $attributes['packaging'] = $this->first($working, [
            '/\b\d+(?:\.\d+)?\s*(?:kg|gm|g|ltr|litre|litres|ml)\s*(?:bag|pack|packet|drum|can|bucket|tin|box)?\b/iu',
            '/\b(?:bag|pack|packet|drum|can|bucket|tin|box)\s+of\s+\d+(?:\.\d+)?\b/iu',
        ]);

        $attributes['specification'] = $this->first($working, [
            '/\b(?:type|series|model)\s*[-:]?\s*[a-z0-9][a-z0-9 .\-\/]+\b/iu',
            '/\b(?:opc|ppc|psc)\s*(?:\d{2,3})?\b/iu',
        ]);

        $found = collect($attributes)
            ->filter(fn ($value) => filled($value))
            ->values()
            ->all();

        $base = $knownBase;

        if (! $base && ! empty($found)) {
            $base = $source;

            usort(
                $found,
                fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a)
            );

            foreach ($found as $value) {
                $base = $this->removeFirst($base, $value);
            }

            $base = $this->cleanupBase($base);
        }

        $attributes['base_name'] = $base;
        $attributes['raw_variant_text'] = $working !== '' ? $working : null;
        $attributes['confidence'] = $knownBase
            ? 95
            : (! empty($found) && filled($base) ? 78 : 0);

        return $attributes;
    }

    private function row(
        MaterialCatalogItem $item,
        string $classification,
        string $baseName,
        array $attributes,
        string $reason,
        int $confidence
    ): array {
        $variantName = collect([
            $attributes['specification'],
            $attributes['size_dimension'],
            $attributes['grade_class'],
            $attributes['capacity_rating'],
            $attributes['finish'],
            $attributes['colour_shade'],
            $attributes['packaging'],
        ])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => $this->clean((string) $value))
            ->unique(fn ($value) => $this->normalize($value))
            ->implode(' — ');

        return [
            'staging_id' => $item->id,
            'category_code' => $item->category?->code,
            'category_name' => $item->category?->name,
            'subcategory_code' => $item->subcategory?->code,
            'subcategory_name' => $item->subcategory?->name,
            'source_item' => $item->source_item_name,
            'match_status' => $item->match_status,
            'classification' => $classification,
            'base_name' => $this->clean($baseName),
            'base_key' => $this->normalize($baseName),
            'existing_material_type_id' => $item->matchedMaterialType?->id,
            'existing_material_type_code' => $item->matchedMaterialType?->material_type_code,
            'variant_name' => $variantName !== '' ? $variantName : null,
            'specification' => $attributes['specification'],
            'size_dimension' => $attributes['size_dimension'],
            'grade_class' => $attributes['grade_class'],
            'capacity_rating' => $attributes['capacity_rating'],
            'finish' => $attributes['finish'],
            'colour_shade' => $attributes['colour_shade'],
            'packaging' => $attributes['packaging'],
            'suggested_unit' => $item->suggestedUnit?->unit_code,
            'search_alias' => $item->source_item_name,
            'confidence' => $confidence,
            'reason' => $reason,
        ];
    }

    private function hasVariant(array $attributes): bool
    {
        return collect([
            $attributes['specification'],
            $attributes['size_dimension'],
            $attributes['grade_class'],
            $attributes['capacity_rating'],
            $attributes['finish'],
            $attributes['colour_shade'],
            $attributes['packaging'],
        ])->filter(fn ($value) => filled($value))->isNotEmpty();
    }

    private function isAmbiguous(string $source): bool
    {
        $normalized = $this->normalize($source);

        $generic = [
            'other',
            'others',
            'misc',
            'miscellaneous',
            'material',
            'materials',
            'item',
            'items',
            'accessory',
            'accessories',
            'fitting',
            'fittings',
            'consumable',
            'consumables',
            'hardware',
            'spares',
            'spare',
            'general',
        ];

        if (in_array($normalized, $generic, true)) {
            return true;
        }

        return mb_strlen($normalized) < 3;
    }

    private function first(string $value, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches)) {
                return $this->clean($matches[0]);
            }
        }

        return null;
    }

    private function removeFirst(string $subject, string $needle): string
    {
        if ($needle === '') {
            return $subject;
        }

        return preg_replace(
            '/'.preg_quote($needle, '/').'/iu',
            '',
            $subject,
            1
        ) ?? $subject;
    }

    private function cleanupBase(string $base): ?string
    {
        $base = preg_replace('/\s+/u', ' ', $base) ?? $base;
        $base = trim($base, " \t\n\r\0\x0B-–—,:;/|()[]");

        // Remove dangling connector words only at the end.
        $base = preg_replace(
            '/\s+(?:size|dia|diameter|thickness|thick|grade|class|finish|shade|colour|color|capacity|rating)$/iu',
            '',
            $base
        ) ?? $base;

        $base = trim($base, " \t\n\r\0\x0B-–—,:;/|()[]");

        return mb_strlen($base) >= 3 ? $base : null;
    }

    private function clean(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
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

    private function openCsv(string $path)
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException("Unable to create {$path}");
        }

        fwrite($handle, "\xEF\xBB\xBF");

        return $handle;
    }

    private function writeDetails(string $path, Collection $rows): void
    {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Staging ID',
            'Category Code',
            'Category',
            'Subcategory Code',
            'Subcategory / Family',
            'Source Item',
            'Original Match Status',
            'Classification',
            'Existing Material Type ID',
            'Existing Material Code',
            'Proposed Base Product',
            'Variant Name',
            'Specification / Type',
            'Size / Dimension',
            'Grade / Class',
            'Capacity / Rating',
            'Finish',
            'Colour / Shade',
            'Packaging',
            'Suggested Unit',
            'Search Alias',
            'Confidence',
            'Reason',
        ]);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['staging_id'],
                $row['category_code'],
                $row['category_name'],
                $row['subcategory_code'],
                $row['subcategory_name'],
                $row['source_item'],
                $row['match_status'],
                $row['classification'],
                $row['existing_material_type_id'],
                $row['existing_material_type_code'],
                $row['base_name'],
                $row['variant_name'],
                $row['specification'],
                $row['size_dimension'],
                $row['grade_class'],
                $row['capacity_rating'],
                $row['finish'],
                $row['colour_shade'],
                $row['packaging'],
                $row['suggested_unit'],
                $row['search_alias'],
                $row['confidence'],
                $row['reason'],
            ]);
        }

        fclose($handle);
    }

    private function writeBases(string $path, Collection $rows): void
    {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Classification',
            'Proposed Base Product',
            'Existing Material Type ID',
            'Existing Material Code',
            'Source Rows',
            'Unique Source Names',
            'Categories',
            'Subcategories / Families',
            'Variant Rows',
            'Suggested Units',
        ]);

        $groups = $rows
            ->whereIn('classification', [
                'EXISTING_BASE',
                'NEW_BASE',
                'BASE_PLUS_VARIANT',
            ])
            ->groupBy('base_key')
            ->sortByDesc(fn (Collection $group): int => $group->count());

        foreach ($groups as $group) {
            $first = $group->first();

            fputcsv($handle, [
                $group->pluck('classification')->unique()->implode(' | '),
                $first['base_name'],
                $group->pluck('existing_material_type_id')->filter()->unique()->implode(' | '),
                $group->pluck('existing_material_type_code')->filter()->unique()->implode(' | '),
                $group->count(),
                $group->pluck('source_item')->unique()->count(),
                $group->pluck('category_name')->filter()->unique()->implode(' | '),
                $group->pluck('subcategory_name')->filter()->unique()->implode(' | '),
                $group->where('classification', 'BASE_PLUS_VARIANT')->count(),
                $group->pluck('suggested_unit')->filter()->unique()->implode(' | '),
            ]);
        }

        fclose($handle);
    }

    private function writeVariants(string $path, Collection $rows): void
    {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Base Product',
            'Existing Material Type ID',
            'Variant Name',
            'Specification / Type',
            'Size / Dimension',
            'Grade / Class',
            'Capacity / Rating',
            'Finish',
            'Colour / Shade',
            'Packaging',
            'Suggested Unit',
            'Source Item',
            'Category',
            'Subcategory / Family',
            'Confidence',
        ]);

        foreach ($rows->where('classification', 'BASE_PLUS_VARIANT') as $row) {
            fputcsv($handle, [
                $row['base_name'],
                $row['existing_material_type_id'],
                $row['variant_name'],
                $row['specification'],
                $row['size_dimension'],
                $row['grade_class'],
                $row['capacity_rating'],
                $row['finish'],
                $row['colour_shade'],
                $row['packaging'],
                $row['suggested_unit'],
                $row['source_item'],
                $row['category_name'],
                $row['subcategory_name'],
                $row['confidence'],
            ]);
        }

        fclose($handle);
    }

    private function writeReviews(string $path, Collection $rows): void
    {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Staging ID',
            'Source Item',
            'Category',
            'Subcategory / Family',
            'Proposed Base Product',
            'Suggested Unit',
            'Confidence',
            'Reason',
        ]);

        foreach ($rows->where('classification', 'MANUAL_REVIEW') as $row) {
            fputcsv($handle, [
                $row['staging_id'],
                $row['source_item'],
                $row['category_name'],
                $row['subcategory_name'],
                $row['base_name'],
                $row['suggested_unit'],
                $row['confidence'],
                $row['reason'],
            ]);
        }

        fclose($handle);
    }
}
