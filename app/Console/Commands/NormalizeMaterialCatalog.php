<?php

namespace App\Console\Commands;

use App\Models\MaterialCatalogItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NormalizeMaterialCatalog extends Command
{
    protected $signature = 'materials:catalog-normalize
                            {--min-group=2 : Minimum rows required to create a grouped candidate}
                            {--status=all : all, probable_variant, new_candidate}';

    protected $description = 'Generate safe base-product and variant normalization candidates from staged catalogue items.';

    public function handle(): int
    {
        if (MaterialCatalogItem::query()->doesntExist()) {
            $this->error(
                'No staged catalogue items found. Run materials:catalog-import and '
                .'materials:catalog-analyse first.'
            );

            return self::FAILURE;
        }

        $status = (string) $this->option('status');
        $allowed = [
            'all',
            MaterialCatalogItem::STATUS_PROBABLE_VARIANT,
            MaterialCatalogItem::STATUS_NEW_CANDIDATE,
        ];

        if (! in_array($status, $allowed, true)) {
            $this->error(
                'Invalid --status. Allowed: '.implode(', ', $allowed)
            );

            return self::FAILURE;
        }

        $minGroup = max(2, (int) $this->option('min-group'));

        $query = MaterialCatalogItem::query()
            ->with([
                'category:id,code,name',
                'subcategory:id,material_catalog_category_id,code,name',
                'matchedMaterialType:id,material_type_name,material_type_code,unit_master_id',
                'suggestedUnit:id,unit_name,unit_code,symbol',
            ])
            ->where('is_active', true)
            ->whereIn('match_status', [
                MaterialCatalogItem::STATUS_PROBABLE_VARIANT,
                MaterialCatalogItem::STATUS_NEW_CANDIDATE,
            ])
            ->orderBy('normalized_name');

        if ($status !== 'all') {
            $query->where('match_status', $status);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->warn('No catalogue rows matched the requested filter.');

            return self::SUCCESS;
        }

        $candidates = $items->map(function (MaterialCatalogItem $item): array {
            [$baseName, $variantText, $rule, $confidence] =
                $this->proposeNormalization($item);

            return [
                'item' => $item,
                'base_name' => $baseName,
                'base_key' => $this->normalize($baseName),
                'variant_text' => $variantText,
                'rule' => $rule,
                'confidence' => $confidence,
            ];
        });

        $groups = $candidates
            ->groupBy('base_key')
            ->filter(fn (Collection $group): bool => $group->count() >= $minGroup)
            ->sortByDesc(fn (Collection $group): int => $group->count());

        $singletons = $candidates
            ->groupBy('base_key')
            ->filter(fn (Collection $group): bool => $group->count() < $minGroup);

        $directory = storage_path('app/material-catalog-review');
        File::ensureDirectoryExists($directory);

        $timestamp = now()->format('Ymd_His');

        $groupPath = $directory.DIRECTORY_SEPARATOR
            ."material_catalog_normalization_groups_{$timestamp}.csv";

        $detailPath = $directory.DIRECTORY_SEPARATOR
            ."material_catalog_normalization_details_{$timestamp}.csv";

        $singletonPath = $directory.DIRECTORY_SEPARATOR
            ."material_catalog_normalization_singletons_{$timestamp}.csv";

        $this->writeGroups($groupPath, $groups);
        $this->writeDetails($detailPath, $candidates);
        $this->writeSingletons($singletonPath, $singletons);

        $this->newLine();
        $this->info('Material Catalogue normalization review created successfully.');

        $this->table(
            ['Measure', 'Count'],
            [
                ['Analysed staging rows', $items->count()],
                ['Proposed base-product groups', $groups->count()],
                [
                    'Rows inside grouped candidates',
                    $groups->sum(fn (Collection $group): int => $group->count()),
                ],
                ['Singleton proposed products', $singletons->count()],
            ]
        );

        $this->newLine();
        $this->line('Grouped base-product candidates:');
        $this->line($groupPath);

        $this->newLine();
        $this->line('Complete normalization details:');
        $this->line($detailPath);

        $this->newLine();
        $this->line('Singleton candidates:');
        $this->line($singletonPath);

        $this->newLine();
        $this->comment(
            'Normalization is review-only. No Material Types, specifications, grades, '
            .'units, or catalogue mappings were modified.'
        );

        return self::SUCCESS;
    }

    private function proposeNormalization(MaterialCatalogItem $item): array
    {
        // Existing probable variants are the safest groups because the
        // analyser has already linked them to a real Material Type.
        if (
            $item->match_status === MaterialCatalogItem::STATUS_PROBABLE_VARIANT
            && $item->matchedMaterialType
        ) {
            return [
                $item->matchedMaterialType->material_type_name,
                $item->variant_text ?: $this->extractVariant(
                    $item->source_item_name,
                    $item->matchedMaterialType->material_type_name
                ),
                'existing_material_type',
                95,
            ];
        }

        $source = $this->cleanDisplayName($item->source_item_name);

        // Strip common trailing variant descriptors conservatively.
        // The stripped text is kept as variant_text, never discarded.
        $patterns = [
            // Metric sizes / thickness / diameter / length.
            '/(?:\s*[-–—,]\s*|\s+)(\d+(?:\.\d+)?\s*(?:mm|cm|mtr|meter|metre|micron|microns)\b.*)$/iu',

            // Imperial dimensions.
            '/(?:\s*[-–—,]\s*|\s+)(\d+(?:\.\d+)?\s*(?:inch|inches|ft|feet)\b.*)$/iu',

            // Grade / class / schedule / SDR / PN.
            '/(?:\s*[-–—,]\s*|\s+)((?:grade|class|schedule|sch|sdr|pn)\s*[-:]?\s*[a-z0-9.\-\/]+.*)$/iu',

            // Common steel/concrete grade tokens such as Fe500, Fe500D, M20.
            '/(?:\s*[-–—,]\s*|\s+)((?:fe|m)\s*\d+[a-z]?\b.*)$/iu',

            // Packaging at the end.
            '/(?:\s*[-–—,]\s*|\s+)(\d+(?:\.\d+)?\s*(?:kg|gm|g|ltr|litre|litres|ml)\s*(?:bag|pack|drum|can|bucket|tin)?\b.*)$/iu',

            // Colour/finish descriptors when explicitly introduced.
            '/(?:\s*[-–—,]\s*|\s+)((?:colour|color|finish|shade)\s*[-:]?\s*.+)$/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
                $fullMatch = $matches[0][0];
                $offset = $matches[0][1];
                $variant = trim(
                    $matches[1][0] ?? $fullMatch,
                    " \t\n\r\0\x0B-–—,"
                );

                $base = trim(
                    mb_substr($source, 0, $offset),
                    " \t\n\r\0\x0B-–—,"
                );

                if (
                    $base !== ''
                    && mb_strlen($base) >= 3
                    && $variant !== ''
                ) {
                    return [
                        $base,
                        $variant,
                        'trailing_variant_pattern',
                        78,
                    ];
                }
            }
        }

        // Handle explicit "X - Y" descriptions only when the right-hand
        // side looks like a specification rather than a different product.
        $parts = preg_split('/\s+[-–—]\s+/u', $source, 2);

        if (is_array($parts) && count($parts) === 2) {
            [$left, $right] = array_map('trim', $parts);

            if (
                $left !== ''
                && $right !== ''
                && $this->looksLikeVariant($right)
            ) {
                return [
                    $left,
                    $right,
                    'dash_variant_pattern',
                    75,
                ];
            }
        }

        // Do not aggressively remove descriptive words. A false merge is
        // more damaging to inventory than leaving a candidate as a singleton.
        return [
            $source,
            null,
            'no_safe_normalization',
            0,
        ];
    }

    private function looksLikeVariant(string $value): bool
    {
        $normalized = $this->normalize($value);

        return (bool) preg_match(
            '/\b(?:'
            .'grade|class|schedule|sch|sdr|pn|'
            .'mm|cm|inch|inches|ft|feet|'
            .'kg|gm|ltr|litre|litres|ml|'
            .'fe\d+|m\d+|'
            .'colour|color|finish|shade'
            .')\b/iu',
            $normalized
        );
    }

    private function extractVariant(
        string $sourceName,
        string $baseName
    ): ?string {
        $pattern = '/'.preg_quote($baseName, '/').'/iu';
        $variant = preg_replace($pattern, '', $sourceName, 1);
        $variant = trim((string) $variant, " \t\n\r\0\x0B-–—,;/");

        return $variant !== '' ? $variant : null;
    }

    private function writeGroups(
        string $path,
        Collection $groups
    ): void {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Proposed Base Product',
            'Row Count',
            'Unique Source Names',
            'Variants Found',
            'Categories',
            'Subcategories / Families',
            'Suggested Units',
            'Existing Matched Products',
            'Normalization Rules',
            'Minimum Confidence',
            'Maximum Confidence',
        ]);

        foreach ($groups as $group) {
            $first = $group->first();

            fputcsv($handle, [
                $first['base_name'],
                $group->count(),
                $group->pluck('item.source_item_name')->unique()->count(),
                $group->pluck('variant_text')->filter()->unique()->implode(' | '),
                $group->pluck('item.category.name')->filter()->unique()->implode(' | '),
                $group->pluck('item.subcategory.name')->filter()->unique()->implode(' | '),
                $group->pluck('item.suggestedUnit.unit_code')->filter()->unique()->implode(' | '),
                $group->pluck('item.matchedMaterialType.material_type_name')->filter()->unique()->implode(' | '),
                $group->pluck('rule')->unique()->implode(' | '),
                $group->min('confidence'),
                $group->max('confidence'),
            ]);
        }

        fclose($handle);
    }

    private function writeDetails(
        string $path,
        Collection $candidates
    ): void {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Staging ID',
            'Category',
            'Subcategory / Family',
            'Source Item',
            'Match Status',
            'Existing Matched Product',
            'Proposed Base Product',
            'Proposed Variant / Specification',
            'Suggested Unit',
            'Normalization Rule',
            'Normalization Confidence',
        ]);

        foreach ($candidates as $candidate) {
            /** @var MaterialCatalogItem $item */
            $item = $candidate['item'];

            fputcsv($handle, [
                $item->id,
                $item->category?->name,
                $item->subcategory?->name,
                $item->source_item_name,
                $item->match_status,
                $item->matchedMaterialType?->material_type_name,
                $candidate['base_name'],
                $candidate['variant_text'],
                $item->suggestedUnit?->unit_code,
                $candidate['rule'],
                $candidate['confidence'],
            ]);
        }

        fclose($handle);
    }

    private function writeSingletons(
        string $path,
        Collection $singletons
    ): void {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Proposed Base Product',
            'Source Item',
            'Category',
            'Subcategory / Family',
            'Proposed Variant / Specification',
            'Suggested Unit',
            'Match Status',
            'Normalization Rule',
            'Confidence',
        ]);

        foreach ($singletons as $group) {
            $candidate = $group->first();
            /** @var MaterialCatalogItem $item */
            $item = $candidate['item'];

            fputcsv($handle, [
                $candidate['base_name'],
                $item->source_item_name,
                $item->category?->name,
                $item->subcategory?->name,
                $candidate['variant_text'],
                $item->suggestedUnit?->unit_code,
                $item->match_status,
                $candidate['rule'],
                $candidate['confidence'],
            ]);
        }

        fclose($handle);
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

    private function cleanDisplayName(string $value): string
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
}
