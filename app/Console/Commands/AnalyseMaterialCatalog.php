<?php

namespace App\Console\Commands;

use App\Models\MaterialCatalogItem;
use App\Models\MaterialType;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyseMaterialCatalog extends Command
{
    protected $signature = 'materials:catalog-analyse
                            {--reset-analysis : Clear previous analysis before recalculating}';

    protected $description = 'Analyse staged catalogue products against existing Material Types without creating products.';

    public function handle(): int
    {
        if (MaterialCatalogItem::query()->doesntExist()) {
            $this->error(
                'No staged catalogue items found. Run materials:catalog-import first.'
            );

            return self::FAILURE;
        }

        $units = DB::table('unit_masters')
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($unit) => Str::upper((string) $unit->unit_code));

        $materialTypes = MaterialType::query()
            ->where('is_active', true)
            ->get([
                'id',
                'material_group',
                'material_type_name',
                'material_type_code',
                'unit_master_id',
            ]);

        $existingByNormalized = $materialTypes
            ->groupBy(fn (MaterialType $material) => $this->normalize(
                $material->material_type_name
            ));

        if ($this->option('reset-analysis')) {
            MaterialCatalogItem::query()->update([
                'suggested_base_name' => null,
                'variant_text' => null,
                'suggested_unit_master_id' => null,
                'matched_material_type_id' => null,
                'match_status' => MaterialCatalogItem::STATUS_PENDING,
                'match_confidence' => null,
                'analysis_notes' => null,
            ]);
        }

        $counts = [
            MaterialCatalogItem::STATUS_EXACT_MATCH => 0,
            MaterialCatalogItem::STATUS_PROBABLE_VARIANT => 0,
            MaterialCatalogItem::STATUS_NEW_CANDIDATE => 0,
            MaterialCatalogItem::STATUS_REVIEW => 0,
        ];

        $items = MaterialCatalogItem::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $this->output->progressStart($items->count());

        DB::transaction(function () use (
            $items,
            $materialTypes,
            $existingByNormalized,
            $units,
            &$counts
        ): void {
            foreach ($items as $item) {
                $normalized = $this->normalize($item->source_item_name);
                $exact = $existingByNormalized->get($normalized)?->first();

                if ($exact) {
                    $item->update([
                        'normalized_name' => $normalized,
                        'suggested_base_name' => $exact->material_type_name,
                        'variant_text' => null,
                        'suggested_unit_master_id' => $exact->unit_master_id,
                        'matched_material_type_id' => $exact->id,
                        'match_status' => MaterialCatalogItem::STATUS_EXACT_MATCH,
                        'match_confidence' => 100,
                        'analysis_notes' => 'Exact normalized name match with existing Material Type.',
                    ]);

                    $counts[MaterialCatalogItem::STATUS_EXACT_MATCH]++;
                    $this->output->progressAdvance();

                    continue;
                }

                $candidate = $this->findProbableExistingMaterial(
                    $normalized,
                    $materialTypes
                );

                if ($candidate !== null) {
                    [$material, $confidence, $reason] = $candidate;

                    $item->update([
                        'normalized_name' => $normalized,
                        'suggested_base_name' => $material->material_type_name,
                        'variant_text' => $this->variantText(
                            $item->source_item_name,
                            $material->material_type_name
                        ),
                        'suggested_unit_master_id' => $material->unit_master_id,
                        'matched_material_type_id' => $material->id,
                        'match_status' => MaterialCatalogItem::STATUS_PROBABLE_VARIANT,
                        'match_confidence' => $confidence,
                        'analysis_notes' => $reason
                            .' Review before promotion; no master change has been made.',
                    ]);

                    $counts[MaterialCatalogItem::STATUS_PROBABLE_VARIANT]++;
                    $this->output->progressAdvance();

                    continue;
                }

                [$unitId, $unitCode, $unitConfidence, $unitReason] =
                    $this->suggestUnit($normalized, $units);

                $item->update([
                    'normalized_name' => $normalized,
                    'suggested_base_name' => $this->cleanDisplayName(
                        $item->source_item_name
                    ),
                    'variant_text' => null,
                    'suggested_unit_master_id' => $unitId,
                    'matched_material_type_id' => null,
                    'match_status' => MaterialCatalogItem::STATUS_NEW_CANDIDATE,
                    'match_confidence' => $unitConfidence,
                    'analysis_notes' => $unitReason
                        .($unitCode ? " Suggested unit: {$unitCode}." : '')
                        .' Product still requires master review before promotion.',
                ]);

                $counts[MaterialCatalogItem::STATUS_NEW_CANDIDATE]++;
                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();
        $this->newLine();

        $this->table(
            ['Analysis Result', 'Rows'],
            [
                ['Exact existing matches', $counts[MaterialCatalogItem::STATUS_EXACT_MATCH]],
                ['Probable variants / related existing products', $counts[MaterialCatalogItem::STATUS_PROBABLE_VARIANT]],
                ['New product candidates', $counts[MaterialCatalogItem::STATUS_NEW_CANDIDATE]],
                ['Manual review', $counts[MaterialCatalogItem::STATUS_REVIEW]],
                ['Total staging rows', array_sum($counts)],
            ]
        );

        $uniqueSourceNames = MaterialCatalogItem::query()
            ->distinct('normalized_name')
            ->count('normalized_name');

        $uniqueNewNames = MaterialCatalogItem::query()
            ->where('match_status', MaterialCatalogItem::STATUS_NEW_CANDIDATE)
            ->distinct('normalized_name')
            ->count('normalized_name');

        $this->info("Unique catalogue names: {$uniqueSourceNames}");
        $this->info("Unique new-candidate names: {$uniqueNewNames}");

        $this->newLine();
        $this->comment(
            'Analysis only: material_types and catalogue mapping pivots were not modified.'
        );

        return self::SUCCESS;
    }

    private function findProbableExistingMaterial(
        string $sourceNormalized,
        Collection $materialTypes
    ): ?array {
        if (mb_strlen($sourceNormalized) < 5) {
            return null;
        }

        $sourceTokens = $this->tokens($sourceNormalized);
        $best = null;

        foreach ($materialTypes as $material) {
            $existingNormalized = $this->normalize(
                $material->material_type_name
            );

            if (mb_strlen($existingNormalized) < 4) {
                continue;
            }

            $existingTokens = $this->tokens($existingNormalized);

            // Conservative rule: every meaningful token in the existing
            // Material Type must appear in the catalogue description.
            $missing = array_diff($existingTokens, $sourceTokens);

            if (! empty($missing)) {
                continue;
            }

            // Avoid mapping a single generic word such as "cement" onto
            // unrelated descriptions unless the catalogue text clearly
            // starts/ends with that term.
            if (
                count($existingTokens) === 1
                && ! str_starts_with($sourceNormalized, $existingNormalized.' ')
                && ! str_ends_with($sourceNormalized, ' '.$existingNormalized)
            ) {
                continue;
            }

            $coverage = count($existingTokens) / max(count($sourceTokens), 1);
            $lengthRatio = mb_strlen($existingNormalized)
                / max(mb_strlen($sourceNormalized), 1);

            $confidence = round(
                min(94, 70 + ($coverage * 14) + ($lengthRatio * 10)),
                2
            );

            if ($best === null || $confidence > $best[1]) {
                $best = [
                    $material,
                    $confidence,
                    'Catalogue description contains the full existing Material Type name/tokens.',
                ];
            }
        }

        return $best;
    }

    private function suggestUnit(
        string $normalized,
        Collection $units
    ): array {
        $unit = function (string $code) use ($units): ?object {
            return $units->get($code);
        };

        $containsAny = static function (
            string $value,
            array $needles
        ): bool {
            foreach ($needles as $needle) {
                if (str_contains($value, $needle)) {
                    return true;
                }
            }

            return false;
        };

        if ($containsAny($normalized, [
            'diesel', 'oil', 'paint', 'primer', 'chemical',
            'thinner', 'solvent', 'liquid', 'admixture',
            'curing compound', 'bonding agent',
        ])) {
            $u = $unit('LTR');

            return [
                $u?->id,
                $u?->unit_code,
                90,
                'High-confidence liquid/chemical unit heuristic.',
            ];
        }

        if (
            str_contains($normalized, 'cement')
            && ! $containsAny($normalized, [
                'cement board', 'cement sheet', 'cement tile',
                'cement paint', 'cement spacer',
            ])
        ) {
            $u = $unit('BAG');

            return [
                $u?->id,
                $u?->unit_code,
                88,
                'High-confidence bagged cement unit heuristic.',
            ];
        }

        if ($containsAny($normalized, [
            'sand', 'aggregate', 'soil', 'murum', 'murrum',
            'crusher dust', 'quarry dust', 'stone dust',
            'filling material',
        ])) {
            $u = $unit('CFT');

            return [
                $u?->id,
                $u?->unit_code,
                82,
                'Bulk loose-material volume heuristic.',
            ];
        }

        if ($containsAny($normalized, [
            'tmt', 'rebar', 'reinforcement steel',
            'binding wire', 'structural steel',
        ])) {
            $u = $unit('KG');

            return [
                $u?->id,
                $u?->unit_code,
                82,
                'Steel/reinforcement weight heuristic.',
            ];
        }

        if (
            $containsAny($normalized, [
                'pipe', 'cable', 'conduit', 'hose',
                'rope', 'thread',
            ])
            && ! $containsAny($normalized, [
                'elbow', 'tee', 'coupler', 'socket', 'clamp',
                'valve', 'cap', 'plug', 'connector',
                'junction', 'bend', 'fitting', 'holder',
                'support', 'clip',
            ])
        ) {
            $u = $unit('MTR');

            return [
                $u?->id,
                $u?->unit_code,
                72,
                'Length-based pipe/cable/conduit heuristic.',
            ];
        }

        // Do not force NOS for every unknown item. Keeping the unit null is
        // safer than silently creating incorrect stock behaviour.
        return [
            null,
            null,
            0,
            'Unit could not be determined safely from the catalogue description.',
        ];
    }

    private function variantText(
        string $sourceName,
        string $baseName
    ): ?string {
        $pattern = '/'.preg_quote($baseName, '/').'/iu';
        $variant = preg_replace($pattern, '', $sourceName, 1);
        $variant = trim((string) $variant, " \t\n\r\0\x0B-–—,;/");

        return $variant !== ''
            ? $variant
            : null;
    }

    private function cleanDisplayName(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    private function tokens(string $normalized): array
    {
        $stopWords = [
            'for', 'of', 'the', 'and', 'with', 'to',
            'in', 'on', 'type', 'material',
        ];

        return array_values(array_filter(
            explode(' ', $normalized),
            static fn (string $token): bool =>
                mb_strlen($token) >= 2
                && ! in_array($token, $stopWords, true)
        ));
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
