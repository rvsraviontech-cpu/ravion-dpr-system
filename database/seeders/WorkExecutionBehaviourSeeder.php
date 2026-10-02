<?php

namespace Database\Seeders;

use App\Models\WorkActivity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkExecutionBehaviourSeeder extends Seeder
{
    /**
     * Configure Work Done UI behaviour for the existing Work Execution catalogue.
     *
     * IMPORTANT:
     * - This seeder does NOT create, delete or rename Work Activities.
     * - "allow_*" flags are treated as UI/default-panel behaviour, not a hard
     *   prohibition against recording an unusual resource when required.
     * - Material calculation remains disabled until the versioned Activity
     *   Consumption Norm engine is implemented.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->applyBaseline();
            $this->applyPackageRules();
            $this->applyActivityRules();
            $this->validate();
        });
    }

    /**
     * Safe baseline for physical execution activities.
     *
     * We start broad, then remove irrelevant panels by package/activity.
     * Photos and labour remain broadly available because they are useful for
     * site evidence and daily execution reporting.
     */
    private function applyBaseline(): void
    {
        WorkActivity::query()->update([
            'allow_materials' => true,
            'allow_labour' => true,
            'allow_equipment' => true,
            'allow_photos' => true,
            'material_calculation_enabled' => false,
        ]);
    }

    /**
     * Package-level defaults.
     *
     * These are intentionally conservative. More precise exceptions are applied
     * afterward at activity level.
     */
    private function applyPackageRules(): void
    {
        // Administrative / engineering / inspection-heavy packages:
        // no material consumption panel by default.
        $this->updatePackages([
            'PRE_CONSTRUCTION',
            'SURVEY_ENGINEERING',
            'TESTING_COMMISSIONING',
            'HANDOVER',
        ], [
            'allow_materials' => false,
        ]);

        // Testing/commissioning and handover normally do not need construction
        // equipment usage entered against every activity.
        $this->updatePackages([
            'HANDOVER',
        ], [
            'allow_equipment' => false,
        ]);

        // Site operations are mainly labour/equipment support activities.
        // Material handling does not mean the handled material was consumed.
        $this->updatePackages([
            'SITE_OPERATIONS',
        ], [
            'allow_materials' => false,
        ]);

        // Rectification may consume replacement/repair materials, so materials
        // remain available. Material calculation stays disabled.
    }

    /**
     * Activity-level refinements.
     *
     * Rules are expressed by stable activity CODE, never database ID.
     */
    private function applyActivityRules(): void
    {
        /*
         * ------------------------------------------------------------------
         * PRE-CONSTRUCTION / SURVEY
         * ------------------------------------------------------------------
         * Most of these are professional/inspection tasks. Equipment can still
         * be useful for survey/investigation work, so we only suppress it on
         * clearly documentation/approval-oriented activities using keywords.
         */
        $this->updateMatching(
            ['PRE_CONSTRUCTION'],
            [
                'approval',
                'permit',
                'drawing',
                'document',
                'planning',
                'schedule',
                'method statement',
                'mobilization plan',
            ],
            ['allow_equipment' => false]
        );

        /*
         * ------------------------------------------------------------------
         * SITE OPERATIONS
         * ------------------------------------------------------------------
         */
        $this->updateCodes([
            'SITE_HOUSEKEEP',
            'SITE_FLOOR_CLEAN',
            'SITE_DUST_CLEAN',
            'SITE_AREA_PREP',
            'SITE_AREA_CLEAR',
            'SITE_GENERAL_LABOUR',
            'SITE_WASTE_SEG',
            'SITE_MATERIAL_COVER',
        ], [
            'allow_equipment' => false,
        ]);

        // Loading, shifting, debris movement and dewatering commonly use
        // machinery/equipment, so equipment remains enabled.
        $this->updateCodes([
            'SITE_UNLOAD',
            'SITE_LOAD',
            'SITE_SHIFT',
            'SITE_FLOOR_SHIFT',
            'SITE_STACK',
            'SITE_RESTACK',
            'SITE_DEBRIS_COLLECT',
            'SITE_DEBRIS_SHIFT',
            'SITE_WATER_REMOVE',
            'SITE_DEWATER_SUPPORT',
            'SITE_WATER_SPRAY',
            'SITE_EQUIP_MOVE',
            'SITE_SCRAP_SHIFT',
        ], [
            'allow_equipment' => true,
        ]);

        // Temporary service setup may use consumables/materials even though the
        // SITE_OPERATIONS package normally hides materials.
        $this->updateCodes([
            'SITE_MARK_BARRICADE',
            'SITE_TEMP_COVER',
            'SITE_TEMP_LIGHT',
            'SITE_TEMP_POWER',
            'SITE_TEMP_WATER',
        ], [
            'allow_materials' => true,
        ]);

        /*
         * ------------------------------------------------------------------
         * TESTING & COMMISSIONING
         * ------------------------------------------------------------------
         * Tests use instruments/equipment but normally do not consume project
         * construction material.
         */
        $this->updatePackages([
            'TESTING_COMMISSIONING',
        ], [
            'allow_materials' => false,
            'allow_equipment' => true,
        ]);

        // Pure visual / witness / statutory inspections do not need an equipment
        // panel by default.
        $this->updateCodes([
            'TC_VISUAL',
            'TC_DIMENSION',
            'TC_PRECOM',
            'TC_THIRD_PARTY',
            'TC_AUTHORITY',
            'TC_WITNESS',
        ], [
            'allow_equipment' => false,
        ]);

        /*
         * ------------------------------------------------------------------
         * WATER TREATMENT TESTING
         * ------------------------------------------------------------------
         */
        $this->updateCodes([
            'WT_STP_TEST',
            'WT_WTP_TEST',
            'WT_WATER_TEST',
        ], [
            'allow_materials' => false,
            'allow_equipment' => true,
        ]);

        /*
         * ------------------------------------------------------------------
         * UTILITY TESTING / COMMISSIONING
         * ------------------------------------------------------------------
         */
        $this->updateCodes([
            'UTIL_TEST',
        ], [
            'allow_materials' => false,
            'allow_equipment' => true,
        ]);

        /*
         * ------------------------------------------------------------------
         * RECTIFICATION / REWORK
         * ------------------------------------------------------------------
         * Snagging and re-testing are inspections, while actual rectification
         * activities may consume materials.
         */
        $this->updateCodes([
            'RECT_SNAG_SURVEY',
            'RECT_SNAG_MARK',
            'RECT_RETEST',
        ], [
            'allow_materials' => false,
        ]);

        $this->updateCodes([
            'RECT_SNAG_SURVEY',
            'RECT_SNAG_MARK',
        ], [
            'allow_equipment' => false,
        ]);

        /*
         * ------------------------------------------------------------------
         * HANDOVER / CLOSEOUT
         * ------------------------------------------------------------------
         * Handover is not material consumption. Cleaning/debris activities may
         * use equipment, while inspections/documents/handover normally do not.
         */
        $this->updateCodes([
            'HAND_ROUGH_CLEAN',
            'HAND_DEEP_CLEAN',
            'HAND_FLOOR_CLEAN',
            'HAND_GLASS_CLEAN',
            'HAND_FIXTURE_CLEAN',
            'HAND_DEBRIS_FINAL',
            'HAND_PROTECT_REMOVE',
            'HAND_LABEL_REMOVE',
        ], [
            'allow_equipment' => true,
        ]);

        $this->updateCodes([
            'HAND_PREINSPECT',
            'HAND_FINAL_INSPECT',
            'HAND_DEMO',
            'HAND_ASBUILT',
            'HAND_OM',
            'HAND_KEYS',
            'HAND_SPARES',
            'HAND_ROOM',
            'HAND_FLOOR',
            'HAND_SYSTEM',
            'HAND_PROJECT',
        ], [
            'allow_materials' => false,
            'allow_equipment' => false,
        ]);

        /*
         * ------------------------------------------------------------------
         * LANDSCAPE ESTABLISHMENT / MAINTENANCE
         * ------------------------------------------------------------------
         */
        $this->updateCodes([
            'LAND_WATERING',
        ], [
            'allow_materials' => false,
        ]);

        /*
         * ------------------------------------------------------------------
         * TEMPORARY WORKS
         * ------------------------------------------------------------------
         * Dismantling/removal does not normally consume new material.
         */
        $this->updateCodes([
            'TEMP_SCAFF_DISMANTLE',
        ], [
            'allow_materials' => false,
        ]);

        /*
         * ------------------------------------------------------------------
         * GENERIC NAME-BASED SAFETY NET
         * ------------------------------------------------------------------
         * The catalogue spans 775 activities. These rules catch inspection,
         * testing and commissioning activities in discipline-specific packages
         * without relying on numeric IDs.
         *
         * They only switch MATERIALS off. Equipment remains available because
         * many tests require meters, pumps, gauges, analyzers or test kits.
         */
        $this->updateMatching(
            null,
            [
                'testing',
                ' test',
                'commissioning',
                'inspection',
                'witness',
                'trial run',
            ],
            ['allow_materials' => false]
        );

        /*
         * Documentation/approval/handover-style tasks should not show material
         * or equipment panels by default.
         */
        $this->updateMatching(
            null,
            [
                'document handover',
                'drawing verification',
                'final handover',
                'permit approval',
                'authority approval',
            ],
            [
                'allow_materials' => false,
                'allow_equipment' => false,
            ]
        );

        /*
         * Material calculation is deliberately OFF everywhere until formulas,
         * versions, specifications, wastage allowances and unit conversions are
         * represented in the Activity Consumption Norm Master.
         */
        WorkActivity::query()->update([
            'material_calculation_enabled' => false,
        ]);
    }

    /**
     * Update activities belonging to one or more package codes.
     */
    private function updatePackages(array $packageCodes, array $values): void
    {
        WorkActivity::query()
            ->whereHas('package', function ($query) use ($packageCodes) {
                $query->whereIn('code', $packageCodes);
            })
            ->update($values);
    }

    /**
     * Update explicitly named activity codes.
     *
     * Missing codes cause the seeder to fail so catalogue changes cannot silently
     * invalidate behaviour configuration.
     */
    private function updateCodes(array $codes, array $values): void
    {
        if ($codes === []) {
            return;
        }

        $existingCodes = WorkActivity::query()
            ->whereIn('code', $codes)
            ->pluck('code')
            ->all();

        $missingCodes = array_values(array_diff($codes, $existingCodes));

        if ($missingCodes !== []) {
            throw new RuntimeException(
                'WorkExecutionBehaviourSeeder references unknown activity code(s): ' .
                implode(', ', $missingCodes)
            );
        }

        WorkActivity::query()
            ->whereIn('code', $codes)
            ->update($values);
    }

    /**
     * Update activities whose names contain any of the supplied phrases.
     *
     * $packageCodes = null means search the entire catalogue.
     */
    private function updateMatching(
        ?array $packageCodes,
        array $phrases,
        array $values
    ): void {
        WorkActivity::query()
            ->when(
                $packageCodes !== null,
                fn ($query) => $query->whereHas(
                    'package',
                    fn ($packageQuery) => $packageQuery->whereIn('code', $packageCodes)
                )
            )
            ->where(function ($query) use ($phrases) {
                foreach ($phrases as $phrase) {
                    $query->orWhere('name', 'like', '%' . $phrase . '%');
                }
            })
            ->update($values);
    }

    /**
     * Post-seed integrity checks.
     */
    private function validate(): void
    {
        $total = WorkActivity::query()->count();

        if ($total === 0) {
            throw new RuntimeException(
                'No Work Activities exist. Run the Work Execution catalogue seeders first.'
            );
        }

        $materialCalculationEnabled = WorkActivity::query()
            ->where('material_calculation_enabled', true)
            ->count();

        if ($materialCalculationEnabled !== 0) {
            throw new RuntimeException(
                'Material calculation must remain disabled until the Activity Consumption Norm engine is implemented.'
            );
        }

        $invalidFlags = WorkActivity::query()
            ->whereNull('allow_materials')
            ->orWhereNull('allow_labour')
            ->orWhereNull('allow_equipment')
            ->orWhereNull('allow_photos')
            ->count();

        if ($invalidFlags > 0) {
            throw new RuntimeException(
                "Found {$invalidFlags} Work Activities with incomplete behaviour flags."
            );
        }
    }
}
