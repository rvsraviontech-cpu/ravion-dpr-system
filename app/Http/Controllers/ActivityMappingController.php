<?php

namespace App\Http\Controllers;

use App\Framework\Traits\MasterAuditTrait;
use App\Imports\ActivityMappingsImport;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\ActivityMapping;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActivityMappingController extends Controller
{
    use MasterAuditTrait;

    public function index(Request $request)
    {
        $activityMappings = ActivityMapping::with([
                'division',
                'activity',
            ])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('rh_cost_code', 'like', '%' . $request->search . '%')
                        ->orWhere('activity_name', 'like', '%' . $request->search . '%')
                        ->orWhere('unit', 'like', '%' . $request->search . '%')
                        ->orWhere('odoo_type', 'like', '%' . $request->search . '%')
                        ->orWhere('material_group', 'like', '%' . $request->search . '%')
                        ->orWhere('contractor_type', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->division_id, function ($query) use ($request) {
                $query->where('activity_division_id', $request->division_id);
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy('rh_cost_code')
            ->paginate(config('rds.pagination.per_page', 25))
            ->withQueryString();

        return view('activity-mappings.index', array_merge(
            compact('activityMappings'),
            $this->formData()
        ));
    }

    public function create()
    {
        return view('activity-mappings.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateMapping($request);

        $activity = Activity::firstOrCreate(
            [
                'activity_name' => $validated['activity_name'],
            ],
            [
                'activity_division_id' => $validated['activity_division_id'] ?? null,
                'unit' => $validated['unit'] ?: 'Nos',
                'work_stage' => 'General',
                'is_active' => true,
            ]
        );

        $validated['activity_id'] = $activity->id;
        $validated['division_code'] = 'RH';
        $validated['unit'] = $validated['unit'] ?: 'Nos';

        $activityMapping = ActivityMapping::create($validated);

        $this->auditCreated(
            'Activity Mappings',
            'ActivityMapping',
            $activityMapping->id,
            $activityMapping->activity_name,
            $this->auditValues($activityMapping)
        );

        return redirect()
            ->route('activity-mappings.index')
            ->with('success', 'Activity mapping created successfully.');
    }

    public function edit(ActivityMapping $activityMapping)
    {
        return view('activity-mappings.edit', array_merge(
            compact('activityMapping'),
            $this->formData()
        ));
    }

    public function update(Request $request, ActivityMapping $activityMapping)
    {
        $validated = $this->validateMapping($request, $activityMapping);

        $oldValues = $this->auditValues($activityMapping);

        $activityMapping->update($validated);

        $activityMapping->refresh();

        if (
            ($oldValues['is_active'] ?? null) != ($activityMapping->is_active ?? null)
        ) {
            $this->auditStatusChanged(
                'Activity Mappings',
                'ActivityMapping',
                $activityMapping->id,
                $activityMapping->activity_name,
                $activityMapping->is_active,
                $oldValues,
                $this->auditValues($activityMapping)
            );
        } else {
            $this->auditUpdated(
                'Activity Mappings',
                'ActivityMapping',
                $activityMapping->id,
                $activityMapping->activity_name,
                $oldValues,
                $this->auditValues($activityMapping)
            );
        }

        return redirect()
            ->route('activity-mappings.index')
            ->with('success', 'Activity mapping updated successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $import = new ActivityMappingsImport();

            Excel::import($import, $request->file('file'));

            $this->auditCreated(
                'Activity Mappings',
                'ActivityMapping',
                0,
                'Import',
                [
                    'imported_count' => $import->importedCount,
                ]
            );

            return back()->with(
                'success',
                $import->importedCount . ' activity mappings imported successfully.'
            );
        } catch (\Throwable $e) {
            return back()->withErrors([
                'import_error' => $e->getMessage(),
            ]);
        }
    }

    private function formData(): array
    {
        return [
            'divisions' => ActivityDivision::where('is_active', true)
                ->orderBy('sequence')
                ->orderBy('name')
                ->get(),

            'activities' => Activity::where('is_active', true)
                ->orderBy('activity_name')
                ->get(),
        ];
    }

    private function validateMapping(Request $request, ?ActivityMapping $activityMapping = null): array
    {
        $mappingId = $activityMapping?->id;

        return $request->validate([
            'activity_division_id' => 'nullable|exists:activity_divisions,id',
            'activity_id' => 'nullable|exists:activities,id',
            'rh_cost_code' => 'required|string|max:255|unique:activity_mappings,rh_cost_code,' . $mappingId,
            'activity_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:255',
            'odoo_type_code' => 'nullable|string|max:255',
            'odoo_type' => 'nullable|string|max:255',
            'material_group' => 'nullable|string|max:255',
            'contractor_type' => 'nullable|string|max:255',
            'inventory_expense_bucket' => 'nullable|string|max:255',
            'procurement_mode' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string',
        ]);
    }

    private function auditValues(ActivityMapping $activityMapping): array
    {
        return $activityMapping->only([
            'id',
            'activity_division_id',
            'activity_id',
            'division_code',
            'rh_cost_code',
            'activity_name',
            'unit',
            'odoo_type_code',
            'odoo_type',
            'material_group',
            'contractor_type',
            'inventory_expense_bucket',
            'procurement_mode',
            'is_active',
            'remarks',
        ]);
    }
}