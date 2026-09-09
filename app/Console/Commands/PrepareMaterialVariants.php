<?php

namespace App\Console\Commands;

use App\Models\MaterialCatalogItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PrepareMaterialVariants extends Command
{
    protected $signature = 'materials:variants-prepare
                            {--status=all : all, exact_match, probable_variant, new_candidate, review}
                            {--limit=0 : Maximum number of staging rows to analyse; 0 means all}';

    protected $description = 'Prepare a review-only base-product and material-variant proposal from analysed catalogue staging data.';

    public function handle(): int
    {
        if (MaterialCatalogItem::query()->doesntExist()) {
            $this->error(
                'No staged catalogue rows found. Run materials:catalog-import and '
                .'materials:catalog-analyse first.'
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

        $query = MaterialCatalogItem::query()
            ->with([
                'category:id,code,name',
                'subcategory:id,material_catalog_category_id,code,name',
                'matchedMaterialType:id,material_group,material_type_name,material_type_code,unit_master_id',
                'suggestedUnit:id,unit_name,unit_code,symbol',
            ])
            ->where('is_active', true)
            ->orderBy('normalized_name')
            ->orderBy('source_sequence');

        if ($status !== 'all') {
            $query->where('match_status', $status);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->warn('No catalogue rows matched the requested filter.');

            return self::SUCCESS;
        }

        $prepared = $items->map(function (MaterialCatalogItem $item): array {
            return $this->prepareItem($item);
        });

        $directory = storage_path('app/material-catalog-review');
        File::ensureDirectoryExists($directory);

        $timestamp = now()->format('Ymd_His');

        $detailPath = $directory.DIRECTORY_SEPARATOR
            ."material_variant_preparation_details_{$timestamp}.csv";

        $basePath = $directory.DIRECTORY_SEPARATOR
            ."material_variant_base_products_{$timestamp}.csv";

        $variantPath = $directory.DIRECTORY_SEPARATOR
            ."material_variant_candidates_{$timestamp}.csv";

        $reviewPath = $directory.DIRECTORY_SEPARATOR
            ."material_variant_manual_review_{$timestamp}.csv";

        $this->writeDetailCsv($detailPath, $prepared);
        $this->writeBaseProductCsv($basePath, $prepared);
        $this->writeVariantCsv($variantPath, $prepared);
        $this->writeManualReviewCsv($reviewPath, $prepared);

        $existingBase = $prepared
            ->where('base_action', 'USE_EXISTING')
            ->pluck('base_key')
            ->unique()
            ->count();

        $newBase = $prepared
            ->where('base_action', 'REVIEW_NEW_BASE')
            ->pluck('base_key')
            ->unique()
            ->count();

        $variantCandidates = $prepared
            ->filter(fn (array $row): bool => $row['has_variant'])
            ->count();

        $manualReview = $prepared
            ->where('review_required', true)
            ->count();

        $this->newLine();
        $this->info('Material Variant preparation review created successfully.');

        $this->table(
            ['Measure', 'Count'],
            [
                ['Staging rows analysed', $prepared->count()],
                ['Existing base products referenced', $existingBase],
                ['Proposed new base products', $newBase],
                ['Rows carrying variant attributes', $variantCandidates],
                ['Rows requiring manual review', $manualReview],
            ]
        );

        $this->newLine();
        $this->line('Preparation details:');
        $this->line($detailPath);

        $this->newLine();
        $this->line('Proposed base products:');
        $this->line($basePath);

        $this->newLine();
        $this->line('Variant candidates:');
        $this->line($variantPath);

        $this->newLine();
        $this->line('Manual-review rows:');
        $this->line($reviewPath);

        $this->newLine();
        $this->comment(
            'Preparation only: material_types, material_variants, specifications, grades, '
            .'brands, units, and catalogue pivots were not modified.'
        );

        return self::SUCCESS;
    }

    private function prepareItem(MaterialCatalogItem $item): array
    {
        $source = $this->cleanDisplayName($item->source_item_name);

        $baseName = null;
        $baseAction = 'REVIEW_NEW_BASE';
        $existingMaterialTypeId = null;
        $existingMaterialTypeName = null;
        $existingMaterialTypeCode = null;

        if ($item->matchedMaterialType) {
            $baseName = $item->matchedMaterialType->material_type_name;
            $baseAction = 'USE_EXISTING';
            $existingMaterialTypeId = $item->matchedMaterialType->id;
            $existingMaterialTypeName = $item->matchedMaterialType->material_type_name;
            $existingMaterialTypeCode = $item->matchedMaterialType->material_type_code;
        } elseif ($item->suggested_base_name) {
            $baseName = $this->cleanDisplayName($item->suggested_base_name);
        }

        $attributes = $this->extractVariantAttributes(
            $source,
            $baseName,
            $item->variant_text
        );

        if (! $baseName) {
            $baseName = $attributes['base_name'] ?: $source;
        }

        $baseName = $this->cleanDisplayName($baseName);

        $baseKey = $this->normalize($baseName);

        $hasVariant = collect([
            $attributes['specification'],
            $attributes['size_dimension'],
            $attributes['grade_class'],
            $attributes['finish'],
            $attributes['colour_shade'],
            $attributes['variant_text'],
        ])->filter(fn ($value) => filled($value))->isNotEmpty();

        $reviewReasons = [];

        if ($baseName === '' || mb_strlen($baseName) < 3) {
            $reviewReasons[] = 'Base product name is too short or empty.';
        }

        if (
            $baseAction === 'REVIEW_NEW_BASE'
            && ! $hasVariant
            && $item->match_status === MaterialCatalogItem::STATUS_NEW_CANDIDATE
        ) {
            $reviewReasons[] = 'New candidate has no safely extracted variant attributes.';
        }

        if (
            $attributes['confidence'] < 70
            && $hasVariant
        ) {
            $reviewReasons[] = 'Variant parsing confidence is below 70.';
        }

        if (
            $item->match_status === MaterialCatalogItem::STATUS_REVIEW
        ) {
            $reviewReasons[] = 'Catalogue analysis already marked this row for manual review.';
        }

        $unitCode = $item->suggestedUnit?->unit_code;

        if (
            ! $unitCode
            && $item->matchedMaterialType?->unit_master_id
        ) {
            $unitCode = 'EXISTING_TYPE_UNIT';
        }

        $variantName = $this->buildVariantName($attributes);

        return [
            'staging_id' => $item->id,
            'category_code' => $item->category?->code,
            'category_name' => $item->category?->name,
            'subcategory_code' => $item->subcategory?->code,
            'subcategory_name' => $item->subcategory?->name,
            'source_item_name' => $source,
            'normalized_source_name' => $item->normalized_name,
            'match_status' => $item->match_status,
            'match_confidence' => $item->match_confidence,

            'base_action' => $baseAction,
            'base_name' => $baseName,
            'base_key' => $baseKey,
            'existing_material_type_id' => $existingMaterialTypeId,
            'existing_material_type_name' => $existingMaterialTypeName,
            'existing_material_type_code' => $existingMaterialTypeCode,

            'variant_name' => $variantName,
            'specification' => $attributes['specification'],
            'size_dimension' => $attributes['size_dimension'],
            'grade_class' => $attributes['grade_class'],
            'finish' => $attributes['finish'],
            'colour_shade' => $attributes['colour_shade'],
            'variant_text' => $attributes['variant_text'],
            'suggested_unit_code' => $unitCode,
            'search_alias' => $source,

            'normalization_rule' => $attributes['rule'],
            'normalization_confidence' => $attributes['confidence'],
            'has_variant' => $hasVariant,
            'review_required' => ! empty($reviewReasons),
            'review_reasons' => implode(' | ', $reviewReasons),
        ];
    }

    private function extractVariantAttributes(
        string $source,
        ?string $knownBaseName,
        ?string $existingVariantText
    ): array {
        $working = $source;

        if ($knownBaseName) {
            $working = $this->removeFirstCaseInsensitive(
                $working,
                $knownBaseName
            );
        }

        $working = trim(
            $working,
            " \t\n\r\0\x0B-–—,:;/|()[]"
        );

        if ($existingVariantText) {
            $working = trim($existingVariantText);
        }

        $size = $this->extractFirst($working, [
            '/\b\d+(?:\.\d+)?\s*(?:mm|cm|mtr|meter|metre|inch|inches|ft|feet)\b(?:\s*[x×]\s*\d+(?:\.\d+)?\s*(?:mm|cm|mtr|meter|metre|inch|inches|ft|feet)\b){0,2}/iu',
            '/\b\d+(?:\.\d+)?\s*[x×]\s*\d+(?:\.\d+)?(?:\s*[x×]\s*\d+(?:\.\d+)?)?\s*(?:mm|cm|mtr|meter|metre|inch|inches|ft|feet)?\b/iu',
        ]);

        $gradeClass = $this->extractFirst($working, [
            '/\b(?:fe\s*\d+[a-z]?|m\s*\d+)\b/iu',
            '/\b(?:grade|class|schedule|sch|sdr|pn)\s*[-:]?\s*[a-z0-9.\-\/]+\b/iu',
        ]);

        $finish = $this->extractFirst($working, [
            '/\b(?:polished|matte|matt|glossy|satin|textured|brushed|mirror|natural|honed|flamed|antique|powder\s*coated|galvanized|galvanised)\b/iu',
            '/\bfinish\s*[-:]?\s*[a-z0-9][a-z0-9 \-\/]+\b/iu',
        ]);

        $colour = $this->extractFirst($working, [
            '/\b(?:colour|color|shade)\s*[-:]?\s*[a-z0-9][a-z0-9 \-\/]+\b/iu',
        ]);

        $specification = null;

        if ($gradeClass) {
            $specification = $this->extractFirst($working, [
                '/\b(?:type|series|model|duty)\s*[-:]?\s*[a-z0-9][a-z0-9 .\-\/]+\b/iu',
            ]);
        } else {
            $specification = $this->extractFirst($working, [
                '/\b(?:type|series|model|duty|sdr|schedule|sch|pn)\s*[-:]?\s*[a-z0-9][a-z0-9 .\-\/]+\b/iu',
            ]);
        }

        $found = array_filter([
            $specification,
            $size,
            $gradeClass,
            $finish,
            $colour,
        ]);

        $variantText = $working !== '' ? $working : null;

        $baseName = null;
        $rule = 'no_safe_split';
        $confidence = 0;

        if ($knownBaseName) {
            $baseName = $knownBaseName;
            $rule = 'existing_or_suggested_base';
            $confidence = 95;
        } elseif (! empty($found)) {
            $baseName = $this->deriveBaseFromSource(
                $source,
                $found
            );

            if ($baseName && $this->normalize($baseName) !== $this->normalize($source)) {
                $rule = 'attribute_stripping';
                $confidence = 78;
            }
        }

        return [
            'base_name' => $baseName,
            'variant_text' => $variantText,
            'specification' => $specification,
            'size_dimension' => $size,
            'grade_class' => $gradeClass,
            'finish' => $finish,
            'colour_shade' => $colour,
            'rule' => $rule,
            'confidence' => $confidence,
        ];
    }

    private function deriveBaseFromSource(
        string $source,
        array $attributes
    ): ?string {
        $base = $source;

        // Remove longer values first so nested fragments are less likely
        // to damage the base product name.
        usort(
            $attributes,
            fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a)
        );

        foreach ($attributes as $attribute) {
            $base = $this->removeFirstCaseInsensitive(
                $base,
                $attribute
            );
        }

        $base = preg_replace(
            '/\s+[-–—,:;/|]\s*$/u',
            '',
            $base
        ) ?? $base;

        $base = trim(
            preg_replace('/\s+/u', ' ', $base) ?? $base,
            " \t\n\r\0\x0B-–—,:;/|()[]"
        );

        return mb_strlen($base) >= 3 ? $base : null;
    }

    private function buildVariantName(array $attributes): ?string
    {
        $parts = collect([
            $attributes['specification'],
            $attributes['size_dimension'],
            $attributes['grade_class'],
            $attributes['finish'],
            $attributes['colour_shade'],
        ])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->unique(fn ($value) => $this->normalize($value))
            ->values();

        if ($parts->isNotEmpty()) {
            return $parts->implode(' — ');
        }

        return filled($attributes['variant_text'])
            ? trim((string) $attributes['variant_text'])
            : null;
    }

    private function extractFirst(
        string $value,
        array $patterns
    ): ?string {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches)) {
                return trim($matches[0]);
            }
        }

        return null;
    }

    private function removeFirstCaseInsensitive(
        string $subject,
        string $needle
    ): string {
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

    private function writeDetailCsv(
        string $path,
        Collection $prepared
    ): void {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Staging ID',
            'Category Code',
            'Category',
            'Subcategory Code',
            'Subcategory / Family',
            'Source Item',
            'Match Status',
            'Match Confidence',
            'Base Action',
            'Existing Material Type ID',
            'Existing Material Type',
            'Existing Material Code',
            'Proposed Base Product',
            'Variant Name',
            'Specification / Type',
            'Size / Dimension',
            'Grade / Class',
            'Finish',
            'Colour / Shade',
            'Raw Variant Text',
            'Suggested Unit',
            'Search Alias',
            'Normalization Rule',
            'Normalization Confidence',
            'Review Required',
            'Review Reasons',
        ]);

        foreach ($prepared as $row) {
            fputcsv($handle, [
                $row['staging_id'],
                $row['category_code'],
                $row['category_name'],
                $row['subcategory_code'],
                $row['subcategory_name'],
                $row['source_item_name'],
                $row['match_status'],
                $row['match_confidence'],
                $row['base_action'],
                $row['existing_material_type_id'],
                $row['existing_material_type_name'],
                $row['existing_material_type_code'],
                $row['base_name'],
                $row['variant_name'],
                $row['specification'],
                $row['size_dimension'],
                $row['grade_class'],
                $row['finish'],
                $row['colour_shade'],
                $row['variant_text'],
                $row['suggested_unit_code'],
                $row['search_alias'],
                $row['normalization_rule'],
                $row['normalization_confidence'],
                $row['review_required'] ? 'YES' : 'NO',
                $row['review_reasons'],
            ]);
        }

        fclose($handle);
    }

    private function writeBaseProductCsv(
        string $path,
        Collection $prepared
    ): void {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Base Action',
            'Existing Material Type ID',
            'Existing Material Code',
            'Proposed Base Product',
            'Source Row Count',
            'Unique Source Names',
            'Categories',
            'Subcategories / Families',
            'Variant Candidate Count',
            'Suggested Units',
            'Review Required Rows',
        ]);

        $groups = $prepared
            ->groupBy(fn (array $row): string => $row['base_action'].'|'.$row['base_key'])
            ->sortByDesc(fn (Collection $rows): int => $rows->count());

        foreach ($groups as $rows) {
            $first = $rows->first();

            fputcsv($handle, [
                $first['base_action'],
                $first['existing_material_type_id'],
                $first['existing_material_type_code'],
                $first['base_name'],
                $rows->count(),
                $rows->pluck('source_item_name')->unique()->count(),
                $rows->pluck('category_name')->filter()->unique()->implode(' | '),
                $rows->pluck('subcategory_name')->filter()->unique()->implode(' | '),
                $rows->where('has_variant', true)->count(),
                $rows->pluck('suggested_unit_code')->filter()->unique()->implode(' | '),
                $rows->where('review_required', true)->count(),
            ]);
        }

        fclose($handle);
    }

    private function writeVariantCsv(
        string $path,
        Collection $prepared
    ): void {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Base Action',
            'Existing Material Type ID',
            'Existing Material Code',
            'Base Product',
            'Variant Name',
            'Specification / Type',
            'Size / Dimension',
            'Grade / Class',
            'Finish',
            'Colour / Shade',
            'Suggested Unit',
            'Source Item',
            'Category',
            'Subcategory / Family',
            'Search Alias',
            'Review Required',
            'Review Reasons',
        ]);

        foreach ($prepared->where('has_variant', true) as $row) {
            fputcsv($handle, [
                $row['base_action'],
                $row['existing_material_type_id'],
                $row['existing_material_type_code'],
                $row['base_name'],
                $row['variant_name'],
                $row['specification'],
                $row['size_dimension'],
                $row['grade_class'],
                $row['finish'],
                $row['colour_shade'],
                $row['suggested_unit_code'],
                $row['source_item_name'],
                $row['category_name'],
                $row['subcategory_name'],
                $row['search_alias'],
                $row['review_required'] ? 'YES' : 'NO',
                $row['review_reasons'],
            ]);
        }

        fclose($handle);
    }

    private function writeManualReviewCsv(
        string $path,
        Collection $prepared
    ): void {
        $handle = $this->openCsv($path);

        fputcsv($handle, [
            'Staging ID',
            'Source Item',
            'Category',
            'Subcategory / Family',
            'Match Status',
            'Proposed Base Product',
            'Variant Name',
            'Suggested Unit',
            'Normalization Rule',
            'Normalization Confidence',
            'Review Reasons',
        ]);

        foreach ($prepared->where('review_required', true) as $row) {
            fputcsv($handle, [
                $row['staging_id'],
                $row['source_item_name'],
                $row['category_name'],
                $row['subcategory_name'],
                $row['match_status'],
                $row['base_name'],
                $row['variant_name'],
                $row['suggested_unit_code'],
                $row['normalization_rule'],
                $row['normalization_confidence'],
                $row['review_reasons'],
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

        // UTF-8 BOM for clean opening in Microsoft Excel on Windows.
        fwrite($handle, "\xEF\xBB\xBF");

        return $handle;
    }

    private function cleanDisplayName(string $value): string
    {
        return trim(
            preg_replace('/\s+/u', ' ', $value) ?? $value
        );
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(trim($value));

        $value = str_replace(
            ['–', '—', '−', '/', '\\', '&', '+'],
            ['-', '-', '-', ' ', ' ', ' and ', ' '],
            $value
        );

        $value = preg_replace(
            '/[^a-z0-9]+/u',
            ' ',
            $value
        ) ?? $value;

        return trim(
            preg_replace('/\s+/u', ' ', $value) ?? $value
        );
    }
}
