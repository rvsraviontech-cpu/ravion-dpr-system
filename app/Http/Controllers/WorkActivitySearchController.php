<?php

namespace App\Http\Controllers;

use App\Models\WorkActivity;
use App\Models\WorkPackage;
use App\Models\WorkSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkActivitySearchController extends Controller
{
    /**
     * Search the canonical Work Activity catalogue.
     *
     * Searches activity names, codes and aliases.
     * Returns operational information only.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'work_package_id' => [
                'nullable',
                'integer',
                'exists:work_packages,id',
            ],
            'work_section_id' => [
                'nullable',
                'integer',
                'exists:work_sections,id',
            ],
        ]);

        $search = trim($validated['q'] ?? '');

        $query = WorkActivity::query()
            ->with([
                'package:id,code,name',
                'section:id,work_package_id,code,name',
            ])
            ->where('is_active', true)
            ->where('is_selectable', true);

        if (! empty($validated['work_package_id'])) {
            $query->where(
                'work_package_id',
                $validated['work_package_id']
            );
        }

        if (! empty($validated['work_section_id'])) {
            $query->where(
                'work_section_id',
                $validated['work_section_id']
            );
        }

        if ($search !== '') {
            $normalizedSearch = mb_strtolower(
                preg_replace('/\s+/', ' ', $search)
            );

            $escapedSearch = addcslashes(
                $search,
                '%_\\'
            );

            $escapedNormalizedSearch = addcslashes(
                $normalizedSearch,
                '%_\\'
            );

            $query->where(function ($builder) use (
                $escapedSearch,
                $escapedNormalizedSearch
            ) {
                $builder
                    ->where(
                        'name',
                        'like',
                        "%{$escapedSearch}%"
                    )
                    ->orWhere(
                        'code',
                        'like',
                        "%{$escapedSearch}%"
                    )
                    ->orWhereHas(
                        'aliases',
                        function ($aliasQuery) use (
                            $escapedNormalizedSearch
                        ) {
                            $aliasQuery
                                ->where('is_active', true)
                                ->where(
                                    'normalized_alias',
                                    'like',
                                    "%{$escapedNormalizedSearch}%"
                                );
                        }
                    );
            });
        }

        $activities = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $activities->map(
                fn (WorkActivity $activity) =>
                    $this->activityPayload($activity)
            )->values(),
        ]);
    }

    /**
     * Return active Work Packages.
     */
    public function packages(): JsonResponse
    {
        $packages = WorkPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json([
            'data' => $packages,
        ]);
    }

    /**
     * Return Work Sections for one Work Package.
     */
    public function sections(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'work_package_id' => [
                'required',
                'integer',
                'exists:work_packages,id',
            ],
        ]);

        $sections = WorkSection::query()
            ->where(
                'work_package_id',
                $validated['work_package_id']
            )
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'work_package_id',
                'code',
                'name',
            ]);

        return response()->json([
            'data' => $sections,
        ]);
    }

    /**
     * Return Work Activities for one Work Section.
     */
    public function activities(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'work_section_id' => [
                'required',
                'integer',
                'exists:work_sections,id',
            ],
        ]);

        $activities = WorkActivity::query()
            ->with([
                'package:id,code,name',
                'section:id,work_package_id,code,name',
            ])
            ->where(
                'work_section_id',
                $validated['work_section_id']
            )
            ->where('is_active', true)
            ->where('is_selectable', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $activities->map(
                fn (WorkActivity $activity) =>
                    $this->activityPayload($activity)
            )->values(),
        ]);
    }

    /**
     * Explicitly whitelist fields safe for Engineers.
     *
     * Never expose internal cost codes, rates,
     * BOQ information or accounting mappings.
     */
    private function activityPayload(
        WorkActivity $activity
    ): array {
        return [
            'id' => $activity->id,
            'code' => $activity->code,
            'name' => $activity->name,

            'work_package_id' =>
                $activity->work_package_id,

            'work_package_name' =>
                $activity->package?->name,

            'work_section_id' =>
                $activity->work_section_id,

            'work_section_name' =>
                $activity->section?->name,

            'default_unit' =>
                $activity->default_unit,

            'allow_materials' =>
                (bool) $activity->allow_materials,

            'allow_labour' =>
                (bool) $activity->allow_labour,

            'allow_equipment' =>
                (bool) $activity->allow_equipment,

            'allow_photos' =>
                (bool) $activity->allow_photos,

            'material_calculation_enabled' =>
                (bool) $activity->material_calculation_enabled,
        ];
    }
}