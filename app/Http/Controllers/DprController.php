<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Contractor;
use App\Models\Dpr;
use App\Models\DprWorkItem;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DprPhoto;
use App\Models\DprLabour;
use App\Models\Material;
use App\Models\DprMaterial;
use App\Models\Vendor;
use App\Models\DprMaterialReceived;
use App\Models\DprMaterialRequired;
use App\Models\MachineryTool;
use App\Models\DprMachineryTool;
use App\Models\TomorrowPlan;
use App\Models\SiteIssue;
use App\Helpers\AuditHelper;
use App\Models\LabourAttendance;
use App\Models\DprManualLabour;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DprController extends Controller
{
    private function userRole()
    {
        return auth()->user()->role->name ?? null;
    }

    private function isEngineer()
    {
        return $this->userRole() === 'Engineer';
    }

    private function isPmoReviewer()
    {
        return in_array($this->userRole(), ['Admin', 'PMO', 'DGM']);
    }

    private function ensureEngineerProjectAccess($projectId)
    {
        if (!$this->isEngineer()) {
            return;
        }

        $hasAccess = auth()->user()
            ->projects()
            ->where('projects.id', $projectId)
            ->exists();

        abort_unless($hasAccess, 403, 'You are not assigned to this project.');
    }

    private function ensureDprAccess(Dpr $dpr)
    {
        if ($this->isEngineer()) {
            abort_unless($dpr->user_id === auth()->id(), 403, 'Unauthorized DPR access.');
        }
    }

    private function ensureEditable(Dpr $dpr)
{
    $this->ensureDprAccess($dpr);

    if ($dpr->status === 'Approved') {
        return redirect('/dprs')
            ->with('success', 'Approved DPR cannot be edited.');
    }

    if ($this->isEngineer() && !in_array($dpr->status, ['Pending', 'Rejected'])) {
        return redirect('/dprs')
            ->with('success', 'This DPR cannot be edited at the current stage.');
    }

    return null;
}
    public function index(Request $request)
    {
        $query = Dpr::with('project', 'user');

        if ($this->isEngineer()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id && !$this->isEngineer()) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->from_date) {
            $query->whereDate('dpr_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('dpr_date', '<=', $request->to_date);
        }

        $dprs = $query->latest()->get();

        if ($this->isEngineer()) {
            $projects = auth()->user()->projects;

            $engineers = \App\Models\User::where('id', auth()->id())->get();
        } else {
            $projects = Project::all();

            $engineers = \App\Models\User::whereHas('role', function ($q) {
                $q->where('name', 'Engineer');
            })->get();
        }

        return view('dprs.index', compact(
            'dprs',
            'projects',
            'engineers'
        ));
    }

    public function create()
{
    if ($this->isEngineer()) {
        $projects = auth()->user()
            ->projects()
            ->whereNotIn('projects.status', [
                'Completed',
                'Handed Over',
                'Closed',
            ])
            ->orderBy('projects.project_name')
            ->get();
    } else {
        $projects = Project::query()
            ->whereNotIn('status', [
                'Completed',
                'Handed Over',
                'Closed',
            ])
            ->orderBy('project_name')
            ->get();
    }

    $activities = Activity::where('is_active', true)
        ->orderBy('activity_name')
        ->get();

    $contractors = Contractor::where('status', 1)
        ->orderBy('contractor_name')
        ->get();

    $materials = Material::where('is_active', true)
        ->orderBy('material_name')
        ->get();

    $vendors = Vendor::where('is_active', true)
        ->orderBy('vendor_name')
        ->get();

    $machineries = MachineryTool::all();

    $projectBlocks = \App\Models\ProjectBlock::where('is_active', true)
        ->get();

    $projectFloors = \App\Models\ProjectFloor::where('is_active', true)
        ->get();

    $projectUnits = \App\Models\ProjectUnit::where('is_active', true)
        ->get();

    $projectRooms = \App\Models\ProjectRoom::where('is_active', true)
        ->get();

    $projectSubspaces = \App\Models\ProjectSubspace::where('is_active', true)
        ->get();

    $activityMappings = \App\Models\ActivityMapping::with('division')
        ->where('is_active', true)
        ->orderBy('activity_name')
        ->get();

    $activityDivisions = \App\Models\ActivityDivision::where('is_active', true)
        ->orderBy('sequence')
        ->get();

    $materialCategories = \App\Models\MaterialCategory::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    $labourCategories = \App\Models\LabourCategory::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    $labourTypes = \App\Models\LabourType::whereNotNull('labour_category_id')
        ->orderBy('labour_type_name')
        ->get();

        $labours = \App\Models\Labour::query()
    ->where('is_active', true)
    ->orderBy('id')
    ->get();

$attendanceStatuses = \App\Models\AttendanceStatus::query()
    ->where('is_active', true)
    ->orderBy('id')
    ->get();

$shifts = \App\Models\Shift::query()
    ->where('is_active', true)
    ->orderBy('id')
    ->get();

    return view('dprs.create', compact(
        'projects',
        'activities',
        'contractors',
        'materials',
        'vendors',
        'machineries',
        'projectBlocks',
        'projectFloors',
        'projectUnits',
        'projectRooms',
        'projectSubspaces',
        'activityMappings',
        'activityDivisions',
        'materialCategories',
        'labourCategories',
        'labourTypes',
        'labours',
'attendanceStatuses',
'shifts',
    ));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'dpr_date' => 'required|date',
            'weather' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',

            'activity_id' => 'required|array|min:1',
            'activity_id.*' => 'required|exists:activities,id',
            'contractor_id.*' => 'nullable|exists:contractors,id',
            'quantity_completed.*' => 'required|numeric|min:0',

            'labour_attendance_ids' => 'nullable|array',
            'labour_attendance_ids.*' => [
                'integer',
                'distinct',
                'exists:labour_attendances,id',
            ],

            'manual_labours' => 'nullable|array',
            'manual_labours.*.labour_id' => [
                'required',
                'integer',
                'distinct',
                'exists:labours,id',
            ],
            'manual_labours.*.attendance_status_id' => [
                'required',
                'integer',
                'exists:attendance_statuses,id',
            ],
            'manual_labours.*.shift_id' => [
                'required',
                'integer',
                'exists:shifts,id',
            ],
            'manual_labours.*.normal_hours' => [
                'required',
                'numeric',
                'min:0',
                'max:24',
            ],
            'manual_labours.*.ot_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
            ],
            'manual_labours.*.reason' => [
                'required',
                'string',
                'in:missed_attendance,late_joining,replacement_labour,emergency_labour,attendance_not_created,attendance_incomplete,other',
            ],
            'manual_labours.*.remarks' => [
                'required',
                'string',
                'max:1000',
            ],

            'photos.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $this->ensureEngineerProjectAccess($validated['project_id']);

        $attendanceIds = collect(
            $validated['labour_attendance_ids'] ?? []
        )
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $manualLabours = collect(
            $validated['manual_labours'] ?? []
        )
            ->filter(
                fn (array $row): bool =>
                    ! empty($row['labour_id'])
            )
            ->values();

        $linkedAttendances = collect();

        if ($attendanceIds->isNotEmpty()) {
            $linkedAttendances = LabourAttendance::query()
                ->with([
                    'details:id,labour_attendance_id,labour_id',
                ])
                ->whereIn('id', $attendanceIds)
                ->where('project_id', $validated['project_id'])
                ->whereDate(
                    'attendance_date',
                    $validated['dpr_date']
                )
                ->where('is_active', true)
                ->whereIn('status', [
                    'submitted',
                    'approved',
                    'reopened',
                ])
                ->get();

            if (
                $linkedAttendances->count()
                !== $attendanceIds->count()
            ) {
                throw ValidationException::withMessages([
                    'labour_attendance_ids' =>
                        'One or more Labour Attendance sheets are invalid, inactive, or do not belong to the selected project and DPR date.',
                ]);
            }
        }

        $attendanceLabourIds = $linkedAttendances
            ->flatMap(
                fn (LabourAttendance $attendance) =>
                    $attendance->details->pluck('labour_id')
            )
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $manualLabourIds = $manualLabours
            ->pluck('labour_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->values();

        $duplicateManualLabourIds = $manualLabourIds
            ->duplicates()
            ->unique()
            ->values();

        if ($duplicateManualLabourIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'manual_labours' =>
                    'The same labour cannot be added more than once as a manual DPR exception.',
            ]);
        }

        $attendanceOverlapIds = $manualLabourIds
            ->intersect($attendanceLabourIds)
            ->unique()
            ->values();

        if ($attendanceOverlapIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'manual_labours' =>
                    'A labourer already present in the linked Labour Attendance cannot also be added manually.',
            ]);
        }

        DB::transaction(function () use (
            $request,
            $validated,
            $attendanceIds,
            $manualLabours,
            &$dpr
        ) {
            $dpr = Dpr::create([
                'project_id' => $validated['project_id'],
                'user_id' => auth()->id(),
                'dpr_date' => $validated['dpr_date'],
                'weather' => $validated['weather'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'Pending',
                'pmo_remarks' => null,
            ]);

            foreach (
                $validated['activity_id']
                as $index => $activityId
            ) {
                DprWorkItem::create([
                    'dpr_id' => $dpr->id,
                    'activity_id' => $activityId,
                    'activity_mapping_id' =>
                        $request->activity_mapping_id[$index]
                        ?? null,
                    'project_block_id' =>
                        $request->project_block_id[$index]
                        ?? null,
                    'project_floor_id' =>
                        $request->project_floor_id[$index]
                        ?? null,
                    'project_unit_id' =>
                        $request->project_unit_id[$index]
                        ?? null,
                    'project_room_id' =>
                        $request->project_room_id[$index]
                        ?? null,
                    'project_subspace_id' =>
                        $request->project_subspace_id[$index]
                        ?? null,
                    'contractor_id' =>
                        $request->contractor_id[$index]
                        ?? null,
                    'quantity_completed' =>
                        $request->quantity_completed[$index],
                    'remarks' =>
                        $request->work_remarks[$index]
                        ?? null,
                ]);
            }

            if ($attendanceIds->isNotEmpty()) {
                $pivotData = $attendanceIds
                    ->mapWithKeys(
                        fn (int $attendanceId): array => [
                            $attendanceId => [
                                'created_by' => auth()->id(),
                            ],
                        ]
                    )
                    ->all();

                $dpr->labourAttendances()->attach($pivotData);
            }

            foreach ($manualLabours as $manualLabour) {
                DprManualLabour::create([
                    'dpr_id' => $dpr->id,
                    'labour_id' =>
                        (int) $manualLabour['labour_id'],
                    'attendance_status_id' =>
                        (int) $manualLabour[
                            'attendance_status_id'
                        ],
                    'shift_id' =>
                        (int) $manualLabour['shift_id'],
                    'normal_hours' =>
                        (float) $manualLabour[
                            'normal_hours'
                        ],
                    'ot_hours' =>
                        (float) (
                            $manualLabour['ot_hours']
                            ?? 0
                        ),
                    'reason' =>
                        $manualLabour['reason'],
                    'remarks' =>
                        trim($manualLabour['remarks']),
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('dpr_photos', 'public');

                    DprPhoto::create([
                        'dpr_id' => $dpr->id,
                        'photo_path' => $path,
                    ]);
                }
            }

            if ($request->material_id) {
                foreach ($request->material_id as $index => $materialId) {
                    if (! $materialId) {
                        continue;
                    }

                    DprMaterial::create([
                        'dpr_id' => $dpr->id,
                        'material_id' => $materialId,
                        'quantity_used' => $request->quantity_used[$index] ?? 0,
                    ]);
                }
            }

            if ($request->received_material_id) {
                foreach ($request->received_material_id as $index => $materialId) {
                    if (! $materialId) {
                        continue;
                    }

                    DprMaterialReceived::create([
                        'dpr_id' => $dpr->id,
                        'material_id' => $materialId,
                        'vendor_id' => $request->vendor_id[$index] ?? null,
                        'quantity_received' => $request->quantity_received[$index] ?? 0,
                        'challan_number' => $request->challan_number[$index] ?? null,
                        'bill_number' => $request->bill_number[$index] ?? null,
                    ]);
                }
            }

            if ($request->required_material_id) {
                foreach ($request->required_material_id as $index => $materialId) {
                    if (! $materialId) {
                        continue;
                    }

                    DprMaterialRequired::create([
                        'dpr_id' => $dpr->id,
                        'material_id' => $materialId,
                        'required_quantity' => $request->required_quantity[$index] ?? 0,
                        'required_date' => $request->required_date[$index] ?? null,
                        'priority' => $request->priority[$index] ?? 'Normal',
                        'reason' => $request->reason[$index] ?? null,
                        'remarks' => $request->required_remarks[$index] ?? null,
                    ]);
                }
            }

            if ($request->machinery_tool_id) {
                foreach ($request->machinery_tool_id as $index => $machineId) {
                    if (! $machineId) {
                        continue;
                    }

                    DprMachineryTool::create([
                        'dpr_id' => $dpr->id,
                        'machinery_tool_id' => $machineId,
                        'quantity' => $request->machine_quantity[$index] ?? 1,
                        'usage_hours' => $request->usage_hours[$index] ?? 0,
                        'working_condition' => $request->working_condition[$index] ?? 'Working',
                        'remarks' => $request->machine_remarks[$index] ?? null,
                    ]);
                }
            }

            if ($request->issue_type) {
                foreach ($request->issue_type as $index => $issueType) {
                    if (! $issueType) {
                        continue;
                    }

                    SiteIssue::create([
                        'dpr_id' => $dpr->id,
                        'issue_type' => $issueType,
                        'related_activity' => $request->related_activity[$index] ?? null,
                        'description' => $request->issue_description[$index] ?? null,
                        'responsible_person' => $request->responsible_person[$index] ?? null,
                        'priority' => $request->issue_priority[$index] ?? 'Medium',
                        'status' => $request->issue_status[$index] ?? 'Open',
                        'remarks' => $request->issue_remarks[$index] ?? null,
                    ]);
                }
            }

            if ($request->plan_activity_id) {
                foreach ($request->plan_activity_id as $index => $activityId) {
                    if (! $activityId) {
                        continue;
                    }

                    TomorrowPlan::create([
                        'dpr_id' => $dpr->id,
                        'activity_id' => $activityId,
                        'planned_quantity' => $request->planned_quantity[$index] ?? 0,
                        'unit' => $request->planned_unit[$index] ?? $request->used_unit[$index] ?? null,
                        'planned_labour' => $request->planned_labour[$index] ?? null,
                        'materials_required' => $request->planned_materials[$index] ?? null,
                        'machinery_required' => $request->planned_machinery[$index] ?? null,
                        'risks_constraints' => $request->planned_risks[$index] ?? null,
                    ]);
                }
            }

            AuditHelper::log(
                'DPR',
                'Created',
                'Dpr',
                $dpr->id,
                'DPR submitted by engineer for PMO review',
                null,
                array_merge(
                    $dpr->only([
                        'id',
                        'project_id',
                        'user_id',
                        'dpr_date',
                        'weather',
                        'remarks',
                        'status',
                        'pmo_remarks',
                    ]),
                    [
                        'linked_labour_attendance_ids' =>
                            $attendanceIds->all(),
                        'manual_labour_count' =>
                            $manualLabours->count(),
                    ]
                )
            );
        });

        return redirect('/dprs')
            ->with('success', 'DPR submitted successfully for PMO review.');
    }

    public function pmoQueue()
    {
        abort_unless($this->isPmoReviewer(), 403);

        $dprs = Dpr::with('project', 'user')
            ->where('status', 'Pending')
            ->latest()
            ->get();

        return view('dprs.pmo-queue', compact('dprs'));
    }

    public function approve(Request $request, $id)
    {
        abort_unless($this->isPmoReviewer(), 403);

        $request->validate([
            'pmo_remarks' => 'nullable|string|max:2000',
        ]);

        $dpr = Dpr::findOrFail($id);

        if ($dpr->status !== 'Pending') {
            return redirect('/pmo/dprs')
                ->with('success', 'Only pending DPRs can be approved.');
        }

        $oldValues = $dpr->only(['status', 'pmo_remarks']);

        $dpr->update([
            'status' => 'Approved',
            'pmo_remarks' => $request->pmo_remarks,
        ]);

        AuditHelper::log(
            'DPR',
            'Approved',
            'Dpr',
            $dpr->id,
            'DPR approved by PMO/Admin',
            $oldValues,
            $dpr->only(['status', 'pmo_remarks'])
        );

        return redirect('/pmo/dprs')
            ->with('success', 'DPR approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        abort_unless($this->isPmoReviewer(), 403);

        $request->validate([
            'pmo_remarks' => 'required|string|max:2000',
        ]);

        $dpr = Dpr::findOrFail($id);

        if ($dpr->status !== 'Pending') {
            return redirect('/pmo/dprs')
                ->with('success', 'Only pending DPRs can be rejected or returned for correction.');
        }

        $oldValues = $dpr->only(['status', 'pmo_remarks']);

        $dpr->update([
            'status' => 'Rejected',
            'pmo_remarks' => $request->pmo_remarks,
        ]);

        AuditHelper::log(
            'DPR',
            'Rejected',
            'Dpr',
            $dpr->id,
            'DPR rejected / returned for correction by PMO/Admin',
            $oldValues,
            $dpr->only(['status', 'pmo_remarks'])
        );

        return redirect('/pmo/dprs')
            ->with('success', 'DPR returned to engineer for correction.');
    }

    public function show($id)
    {
        $dpr = Dpr::with([
            'project',
            'user',
            'workItems.activity',
            'workItems.contractor',
            'photos',
            'labours.labourType',
            'materials.material',
            'materialReceived.material',
            'materialReceived.vendor',
            'materialRequired.material',
            'machineryTools.machineryTool',
            'siteIssues',
            'tomorrowPlans.activity',
            'workItems.block',
            'workItems.floor',
            'workItems.unit',
            'workItems.room',
            'workItems.subspace',
            'workItems.activityMapping.division',
        ])->findOrFail($id);

        $this->ensureDprAccess($dpr);

        return view('dprs.show', compact('dpr'));
    }

    public function edit($id)
{
    $dpr = Dpr::with([
        'workItems',
        'labours',
        'materials',
        'materialReceived',
        'materialRequired',
        'machineryTools',
        'siteIssues',
        'tomorrowPlans',
        'photos',
    ])->findOrFail($id);

    if ($response = $this->ensureEditable($dpr)) {
        return $response;
    }

    $projects = $this->isEngineer()
        ? auth()->user()->projects
        : Project::all();

    $activities = Activity::where('is_active', true)
        ->orderBy('activity_name')
        ->get();

    $contractors = Contractor::where('status', 1)
        ->orderBy('contractor_name')
        ->get();

    $materials = Material::where('is_active', true)
        ->orderBy('material_name')
        ->get();

    $vendors = Vendor::where('is_active', true)
        ->orderBy('vendor_name')
        ->get();

    $machineries = MachineryTool::all();

    $projectBlocks = \App\Models\ProjectBlock::where('is_active', true)->get();
    $projectFloors = \App\Models\ProjectFloor::where('is_active', true)->get();
    $projectUnits = \App\Models\ProjectUnit::where('is_active', true)->get();
    $projectRooms = \App\Models\ProjectRoom::where('is_active', true)->get();
    $projectSubspaces = \App\Models\ProjectSubspace::where('is_active', true)->get();

    $activityMappings = \App\Models\ActivityMapping::with('division')
        ->where('is_active', true)
        ->orderBy('activity_name')
        ->get();

    $activityDivisions = \App\Models\ActivityDivision::where('is_active', true)
        ->orderBy('sequence')
        ->get();

    $materialCategories = \App\Models\MaterialCategory::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    $labourCategories = \App\Models\LabourCategory::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    $labourTypes = \App\Models\LabourType::whereNotNull('labour_category_id')
        ->orderBy('labour_type_name')
        ->get();

    return view('dprs.edit', compact(
        'dpr',
        'projects',
        'activities',
        'contractors',
        'materials',
        'vendors',
        'machineries',
        'projectBlocks',
        'projectFloors',
        'projectUnits',
        'projectRooms',
        'projectSubspaces',
        'activityMappings',
        'activityDivisions',
        'materialCategories',
        'labourCategories',
        'labourTypes'
    ));
}

    public function update(Request $request, $id)
{
    $dpr = Dpr::findOrFail($id);

    if ($response = $this->ensureEditable($dpr)) {
        return $response;
    }

    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'dpr_date' => 'required|date',
        'weather' => 'nullable|string|max:255',
        'remarks' => 'nullable|string',
        'activity_id.*' => 'required|exists:activities,id',
        'contractor_id.*' => 'nullable|exists:contractors,id',
        'quantity_completed.*' => 'required|numeric|min:0',
        'photos.*' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
    ]);

    $this->ensureEngineerProjectAccess($request->project_id);

    DB::transaction(function () use ($request, $dpr) {

        $oldValues = $dpr->only([
            'project_id',
            'dpr_date',
            'weather',
            'remarks',
            'status',
            'pmo_remarks',
        ]);

        $newStatus = $dpr->status === 'Rejected'
            ? 'Pending'
            : $dpr->status;

        $dpr->update([
            'project_id' => $request->project_id,
            'dpr_date' => $request->dpr_date,
            'weather' => $request->weather,
            'remarks' => $request->remarks,
            'status' => $newStatus,
            'pmo_remarks' => $newStatus === 'Pending' ? null : $dpr->pmo_remarks,
        ]);

        DprWorkItem::where('dpr_id', $dpr->id)->delete();
        DprLabour::where('dpr_id', $dpr->id)->delete();
        DprMaterial::where('dpr_id', $dpr->id)->delete();
        DprMaterialReceived::where('dpr_id', $dpr->id)->delete();
        DprMaterialRequired::where('dpr_id', $dpr->id)->delete();
        DprMachineryTool::where('dpr_id', $dpr->id)->delete();
        SiteIssue::where('dpr_id', $dpr->id)->delete();
        TomorrowPlan::where('dpr_id', $dpr->id)->delete();

        foreach ($request->activity_id as $index => $activityId) {
            DprWorkItem::create([
                'dpr_id' => $dpr->id,
                'activity_id' => $activityId,
                'activity_mapping_id' => $request->activity_mapping_id[$index] ?? null,
                'project_block_id' => $request->project_block_id[$index] ?? null,
                'project_floor_id' => $request->project_floor_id[$index] ?? null,
                'project_unit_id' => $request->project_unit_id[$index] ?? null,
                'project_room_id' => $request->project_room_id[$index] ?? null,
                'project_subspace_id' => $request->project_subspace_id[$index] ?? null,
                'contractor_id' => $request->contractor_id[$index] ?? null,
                'quantity_completed' => $request->quantity_completed[$index],
                'remarks' => $request->work_remarks[$index] ?? null,
            ]);
        }

        if ($request->labour_type) {
            foreach ($request->labour_type as $index => $labourTypeId) {
                if (!$labourTypeId) continue;

                $male = $request->male_count[$index] ?? 0;
                $female = $request->female_count[$index] ?? 0;

                DprLabour::create([
                    'dpr_id' => $dpr->id,
                    'labour_type_id' => $labourTypeId,
                    'male_count' => $male,
                    'female_count' => $female,
                    'local_count' => $request->local_count[$index] ?? 0,
                    'non_local_count' => $request->non_local_count[$index] ?? 0,
                    'total_count' => $male + $female,
                ]);
            }
        }

        if ($request->material_id) {
            foreach ($request->material_id as $index => $materialId) {
                if (!$materialId) continue;

                DprMaterial::create([
                    'dpr_id' => $dpr->id,
                    'material_id' => $materialId,
                    'quantity_used' => $request->quantity_used[$index] ?? 0,
                ]);
            }
        }

        if ($request->received_material_id) {
            foreach ($request->received_material_id as $index => $materialId) {
                if (!$materialId) continue;

                DprMaterialReceived::create([
                    'dpr_id' => $dpr->id,
                    'material_id' => $materialId,
                    'vendor_id' => $request->vendor_id[$index] ?? null,
                    'quantity_received' => $request->quantity_received[$index] ?? 0,
                    'challan_number' => $request->challan_number[$index] ?? null,
                    'bill_number' => $request->bill_number[$index] ?? null,
                    'unit' => $request->received_unit[$index] ?? null,
                ]);
            }
        }

        if ($request->required_material_id) {
            foreach ($request->required_material_id as $index => $materialId) {
                if (!$materialId) continue;

                DprMaterialRequired::create([
                    'dpr_id' => $dpr->id,
                    'material_id' => $materialId,
                    'required_quantity' => $request->required_quantity[$index] ?? 0,
                    'required_date' => $request->required_date[$index] ?? null,
                    'priority' => $request->priority[$index] ?? 'Normal',
                    'reason' => $request->reason[$index] ?? null,
                    'remarks' => $request->required_remarks[$index] ?? null,
                    'unit' => $request->required_unit[$index] ?? null,
                ]);
            }
        }

        if ($request->machinery_tool_id) {
            foreach ($request->machinery_tool_id as $index => $machineId) {
                if (!$machineId) continue;

                DprMachineryTool::create([
                    'dpr_id' => $dpr->id,
                    'machinery_tool_id' => $machineId,
                    'quantity' => $request->machine_quantity[$index] ?? 1,
                    'usage_hours' => $request->usage_hours[$index] ?? 0,
                    'working_condition' => $request->working_condition[$index] ?? 'Working',
                    'remarks' => $request->machine_remarks[$index] ?? null,
                ]);
            }
        }

        if ($request->issue_type) {
            foreach ($request->issue_type as $index => $issueType) {
                if (!$issueType) continue;

                SiteIssue::create([
                    'dpr_id' => $dpr->id,
                    'issue_type' => $issueType,
                    'related_activity' => $request->related_activity[$index] ?? null,
                    'description' => $request->issue_description[$index] ?? null,
                    'responsible_person' => $request->responsible_person[$index] ?? null,
                    'priority' => $request->issue_priority[$index] ?? 'Medium',
                    'status' => $request->issue_status[$index] ?? 'Open',
                    'remarks' => $request->issue_remarks[$index] ?? null,
                ]);
            }
        }

        if ($request->plan_activity_id) {
            foreach ($request->plan_activity_id as $index => $activityId) {
                if (!$activityId) continue;

                TomorrowPlan::create([
                    'dpr_id' => $dpr->id,
                    'activity_id' => $activityId,
                    'planned_quantity' => $request->planned_quantity[$index] ?? 0,
                    'unit' => $request->planned_unit[$index] ?? null,
                    'planned_labour' => $request->planned_labour[$index] ?? null,
                    'materials_required' => $request->planned_materials[$index] ?? null,
                    'machinery_required' => $request->planned_machinery[$index] ?? null,
                    'risks_constraints' => $request->planned_risks[$index] ?? null,
                ]);
            }
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('dpr_photos', 'public');

                DprPhoto::create([
                    'dpr_id' => $dpr->id,
                    'photo_path' => $path,
                ]);
            }
        }

        AuditHelper::log(
            'DPR',
            $oldValues['status'] === 'Rejected' ? 'Resubmitted' : 'Updated',
            'Dpr',
            $dpr->id,
            $oldValues['status'] === 'Rejected'
                ? 'Rejected DPR corrected and resubmitted for PMO review'
                : 'DPR fully updated before PMO approval',
            $oldValues,
            $dpr->only([
                'project_id',
                'dpr_date',
                'weather',
                'remarks',
                'status',
                'pmo_remarks',
            ])
        );
    });

    return redirect('/dprs')
        ->with('success', 'DPR updated successfully.');
}

    public function destroy($id)
    {
        $dpr = Dpr::findOrFail($id);

        $this->ensureDprAccess($dpr);

        if ($dpr->status === 'Approved') {
            return redirect('/dprs')
                ->with('success', 'Approved DPR cannot be deleted.');
        }

        if ($this->isEngineer() && !in_array($dpr->status, ['Pending', 'Rejected'])) {
    return redirect('/dprs')
        ->with('success', 'This DPR cannot be deleted at the current stage.');
}

        AuditHelper::log(
            'DPR',
            'Deleted',
            'Dpr',
            $dpr->id,
            'DPR deleted',
            $dpr->only([
                'id',
                'project_id',
                'user_id',
                'dpr_date',
                'weather',
                'remarks',
                'status',
                'pmo_remarks',
            ]),
            null
        );

        $dpr->delete();

        return redirect('/dprs')
            ->with('success', 'DPR deleted successfully.');
    }

    public function downloadPdf($id)
    {
        $dpr = Dpr::with([
            'project',
            'user',
            'workItems.activity',
            'workItems.contractor',
            'photos',
            'labours.labourType',
            'materials.material',
            'materialReceived.material',
            'materialReceived.vendor',
            'materialRequired.material',
            'machineryTools.machineryTool',
            'siteIssues',
            'tomorrowPlans.activity',
            'workItems.block',
            'workItems.floor',
            'workItems.unit',
            'workItems.room',
            'workItems.subspace',
            'workItems.activityMapping.division',
        ])->findOrFail($id);

        $this->ensureDprAccess($dpr);

        $pdf = Pdf::loadView('dprs.pdf', compact('dpr'));

        $date = \Carbon\Carbon::parse($dpr->dpr_date)->format('d-m-Y');

        $fileName =
            $date . '_' .
            str_replace(' ', '-', $dpr->project->project_name) . '_' .
            str_replace(' ', '-', $dpr->user->name) .
            '.pdf';

        return $pdf->download($fileName);
    }

    /**
 * Return Labour Attendance sheets matching the selected DPR project and date.
 */
public function labourAttendance(Request $request): JsonResponse
{
    $validated = $request->validate([
        'project_id' => [
            'required',
            'integer',
            'exists:projects,id',
        ],
        'attendance_date' => [
            'required',
            'date',
        ],
    ]);

    $projectId = (int) $validated['project_id'];
    $attendanceDate = $validated['attendance_date'];

    $this->ensureEngineerProjectAccess($projectId);

    $attendances = LabourAttendance::query()
        ->with([
            'project',
            'shift',
            'details.labour',
            'details.attendanceStatus',
            'details.designationRole',
            'details.contractor',
        ])
        ->where('project_id', $projectId)
        ->whereDate('attendance_date', $attendanceDate)
        ->where('is_active', true)
        ->whereIn('status', [
            'submitted',
            'approved',
            'reopened',
        ])
        ->orderBy('shift_id')
        ->orderBy('id')
        ->get();

    if ($attendances->isEmpty()) {
        return response()->json([
            'success' => true,
            'attendance_found' => false,
            'message' => 'No submitted or approved Labour Attendance was found for the selected project and date.',
            'attendance_count' => 0,
            'attendance_ids' => [],
            'attendances' => [],
        ]);
    }

    $attendanceData = $attendances->map(function (
        LabourAttendance $attendance
    ): array {
        $details = $attendance->details;

        $normalHours = (float) $details->sum(
            fn ($detail) => (float) ($detail->normal_hours ?? 0)
        );

        $otHours = (float) $details->sum(
            fn ($detail) => (float) ($detail->ot_hours ?? 0)
        );

        $labourDetails = $details->map(function ($detail): array {
            return [
                'id' => $detail->id,
                'labour_id' => $detail->labour_id,

                'labour_name' =>
                    $detail->labour?->full_name
                    ?? $detail->labour?->name
                    ?? 'Unknown Labour',

                'labour_code' =>
                    $detail->labour?->labour_code
                    ?? null,

                'designation' =>
                    $detail->designationRole?->name
                    ?? $detail->designationRole?->designation_name
                    ?? $detail->designationRole?->role_name
                    ?? null,

                'contractor' =>
                    $detail->contractor?->contractor_name
                    ?? $detail->contractor?->name
                    ?? null,

                'attendance_status' =>
                    $detail->attendanceStatus?->name
                    ?? $detail->attendanceStatus?->status_name
                    ?? $detail->attendanceStatus?->code
                    ?? 'Not Specified',

                'attendance_status_code' =>
                    $detail->attendanceStatus?->code
                    ?? null,

                'normal_hours' =>
                    (float) ($detail->normal_hours ?? 0),

                'ot_hours' =>
                    (float) ($detail->ot_hours ?? 0),

                'remarks' => $detail->remarks,
            ];
        })->values();

        return [
            'id' => $attendance->id,

            'attendance_number' =>
                $attendance->attendance_number,

            'attendance_date' =>
                optional($attendance->attendance_date)
                    ->format('Y-m-d'),

            'shift_id' => $attendance->shift_id,

            'shift' =>
                $attendance->shift?->shift_name
                ?? $attendance->shift?->name
                ?? 'General Shift',

            'status' => $attendance->status,

            'display_status' =>
                $attendance->display_status
                ?? ucfirst($attendance->status),

            'is_approved' =>
                strtolower((string) $attendance->status) === 'approved',

            'total_labour' => $details->count(),

            'normal_hours' => round($normalHours, 2),

            'ot_hours' => round($otHours, 2),

            'total_hours' =>
                round($normalHours + $otHours, 2),

            'remarks' => $attendance->remarks,

            'view_url' => route(
                'labour-attendances.show',
                $attendance
            ),

            'details' => $labourDetails,
        ];
    })->values();

    return response()->json([
        'success' => true,
        'attendance_found' => true,
        'message' => $attendances->count() === 1
            ? 'One Labour Attendance sheet was found.'
            : "{$attendances->count()} Labour Attendance sheets were found.",

        'attendance_count' => $attendances->count(),

        'attendance_ids' =>
            $attendances->pluck('id')->values(),

        'attendances' => $attendanceData,
    ]);
}

    public function getFloors($block)
    {
        return response()->json(
            \App\Models\ProjectFloor::where('project_block_id', $block)
                ->where('is_active', true)
                ->orderBy('sequence')
                ->get(['id', 'name'])
        );
    }

    public function getUnits($floor)
    {
        return response()->json(
            \App\Models\ProjectUnit::where('project_floor_id', $floor)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function getRooms($unit)
    {
        return response()->json(
            \App\Models\ProjectRoom::where('project_unit_id', $unit)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function getSubspaces($room)
    {
        return response()->json(
            \App\Models\ProjectSubspace::where('project_room_id', $room)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }
}