<?php

namespace App\Console\Commands;

use App\Models\WorkActivity;
use App\Models\WorkActivityAlias;
use App\Models\WorkPackage;
use App\Models\WorkProjectType;
use App\Models\WorkSection;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuditWorkExecutionCatalogue extends Command
{
    protected $signature = 'work-execution:audit';

    protected $description = 'Audit the Work Execution catalogue without modifying any data';

    public function handle(): int
    {
        $this->newLine();
        $this->info('RAVION WORK EXECUTION CATALOGUE AUDIT');
        $this->line(str_repeat('=', 60));

        $this->showSummary();
        $this->auditPackages();
        $this->auditSections();
        $this->auditActivities();
        $this->auditUnits();
        $this->auditDuplicates();
        $this->auditAliases();
        $this->auditResourceFlags();
        $this->auditProjectTypes();

        $this->newLine();
        $this->info('Audit completed. No database records were modified.');

        return self::SUCCESS;
    }

    private function showSummary(): void
    {
        $this->newLine();
        $this->info('1. MASTER SUMMARY');

        $this->table(
            ['Master', 'Count'],
            [
                ['Work Packages', WorkPackage::count()],
                ['Work Sections', WorkSection::count()],
                ['Work Activities', WorkActivity::count()],
                ['Work Activity Aliases', WorkActivityAlias::count()],
                ['Work Project Types', WorkProjectType::count()],
            ]
        );
    }

    private function auditPackages(): void
    {
        $this->newLine();
        $this->info('2. PACKAGE COVERAGE');

        $packages = WorkPackage::query()
            ->withCount(['sections', 'activities'])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        $this->table(
            [
                'Code',
                'Package',
                'Sections',
                'Activities',
                'Active',
            ],
            $packages->map(fn ($package) => [
                $package->code,
                $package->name,
                $package->sections_count,
                $package->activities_count,
                $package->is_active ? 'YES' : 'NO',
            ])->all()
        );

        $emptyPackages = $packages
            ->where('activities_count', 0);

        if ($emptyPackages->isNotEmpty()) {
            $this->warn(
                'Packages without activities: ' .
                $emptyPackages->pluck('code')->implode(', ')
            );
        } else {
            $this->line('<fg=green>PASS:</> Every package contains activities.');
        }
    }

    private function auditSections(): void
    {
        $this->newLine();
        $this->info('3. SECTION INTEGRITY');

        $emptySections = WorkSection::query()
            ->with('package:id,code,name')
            ->withCount('activities')
            ->having('activities_count', '=', 0)
            ->orderBy('work_package_id')
            ->orderBy('sort_order')
            ->get();

        if ($emptySections->isEmpty()) {
            $this->line('<fg=green>PASS:</> No empty Work Sections found.');
        } else {
            $this->warn(
                'Found ' . $emptySections->count() .
                ' Work Sections without activities.'
            );

            $this->table(
                ['Section Code', 'Section', 'Package'],
                $emptySections->map(fn ($section) => [
                    $section->code,
                    $section->name,
                    $section->package?->code,
                ])->all()
            );
        }

        $wrongPackageLinks = WorkActivity::query()
            ->join(
                'work_sections',
                'work_sections.id',
                '=',
                'work_activities.work_section_id'
            )
            ->whereColumn(
                'work_activities.work_package_id',
                '!=',
                'work_sections.work_package_id'
            )
            ->select([
                'work_activities.code',
                'work_activities.name',
                'work_activities.work_package_id',
                'work_sections.work_package_id as section_package_id',
            ])
            ->get();

        if ($wrongPackageLinks->isEmpty()) {
            $this->line(
                '<fg=green>PASS:</> All activities match their section package.'
            );
        } else {
            $this->error(
                'Found ' . $wrongPackageLinks->count() .
                ' activities linked to a section from another package.'
            );

            $this->table(
                [
                    'Activity Code',
                    'Activity',
                    'Activity Package',
                    'Section Package',
                ],
                $wrongPackageLinks->map(fn ($row) => [
                    $row->code,
                    $row->name,
                    $row->work_package_id,
                    $row->section_package_id,
                ])->all()
            );
        }
    }

    private function auditActivities(): void
    {
        $this->newLine();
        $this->info('4. ACTIVITY INTEGRITY');

        $missingSection = WorkActivity::query()
            ->whereNull('work_section_id')
            ->count();

        $missingUnit = WorkActivity::query()
            ->where(function ($query) {
                $query
                    ->whereNull('default_unit')
                    ->orWhere('default_unit', '');
            })
            ->count();

        $notSelectable = WorkActivity::query()
            ->where('is_selectable', false)
            ->count();

        $inactive = WorkActivity::query()
            ->where('is_active', false)
            ->count();

        $this->table(
            ['Check', 'Count'],
            [
                ['Activities without section', $missingSection],
                ['Activities without default unit', $missingUnit],
                ['Non-selectable activities', $notSelectable],
                ['Inactive activities', $inactive],
            ]
        );
    }

    private function auditUnits(): void
    {
        $this->newLine();
        $this->info('5. DEFAULT UNIT DISTRIBUTION');

        $units = WorkActivity::query()
            ->select(
                'default_unit',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('default_unit')
            ->orderByDesc('total')
            ->orderBy('default_unit')
            ->get();

        $this->table(
            ['Default Unit', 'Activities'],
            $units->map(fn ($row) => [
                $row->default_unit ?: '[EMPTY]',
                $row->total,
            ])->all()
        );

        $knownUnits = collect([
            'Sq.m',
            'Cum',
            'Rm',
            'Kg',
            'Nos',
            'Job',
            'Hour',
            'Shift',
            'Load',
            'Set',
            'Point',
            'Circuit',
            'Loop',
            'Camera',
            'Sample',
            'Area',
            'System',
            'Item',
        ]);

        $unknownUnits = $units
            ->pluck('default_unit')
            ->filter()
            ->reject(fn ($unit) => $knownUnits->contains($unit))
            ->values();

        if ($unknownUnits->isEmpty()) {
            $this->line(
                '<fg=green>PASS:</> All activity units are within the current known unit list.'
            );
        } else {
            $this->warn(
                'Units requiring review: ' .
                $unknownUnits->implode(', ')
            );
        }
    }

    private function auditDuplicates(): void
    {
        $this->newLine();
        $this->info('6. POSSIBLE DUPLICATE ACTIVITIES');

        $duplicateNames = WorkActivity::query()
            ->select(
                DB::raw('LOWER(TRIM(name)) as normalized_name'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('LOWER(TRIM(name))'))
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('total')
            ->get();

        if ($duplicateNames->isEmpty()) {
            $this->line(
                '<fg=green>PASS:</> No exact duplicate activity names found.'
            );
        } else {
            $this->warn(
                'Found ' . $duplicateNames->count() .
                ' duplicate normalized activity names.'
            );

            $this->table(
                ['Normalized Activity Name', 'Count'],
                $duplicateNames->map(fn ($row) => [
                    $row->normalized_name,
                    $row->total,
                ])->all()
            );
        }

        $duplicateCodes = WorkActivity::query()
            ->select(
                'code',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('code')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateCodes->isEmpty()) {
            $this->line(
                '<fg=green>PASS:</> No duplicate Work Activity codes found.'
            );
        } else {
            $this->error(
                'Duplicate Work Activity codes found.'
            );

            $this->table(
                ['Code', 'Count'],
                $duplicateCodes->map(fn ($row) => [
                    $row->code,
                    $row->total,
                ])->all()
            );
        }
    }

    private function auditAliases(): void
    {
        $this->newLine();
        $this->info('7. SEARCH ALIAS AUDIT');

        $activitiesWithoutAliases = WorkActivity::query()
            ->doesntHave('aliases')
            ->count();

        $inactiveAliases = WorkActivityAlias::query()
            ->where('is_active', false)
            ->count();

        $emptyAliases = WorkActivityAlias::query()
            ->where(function ($query) {
                $query
                    ->whereNull('normalized_alias')
                    ->orWhere('normalized_alias', '');
            })
            ->count();

        $this->table(
            ['Check', 'Count'],
            [
                [
                    'Activities without aliases',
                    $activitiesWithoutAliases,
                ],
                [
                    'Inactive aliases',
                    $inactiveAliases,
                ],
                [
                    'Empty normalized aliases',
                    $emptyAliases,
                ],
            ]
        );

        $sharedAliases = WorkActivityAlias::query()
            ->select(
                'normalized_alias',
                DB::raw(
                    'COUNT(DISTINCT work_activity_id) as activity_count'
                )
            )
            ->where('is_active', true)
            ->groupBy('normalized_alias')
            ->havingRaw(
                'COUNT(DISTINCT work_activity_id) > 1'
            )
            ->orderByDesc('activity_count')
            ->orderBy('normalized_alias')
            ->limit(100)
            ->get();

        if ($sharedAliases->isEmpty()) {
            $this->line(
                '<fg=green>PASS:</> No search aliases currently point to multiple activities.'
            );
        } else {
            $this->warn(
                'Found ' . $sharedAliases->count() .
                ' shared aliases in the first 100 results.'
            );

            $this->table(
                ['Search Alias', 'Activities'],
                $sharedAliases->map(fn ($row) => [
                    $row->normalized_alias,
                    $row->activity_count,
                ])->all()
            );
        }
    }

    private function auditResourceFlags(): void
    {
        $this->newLine();
        $this->info('8. RESOURCE FLAG DISTRIBUTION');

        $this->table(
            ['Resource', 'Enabled', 'Disabled'],
            [
                [
                    'Materials',
                    WorkActivity::where(
                        'allow_materials',
                        true
                    )->count(),
                    WorkActivity::where(
                        'allow_materials',
                        false
                    )->count(),
                ],
                [
                    'Labour',
                    WorkActivity::where(
                        'allow_labour',
                        true
                    )->count(),
                    WorkActivity::where(
                        'allow_labour',
                        false
                    )->count(),
                ],
                [
                    'Equipment',
                    WorkActivity::where(
                        'allow_equipment',
                        true
                    )->count(),
                    WorkActivity::where(
                        'allow_equipment',
                        false
                    )->count(),
                ],
                [
                    'Photos',
                    WorkActivity::where(
                        'allow_photos',
                        true
                    )->count(),
                    WorkActivity::where(
                        'allow_photos',
                        false
                    )->count(),
                ],
                [
                    'Material Calculation',
                    WorkActivity::where(
                        'material_calculation_enabled',
                        true
                    )->count(),
                    WorkActivity::where(
                        'material_calculation_enabled',
                        false
                    )->count(),
                ],
            ]
        );

        $allResourcesEnabled = WorkActivity::query()
            ->where('allow_materials', true)
            ->where('allow_labour', true)
            ->where('allow_equipment', true)
            ->where('allow_photos', true)
            ->count();

        $this->line(
            'Activities with Materials + Labour + Equipment + Photos all enabled: ' .
            $allResourcesEnabled
        );

        if ($allResourcesEnabled === WorkActivity::count()) {
            $this->warn(
                'REVIEW REQUIRED: Every activity currently enables all four resource panels.'
            );
        }
    }

    private function auditProjectTypes(): void
    {
        $this->newLine();
        $this->info('9. PROJECT TYPE APPLICABILITY');

        $projectTypes = WorkProjectType::query()
            ->withCount('activities')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $this->table(
            [
                'Code',
                'Project Type',
                'Activities',
                'General',
                'Active',
            ],
            $projectTypes->map(fn ($type) => [
                $type->code,
                $type->name,
                $type->activities_count,
                $type->is_general ? 'YES' : 'NO',
                $type->is_active ? 'YES' : 'NO',
            ])->all()
        );

        $unmappedActivities = WorkActivity::query()
            ->doesntHave('projectTypes')
            ->count();

        $this->line(
            'Activities without Project Type mapping: ' .
            $unmappedActivities
        );

        if ($unmappedActivities > 0) {
            $this->warn(
                'Project Type applicability has not yet been completed for all activities.'
            );
        }
    }
}