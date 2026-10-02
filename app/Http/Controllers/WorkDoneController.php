<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Activity;
use App\Models\ActivityDivision;
use App\Models\ActivityMapping;
use App\Models\Contractor;
use App\Models\DprWorkPhoto;
use App\Models\MaterialConsumed;
use App\Services\MaterialInventoryService;
use App\Models\LabourAttendanceDetail;
use App\Models\WorkDoneItemLabour;
use App\Models\LabourGroup;
use App\Models\DesignationRole;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ProjectUnit;
use App\Models\WorkDoneHeader;
use App\Models\WorkDoneItem;
use App\Models\WorkActivity;
use App\Models\WorkStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class WorkDoneController extends Controller
{
    private const PHOTO_TYPES = [
        'Before Work',
        'During Work',
        'Completed Work',
        'Quality Inspection',
        'Measurement',
        'Hidden Work',
        'Other',
    ];

    private const EXECUTION_STATUSES = [
        'Not Started',
        'In Progress',
        'Completed',
        'On Hold',
        'Rework',
        'Inspection Pending',
    ];

    /**
     * Display Daily Work Execution headers.
     */
    public function index(Request $request): View
    {
        $query = WorkDoneHeader::query()
            ->with([
                'project',
                'engineer',
                'items' => fn ($itemQuery) => $itemQuery
                    ->with([
                        'workStage',
                        'activityDivision',
                        'activity',
                        'activityMapping',
                        'workActivity',
                        'contractor',
                        'block',
                        'floor',
                        'unitLocation',
                        'room',
                        'subspace',
                        'materialConsumptions.items.materialType',
                        'materialConsumptions.items.brand',
                        'materialConsumptions.items.specification',
                        'materialConsumptions.items.grade',
                        'materialConsumptions.items.unit',
                        'photos.uploader',
                        'dpr',
                    ]),
            ]);

        if ($this->isEngineer()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('project_id')) {
            $query->where(
                'project_id',
                $request->integer('project_id')
            );
        }

        if ($request->filled('work_date')) {
            $query->whereDate(
                'work_date',
                $request->input('work_date')
            );
        }

        if (
            $request->filled('user_id')
            && ! $this->isEngineer()
        ) {
            $query->where(
                'user_id',
                $request->integer('user_id')
            );
        }

        if ($request->filled('dpr_link')) {
            if ($request->input('dpr_link') === 'linked') {
                $query->whereHas(
                    'items',
                    fn (Builder $itemQuery) =>
                        $itemQuery->whereNotNull('dpr_id')
                );
            }

            if ($request->input('dpr_link') === 'unlinked') {
                $query->whereHas(
                    'items',
                    fn (Builder $itemQuery) =>
                        $itemQuery->whereNull('dpr_id')
                );
            }
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('remarks', 'like', "%{$search}%")
                    ->orWhereHas(
                        'project',
                        fn (Builder $projectQuery) =>
                            $projectQuery->where(
                                'project_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.activity',
                        fn (Builder $activityQuery) =>
                            $activityQuery->where(
                                'activity_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.activityMapping',
                        fn (Builder $mappingQuery) =>
                            $mappingQuery->where(
                                'activity_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.workActivity',
                        fn (Builder $activityQuery) =>
                            $activityQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                    )
                    ->orWhereHas(
                        'items.contractor',
                        fn (Builder $contractorQuery) =>
                            $contractorQuery->where(
                                'contractor_name',
                                'like',
                                "%{$search}%"
                            )
                    );
            });
        }

        $workDoneHeaders = $query
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $projects = $this->availableProjects();

        $engineers = $this->isEngineer()
            ? collect([auth()->user()])
            : \App\Models\User::query()
                ->whereHas(
                    'role',
                    fn (Builder $roleQuery) =>
                        $roleQuery->where('name', 'Engineer')
                )
                ->orderBy('name')
                ->get();

        return view(
            'work-done.index',
            compact(
                'workDoneHeaders',
                'projects',
                'engineers'
            )
        );
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view(
            'work-done.create',
            $this->formData()
        );
    }

    /**
     * Store one header with multiple Work Activity cards.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $projectId = (int) $validated['project_id'];
        $workDate = $validated['work_date'];

        $this->ensureProjectAccess($projectId);

        $this->validateWorks(
            works: $validated['works'],
            projectId: $projectId,
            workDate: $workDate,
            engineerId: auth()->id()
        );

        $storedPaths = [];

        try {
            $header = DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $projectId,
                    $workDate,
                    &$storedPaths
                ): WorkDoneHeader {
                    /*
                     * One header per Project + Engineer + Date.
                     * If an engineer returns later and adds more activities,
                     * we append to the same daily header.
                     */
                    $header = WorkDoneHeader::query()
                        ->firstOrCreate(
                            [
                                'project_id' => $projectId,
                                'user_id' => auth()->id(),
                                'work_date' => $workDate,
                            ],
                            [
                                'status' => 'Draft',
                                'remarks' => $this->nullableTrim(
                                    $validated['remarks'] ?? null
                                ),
                            ]
                        );

                    if (
                        array_key_exists('remarks', $validated)
                        && $header->remarks !== $this->nullableTrim(
                            $validated['remarks']
                        )
                    ) {
                        $header->update([
                            'remarks' => $this->nullableTrim(
                                $validated['remarks']
                            ),
                        ]);
                    }

                    $startingSortOrder = (int) $header->items()
                        ->max('sort_order');

                    foreach (
                        $validated['works']
                        as $workIndex => $workData
                    ) {
                        $workItem = $header->items()->create(
                            $this->workItemAttributes(
                                workData: $workData,
                                sortOrder: $startingSortOrder
                                    + $workIndex
                                    + 1
                            )
                        );
                        $this->saveReportedResources($workItem, $workData, $projectId, $workDate);

                        $this->storeWorkPhotos(
                            request: $request,
                            workItem: $workItem,
                            workIndex: $workIndex,
                            storedPaths: $storedPaths
                        );
                    }

                    $header->load($this->headerRelationships());

                    AuditHelper::log(
                        'Daily Work Execution',
                        'Created',
                        'WorkDoneHeader',
                        $header->id,
                        'Daily Work Execution activities saved.',
                        null,
                        $this->auditHeaderValues($header)
                    );

                    return $header;
                }
            );

            return redirect()
                ->route('work-done.create')
                ->with(
                    'success',
                    'Work Done saved successfully. You can add more activities for the same Project and Date if required.'
                );
        } catch (ValidationException $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to save Work Done.'
                );
        }
    }

    /**
     * Display one Daily Work Execution header.
     */
    public function show(
        WorkDoneHeader $workDone
    ): View {
        $workDone->load(
            $this->headerRelationships()
        );

        $this->ensureHeaderAccess($workDone);

        $itemIds = $workDone->items->pluck('id')->all();

        $reportedMaterials = DB::table('work_done_reported_materials as r')
            ->leftJoin('material_types as mt', 'mt.id', '=', 'r.material_type_id')
            ->leftJoin('brand_masters as bm', 'bm.id', '=', 'r.brand_master_id')
            ->leftJoin('material_specifications as ms', 'ms.id', '=', 'r.material_specification_id')
            ->leftJoin('material_grades as mg', 'mg.id', '=', 'r.material_grade_id')
            ->leftJoin('unit_masters as um', 'um.id', '=', 'r.unit_master_id')
            ->whereIn('r.work_done_item_id', $itemIds)
            ->orderBy('r.sort_order')
            ->select('r.*', 'mt.material_type_name as material_name', 'bm.brand_name as brand_name',
                'ms.specification_name as specification_name', 'mg.grade_name as grade_name', 'um.unit_name as unit_name')
            ->get()->groupBy('work_done_item_id');

        $reportedLabours = DB::table('work_done_item_labours as wl')
            ->leftJoin('labour_groups as lg', 'lg.id', '=', 'wl.labour_group_id')
            ->leftJoin('designation_roles as dr', 'dr.id', '=', 'wl.designation_role_id')
            ->whereIn('wl.work_done_item_id', $itemIds)
            ->orderBy('wl.sort_order')
            ->select('wl.*', 'lg.name as labour_group_name', 'dr.name as designation_name')
            ->get()->groupBy('work_done_item_id');

        $reportedMachinery = DB::table('work_done_item_machinery')
            ->whereIn('work_done_item_id', $itemIds)
            ->orderBy('sort_order')
            ->get()->groupBy('work_done_item_id');

        return view('work-done.show', compact(
            'workDone', 'reportedMaterials', 'reportedLabours', 'reportedMachinery'
        ));
    }

    /**
     * Show edit form.
     */
    public function edit(
        WorkDoneHeader $workDone
    ): View {
        $workDone->load(
            $this->headerRelationships()
        );

        $this->ensureHeaderAccess($workDone);

        return view(
            'work-done.edit',
            array_merge(
                compact('workDone'),
                $this->formData(
                    projectId: $workDone->project_id,
                    workDate: $workDone->work_date?->format('Y-m-d'),
                    engineerId: $workDone->user_id,
                    headerId: $workDone->id
                )
            )
        );
    }

    /**
     * Update header and its Work Activities.
     *
     * Existing DPR-linked Work Activities are protected and are not deleted
     * or edited by this standalone screen.
     */
    public function update(
        Request $request,
        WorkDoneHeader $workDone
    ): RedirectResponse {
        $workDone->load(
            $this->headerRelationships()
        );

        $this->ensureHeaderAccess($workDone);

        $validated = $this->validatePayload(
            $request,
            updating: true
        );

        $projectId = (int) $validated['project_id'];
        $workDate = $validated['work_date'];

        $this->ensureProjectAccess($projectId);

        if ($projectId !== (int) $workDone->project_id || $workDate !== $workDone->work_date?->format('Y-m-d')) {
            throw ValidationException::withMessages([
                'project_id' => 'Project and Work Date cannot be changed while editing a saved daily execution record.',
            ]);
        }

        $this->validateWorks(
            works: $validated['works'],
            projectId: $projectId,
            workDate: $workDate,
            engineerId: $workDone->user_id,
            headerId: $workDone->id
        );

        $storedPaths = [];
        $pathsToDeleteAfterCommit = [];

        try {
            DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $projectId,
                    $workDate,
                    $workDone,
                    &$storedPaths,
                    &$pathsToDeleteAfterCommit
                ): void {
                    $oldValues =
                        $this->auditHeaderValues($workDone);

                    $workDone->update([
                        'project_id' => $projectId,
                        'work_date' => $workDate,
                        'remarks' => $this->nullableTrim(
                            $validated['remarks'] ?? null
                        ),
                    ]);

                    $submittedExistingIds = collect(
                        $validated['works']
                    )
                        ->pluck('id')
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->values();

                    /*
                     * Delete only unlinked activities omitted from the form.
                     * DPR-linked activities are protected.
                     */
                    $deletableItems = $workDone->items()
                        ->whereNull('dpr_id');

                    if ($submittedExistingIds->isNotEmpty()) {
                        $deletableItems->whereNotIn(
                            'id',
                            $submittedExistingIds
                        );
                    }

                    $itemsToDelete = $deletableItems
                        ->with('photos')
                        ->get();

                    foreach ($itemsToDelete as $itemToDelete) {
                        foreach ($itemToDelete->photos as $photo) {
                            $pathsToDeleteAfterCommit[] =
                                $photo->file_path;
                        }

                        MaterialConsumed::query()
                            ->where(
                                'work_done_item_id',
                                $itemToDelete->id
                            )
                            ->update([
                                'work_done_item_id' => null,
                            ]);

                        $itemToDelete->delete();
                    }

                    foreach (
                        $validated['works']
                        as $workIndex => $workData
                    ) {
                        $existingId = isset($workData['id'])
                            ? (int) $workData['id']
                            : null;

                        if ($existingId) {
                            $workItem = $workDone->items()
                                ->whereKey($existingId)
                                ->firstOrFail();

                            if ($workItem->dpr_id !== null) {
                                /*
                                 * Linked activity is immutable here.
                                 * Keep it exactly as approved/linked.
                                 */
                                continue;
                            }

                            $workItem->update(
                                $this->workItemAttributes(
                                    workData: $workData,
                                    sortOrder: $workIndex + 1
                                )
                            );
                        } else {
                            $workItem = $workDone->items()->create(
                                $this->workItemAttributes(
                                    workData: $workData,
                                    sortOrder: $workIndex + 1
                                )
                            );
                        }
                        $this->saveReportedResources($workItem, $workData, $projectId, $workDate);

                        $removePhotoIds = collect(
                            $workData['remove_photo_ids'] ?? []
                        )
                            ->map(fn ($id) => (int) $id)
                            ->unique()
                            ->values();

                        if ($removePhotoIds->isNotEmpty()) {
                            $photosToRemove = $workItem->photos()
                                ->whereIn('id', $removePhotoIds)
                                ->get();

                            foreach ($photosToRemove as $photo) {
                                $pathsToDeleteAfterCommit[] =
                                    $photo->file_path;

                                $photo->delete();
                            }
                        }

                        $this->storeWorkPhotos(
                            request: $request,
                            workItem: $workItem,
                            workIndex: $workIndex,
                            storedPaths: $storedPaths
                        );
                    }

                    $workDone->load(
                        $this->headerRelationships()
                    );

                    AuditHelper::log(
                        'Daily Work Execution',
                        'Updated',
                        'WorkDoneHeader',
                        $workDone->id,
                        'Daily Work Execution updated.',
                        $oldValues,
                        $this->auditHeaderValues($workDone)
                    );
                }
            );

            $this->deleteStoredPaths(
                $pathsToDeleteAfterCommit
            );

            return redirect()
                ->route('work-done.show', $workDone)
                ->with(
                    'success',
                    'Work Done updated successfully.'
                );
        } catch (ValidationException $exception) {
            $this->deleteStoredPaths($storedPaths);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteStoredPaths($storedPaths);
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update Work Done.'
                );
        }
    }

    /**
     * Delete header only when none of its Work Activities are DPR-linked.
     */
    public function destroy(
        WorkDoneHeader $workDone
    ): RedirectResponse {
        $workDone->load(
            $this->headerRelationships()
        );

        $this->ensureHeaderAccess($workDone);

        abort_if(
            $workDone->items->contains(
                fn (WorkDoneItem $item) =>
                    $item->dpr_id !== null
            ),
            403,
            'This Work Done record contains activities already linked to a DPR.'
        );

        $oldValues =
            $this->auditHeaderValues($workDone);

        $photoPaths = $workDone->items
            ->flatMap(fn (WorkDoneItem $item) =>
                $item->photos->pluck('file_path')
            )
            ->filter()
            ->values()
            ->all();

        DB::transaction(
            function () use (
                $workDone,
                $oldValues
            ): void {
                MaterialConsumed::query()
                    ->whereIn(
                        'work_done_item_id',
                        $workDone->items->pluck('id')
                    )
                    ->update([
                        'work_done_item_id' => null,
                    ]);

                $headerId = $workDone->id;

                $workDone->delete();

                AuditHelper::log(
                    'Daily Work Execution',
                    'Deleted',
                    'WorkDoneHeader',
                    $headerId,
                    'Daily Work Execution deleted.',
                    $oldValues,
                    null
                );
            }
        );

        $this->deleteStoredPaths($photoPaths);

        return redirect()
            ->route('work-done.index')
            ->with(
                'success',
                'Work Done deleted successfully.'
            );
    }

    /**
     * AJAX endpoint used by Create/Edit Material Used section.
     *
     * Returns standalone Material Consumed headers for the same project/date/
     * engineer which are not assigned to another Work Activity.
     */
    public function availableMaterials(Request $request, MaterialInventoryService $inventory): JsonResponse
    {
        $input = $request->validate(['project_id' => ['required','integer','exists:projects,id']]);
        $projectId = (int) $input['project_id'];
        $this->ensureProjectAccess($projectId);
        return response()->json(['data' => $inventory->availableForConsumption($projectId)->values()]);
    }

    /**
     * Validation for header + repeating Work Activity cards.
     */
    /**
     * Operational attendance lookup: no wage/rate/OT monetary fields leave this endpoint.
     */
    public function availableLabour(Request $request): JsonResponse
    {
        $input = $request->validate([
            'project_id' => ['required','integer','exists:projects,id'],
            'work_date' => ['required','date'],
        ]);
        $this->ensureProjectAccess((int) $input['project_id']);
        return response()->json(['data' => $this->attendanceGroupCounts((int) $input['project_id'], $input['work_date'])]);
    }

    private function attendanceGroupCounts(int $projectId, string $date): array
    {
        return LabourAttendanceDetail::query()
            ->join('labour_attendances as a', 'a.id', '=', 'labour_attendance_details.labour_attendance_id')
            ->join('attendance_statuses as s', 's.id', '=', 'labour_attendance_details.attendance_status_id')
            ->join('labours as l', 'l.id', '=', 'labour_attendance_details.labour_id')
            ->join('labour_groups as g', 'g.id', '=', 'l.labour_group_id')
            ->where('a.project_id', $projectId)->whereDate('a.attendance_date', $date)
            ->whereRaw('LOWER(a.status) = ?', ['approved'])
            ->where('a.is_active', 1)->whereNull('a.deleted_at')
            ->where('labour_attendance_details.is_active', 1)
            ->whereNull('labour_attendance_details.deleted_at')
            ->where('s.counts_as_present', 1)
            ->where('l.is_active', 1)->whereNull('l.deleted_at')
            ->selectRaw('g.id as id, g.name as name, COUNT(DISTINCT l.id) as available')
            ->groupBy('g.id', 'g.name')->orderBy('g.name')->get()
            ->map(fn ($r) => ['id'=>(int)$r->id,'name'=>$r->name,'available'=>(int)$r->available])->all();
    }

    /** Reported usage only. No MaterialConsumed writes, reservations or inventory changes. */
    private function saveReportedResources(WorkDoneItem $item, array $work, int $projectId, string $workDate): void
    {
        if (array_key_exists('materials_used', $work)) {
            $inventory = app(MaterialInventoryService::class)->availableForConsumption($projectId)->keyBy('stock_key');
            $totals = [];
            foreach ($work['materials_used'] as $i => $row) {
                $key = $row['stock_key'];
                $stock = $inventory->get($key);
                if (!$stock) {
                    throw ValidationException::withMessages(["works.materials_used.{$i}.stock_key" => 'This material is not currently available in project inventory. Receive it in Material Tracking first.']);
                }
                $totals[$key] = ($totals[$key] ?? 0) + (float)$row['quantity'];
                if ($totals[$key] > (float)$stock['available_qty'] + 0.000001) {
                    throw ValidationException::withMessages(["works.materials_used.{$i}.quantity" => 'Reported quantity exceeds currently available project inventory for ' . $stock['material_type_name'] . '.']);
                }
            }
            DB::table('work_done_reported_materials')->where('work_done_item_id', $item->id)->delete();
            foreach (array_values($work['materials_used']) as $i => $row) {
                $stock = $inventory->get($row['stock_key']);
                DB::table('work_done_reported_materials')->insert([
                    'work_done_item_id'=>$item->id, 'stock_key'=>$stock['stock_key'],
                    'material_type_id'=>$stock['material_type_id'], 'brand_master_id'=>$stock['brand_master_id'],
                    'material_specification_id'=>$stock['material_specification_id'], 'material_grade_id'=>$stock['material_grade_id'],
                    'unit_master_id'=>$stock['unit_master_id'], 'quantity_reported'=>$row['quantity'],
                    'sort_order'=>$i+1, 'created_at'=>now(), 'updated_at'=>now(),
                ]);
            }
        }
        if (array_key_exists('machinery_used', $work)) {
            DB::table('work_done_item_machinery')->where('work_done_item_id', $item->id)->delete();
            foreach (array_values($work['machinery_used']) as $i => $machine) {
                DB::table('work_done_item_machinery')->insert([
                    'work_done_item_id' => $item->id,
                    'equipment_name' => trim($machine['equipment_name']),
                    'quantity' => (int) $machine['quantity'],
                    'operating_hours' => $machine['operating_hours'] ?? null,
                    'sort_order' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        if (array_key_exists('labours', $work)) {
            $available = collect($this->attendanceGroupCounts($projectId, $workDate))->keyBy('id');
            $totals = [];
            foreach ($work['labours'] as $i => $row) {
                $groupId = (int)$row['labour_group_id'];
                $totals[$groupId] = ($totals[$groupId] ?? 0) + (int)$row['quantity'];
                $group = $available->get($groupId);
                if ($group && $totals[$groupId] > $group['available']) {
                    throw ValidationException::withMessages(["works.labours.{$i}.quantity" =>
                        ($group['name'] ?? 'Selected labour group') . ': ' . $totals[$groupId] . ' entered, but only ' . ($group['available'] ?? 0) . ' are present in approved attendance.']);
                }
            }
            $item->labours()->delete();
            foreach (array_values($work['labours']) as $i => $row) {
                $item->labours()->create(['labour_group_id'=>$row['labour_group_id'], 'quantity'=>$row['quantity'], 'sort_order'=>$i+1]);
            }
        }
    }

    private function validatePayload(
        Request $request,
        bool $updating = false
    ): array {
        return $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'work_date' => [
                'required',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'works' => [
                'required',
                'array',
                'min:1',
                'max:50',
            ],

            'works.*.id' => [
                $updating ? 'nullable' : 'prohibited',
                'integer',
                'exists:work_done_items,id',
            ],

            'works.*.work_stage_id' => [
                'nullable',
                'integer',
                'exists:work_stages,id',
            ],

            'works.*.activity_division_id' => [
                'nullable',
                'integer',
                'exists:activity_divisions,id',
            ],

            'works.*.activity_id' => [
                'required_without:works.*.work_activity_id',
                'integer',
                'exists:activities,id',
            ],

            'works.*.work_activity_id' => [
                'required_without:works.*.activity_id',
                'integer',
                'exists:work_activities,id',
            ],

            'works.*.activity_mapping_id' => [
                'nullable',
                'integer',
                'exists:activity_mappings,id',
            ],

            'works.*.contractor_id' => [
                'nullable',
                'integer',
                'exists:contractors,id',
            ],

            'works.*.project_block_id' => [
                'nullable',
                'integer',
                'exists:project_blocks,id',
            ],

            'works.*.project_floor_id' => [
                'nullable',
                'integer',
                'exists:project_floors,id',
            ],

            'works.*.project_unit_id' => [
                'nullable',
                'integer',
                'exists:project_units,id',
            ],

            'works.*.project_room_id' => [
                'nullable',
                'integer',
                'exists:project_rooms,id',
            ],

            'works.*.project_subspace_id' => [
                'nullable',
                'integer',
                'exists:project_subspaces,id',
            ],

            'works.*.quantity_completed' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'works.*.unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'works.*.progress_percentage' => [
                'nullable',
                'numeric',
                'between:0,100',
            ],

            'works.*.execution_status' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    self::EXECUTION_STATUSES
                ),
            ],

            'works.*.remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'works.*.material_consumed_ids' => [
                'nullable',
                'array',
                'max:100',
            ],

            'works.*.material_consumed_ids.*' => [
                'integer',
                'exists:material_consumeds,id',
            ],

            'works.*.labours' => ['nullable', 'array', 'max:100'],
            'works.*.labours.*.labour_group_id' => ['required', 'integer', 'exists:labour_groups,id'],

            'works.*.labours.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'works.*.machinery_used' => ['sometimes', 'array', 'max:100'],
            'works.*.machinery_used.*.equipment_name' => ['required', 'string', 'max:200'],
            'works.*.machinery_used.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'works.*.machinery_used.*.operating_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'works.*.materials_used' => ['sometimes', 'array', 'max:100'],
            'works.*.materials_used.*.stock_key' => ['required', 'string', 'max:255'],
            'works.*.materials_used.*.quantity' => ['required', 'numeric', 'gt:0'],

            'works.*.photos' => [
                'nullable',
                'array',
                'max:30',
            ],

            'works.*.photos.*.file' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'works.*.photos.*.photo_type' => [
                'nullable',
                'string',
                'in:' . implode(
                    ',',
                    self::PHOTO_TYPES
                ),
            ],

            'works.*.photos.*.caption' => [
                'nullable',
                'string',
                'max:500',
            ],

            'works.*.remove_photo_ids' => [
                'nullable',
                'array',
            ],

            'works.*.remove_photo_ids.*' => [
                'integer',
                'exists:dpr_work_photos,id',
            ],
        ], [
            'works.required' =>
                'Add at least one Work Activity.',

            'works.min' =>
                'Add at least one Work Activity.',

            'works.*.quantity_completed.gt' =>
                'Quantity completed must be greater than zero.',

            'works.*.progress_percentage.between' =>
                'Progress percentage must be between 0 and 100.',

            'works.*.photos.*.file.max' =>
                'Each Work Done photo may be up to 10 MB.',
        ]);
    }

    /**
     * Cross-field validation Laravel rules cannot express cleanly.
     */
    private function validateWorks(
        array $works,
        int $projectId,
        string $workDate,
        int $engineerId,
        ?int $headerId = null
    ): void {
        $errors = [];

        foreach ($works as $index => $work) {
            $prefix = "works.{$index}";

            if (! empty($work['work_activity_id'])) {
                $canonicalActivity = WorkActivity::query()
                    ->find($work['work_activity_id']);

                if (! $canonicalActivity
                    || ! $canonicalActivity->is_active
                    || ! $canonicalActivity->is_selectable) {
                    $errors["{$prefix}.work_activity_id"][] =
                        'Select an active Work Activity from the Work Execution Master.';
                }
            }


            if (
                ! empty($work['id'])
                && $headerId
            ) {
                $belongsToHeader =
                    WorkDoneItem::query()
                        ->whereKey($work['id'])
                        ->where(
                            'work_done_header_id',
                            $headerId
                        )
                        ->exists();

                if (! $belongsToHeader) {
                    $errors["{$prefix}.id"][] =
                        'Invalid Work Activity.';
                }
            }

            if (
                ! empty($work['project_block_id'])
                && ! ProjectBlock::query()
                    ->whereKey(
                        $work['project_block_id']
                    )
                    ->where(
                        'project_id',
                        $projectId
                    )
                    ->exists()
            ) {
                $errors["{$prefix}.project_block_id"][] =
                    'The selected Block does not belong to the selected Project.';
            }

            if (! empty($work['project_floor_id'])) {
                $floorQuery =
                    ProjectFloor::query()
                        ->whereKey(
                            $work['project_floor_id']
                        )
                        ->where(
                            'project_id',
                            $projectId
                        );

                if (! empty($work['project_block_id'])) {
                    $floorQuery->where(
                        'project_block_id',
                        $work['project_block_id']
                    );
                }

                if (! $floorQuery->exists()) {
                    $errors["{$prefix}.project_floor_id"][] =
                        'The selected Floor does not belong to the selected Project/Block.';
                }
            }

            if (! empty($work['project_unit_id'])) {
                $unitQuery =
                    ProjectUnit::query()
                        ->whereKey(
                            $work['project_unit_id']
                        )
                        ->where(
                            'project_id',
                            $projectId
                        );

                if (! empty($work['project_floor_id'])) {
                    $unitQuery->where(
                        'project_floor_id',
                        $work['project_floor_id']
                    );
                }

                if (! $unitQuery->exists()) {
                    $errors["{$prefix}.project_unit_id"][] =
                        'The selected Unit does not belong to the selected Project/Floor.';
                }
            }

            if (! empty($work['project_room_id'])) {
                $roomQuery =
                    ProjectRoom::query()
                        ->whereKey(
                            $work['project_room_id']
                        );

                if (! empty($work['project_unit_id'])) {
                    $roomQuery->where(
                        'project_unit_id',
                        $work['project_unit_id']
                    );
                }

                if (! $roomQuery->exists()) {
                    $errors["{$prefix}.project_room_id"][] =
                        'The selected Room does not belong to the selected Unit.';
                }
            }

            if (! empty($work['project_subspace_id'])) {
                $subspaceQuery =
                    ProjectSubspace::query()
                        ->whereKey(
                            $work['project_subspace_id']
                        );

                if (! empty($work['project_room_id'])) {
                    $subspaceQuery->where(
                        'project_room_id',
                        $work['project_room_id']
                    );
                }

                if (! $subspaceQuery->exists()) {
                    $errors["{$prefix}.project_subspace_id"][] =
                        'The selected Sub-space does not belong to the selected Room.';
                }
            }

            if (! empty($work['activity_mapping_id'])) {
                $mapping = ActivityMapping::query()
                    ->find(
                        $work['activity_mapping_id']
                    );

                if (
                    $mapping
                    && ! empty($mapping->activity_id)
                    && ! empty($work['activity_id'])
                    && (int) $mapping->activity_id
                        !== (int) $work['activity_id']
                ) {
                    $errors["{$prefix}.activity_mapping_id"][] =
                        'The selected Activity Mapping does not match the selected Activity.';
                }

                if (
                    $mapping
                    && ! empty($work['activity_division_id'])
                    && ! empty(
                        $mapping->activity_division_id
                    )
                    && (int) $mapping->activity_division_id
                        !== (int) $work['activity_division_id']
                ) {
                    $errors["{$prefix}.activity_mapping_id"][] =
                        'The selected Activity Mapping does not match the selected Activity Division.';
                }
            }

            foreach (
                collect(
                    $work['material_consumed_ids'] ?? []
                )
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                as $materialConsumedId
            ) {
                $materialConsumed =
                    MaterialConsumed::query()
                        ->find($materialConsumedId);

                if (! $materialConsumed) {
                    continue;
                }

                if (
                    (int) $materialConsumed->project_id
                    !== $projectId
                ) {
                    $errors["{$prefix}.material_consumed_ids"][] =
                        'A selected Material Consumed record belongs to a different Project.';
                    continue;
                }

                if (
                    optional(
                        $materialConsumed->consumed_date
                    )->format('Y-m-d')
                    !== $workDate
                ) {
                    $errors["{$prefix}.material_consumed_ids"][] =
                        'A selected Material Consumed record belongs to a different Date.';
                    continue;
                }

                if (
                    $this->isEngineer()
                    && (int) $materialConsumed->user_id
                        !== $engineerId
                ) {
                    $errors["{$prefix}.material_consumed_ids"][] =
                        'A selected Material Consumed record belongs to another Engineer.';
                    continue;
                }

                $currentWorkItemId =
                    ! empty($work['id'])
                        ? (int) $work['id']
                        : null;

                if (
                    $materialConsumed->work_done_item_id
                    && (int) $materialConsumed->work_done_item_id
                        !== $currentWorkItemId
                ) {
                    $errors["{$prefix}.material_consumed_ids"][] =
                        'A selected Material Consumed record is already linked to another Work Activity.';
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }

    private function workItemAttributes(
        array $workData,
        int $sortOrder
    ): array {
        return [
            'dpr_id' => null,

            'work_stage_id' =>
                $workData['work_stage_id'] ?? null,

            'activity_division_id' =>
                $workData['activity_division_id']
                ?? null,

            'activity_id' =>
                ! empty($workData['activity_id'])
                    ? (int) $workData['activity_id']
                    : null,

            'work_activity_id' =>
                ! empty($workData['work_activity_id'])
                    ? (int) $workData['work_activity_id']
                    : null,

            'activity_mapping_id' =>
                $workData['activity_mapping_id']
                ?? null,

            'contractor_id' =>
                $workData['contractor_id'] ?? null,

            'project_block_id' =>
                $workData['project_block_id'] ?? null,

            'project_floor_id' =>
                $workData['project_floor_id'] ?? null,

            'project_unit_id' =>
                $workData['project_unit_id'] ?? null,

            'project_room_id' =>
                $workData['project_room_id'] ?? null,

            'project_subspace_id' =>
                $workData['project_subspace_id']
                ?? null,

            'quantity_completed' =>
                $workData['quantity_completed'],

            'unit' =>
                $this->nullableTrim(
                    $workData['unit'] ?? null
                ),

            'progress_percentage' =>
                $workData['progress_percentage']
                ?? null,

            'execution_status' =>
                $workData['execution_status']
                ?? 'In Progress',

            'remarks' =>
                $this->nullableTrim(
                    $workData['remarks'] ?? null
                ),

            'sort_order' => $sortOrder,
        ];
    }

    /**
     * Link existing Material Consumed header records to this Work Activity.
     */
    private function linkMaterialConsumptions(
        WorkDoneItem $workItem,
        array $materialConsumedIds,
        int $projectId,
        string $workDate,
        int $engineerId,
        bool $allowCurrentlyLinkedToItem = false
    ): void {
        $selectedIds = collect($materialConsumedIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        /*
         * Unlink old material records which were removed from this activity.
         */
        MaterialConsumed::query()
            ->where(
                'work_done_item_id',
                $workItem->id
            )
            ->when(
                $selectedIds->isNotEmpty(),
                fn (Builder $query) =>
                    $query->whereNotIn(
                        'id',
                        $selectedIds
                    )
            )
            ->update([
                'work_done_item_id' => null,
            ]);

        if ($selectedIds->isEmpty()) {
            return;
        }

        $query = MaterialConsumed::query()
            ->whereIn('id', $selectedIds)
            ->where('project_id', $projectId)
            ->whereDate('consumed_date', $workDate);

        if ($this->isEngineer()) {
            $query->where(
                'user_id',
                $engineerId
            );
        }

        $query->where(
            function (Builder $builder) use (
                $workItem,
                $allowCurrentlyLinkedToItem
            ) {
                $builder->whereNull(
                    'work_done_item_id'
                );

                if ($allowCurrentlyLinkedToItem) {
                    $builder->orWhere(
                        'work_done_item_id',
                        $workItem->id
                    );
                }
            }
        );

        $eligibleIds = $query
            ->pluck('id');

        if (
            $eligibleIds->count()
            !== $selectedIds->count()
        ) {
            throw ValidationException::withMessages([
                'works' =>
                    'One or more selected Material Consumed records are no longer available for this Work Activity.',
            ]);
        }

        MaterialConsumed::query()
            ->whereIn('id', $eligibleIds)
            ->update([
                'work_done_item_id' =>
                    $workItem->id,
            ]);
    }

    private function storeWorkPhotos(
        Request $request,
        WorkDoneItem $workItem,
        int $workIndex,
        array &$storedPaths
    ): void {
        $photoRows =
            $request->input(
                "works.{$workIndex}.photos",
                []
            );

        $uploadedPhotos =
            $request->file(
                "works.{$workIndex}.photos",
                []
            );

        if (! is_array($uploadedPhotos)) {
            return;
        }

        $workItem->loadMissing([
            'header.project',
            'header.engineer',
            'activity',
            'activityMapping',
            'workActivity',
        ]);

        $projectName =
            $workItem->header
                ?->project
                ?->project_name
            ?? 'Project';

        $activityName =
            $workItem->activity_name
            ?? 'Work';

        $engineerName =
            $workItem->header
                ?->engineer
                ?->name
            ?? auth()->user()?->name
            ?? 'Engineer';

        $datePart =
            $workItem->header
                ?->work_date
                ?->format('Ymd')
            ?? now()->format('Ymd');

        $timePart = now()->format('His');

        $sequence =
            $workItem->photos()->count() + 1;

        foreach (
            $uploadedPhotos
            as $photoIndex => $fileData
        ) {
            $file = is_array($fileData)
                ? ($fileData['file'] ?? null)
                : null;

            if (! $file) {
                continue;
            }

            $metadata =
                $photoRows[$photoIndex]
                ?? [];

            $photoType =
                $this->normalizePhotoType(
                    $metadata['photo_type']
                    ?? 'Completed Work'
                );

            $caption =
                $this->nullableTrim(
                    $metadata['caption']
                    ?? null
                );

            $extension = strtolower(
                $file->getClientOriginalExtension()
                ?: $file->extension()
                ?: 'jpg'
            );

            $filename = implode('-', [
                $this->filenamePart(
                    $projectName,
                    50
                ),

                $this->filenamePart(
                    $activityName,
                    60
                ),

                $this->filenamePart(
                    $photoType,
                    35
                ),

                $this->filenamePart(
                    $engineerName,
                    40
                ),

                $datePart,

                $timePart,

                str_pad(
                    (string) $sequence,
                    3,
                    '0',
                    STR_PAD_LEFT
                ),
            ]) . '.' . $extension;

            $directory = implode('/', [
                'work-done-v2',
                'project-'
                    . $workItem->header->project_id,
                'header-'
                    . $workItem->work_done_header_id,
                'work-item-'
                    . $workItem->id,
            ]);

            $path = $file->storeAs(
                $directory,
                $filename,
                'public'
            );

            $storedPaths[] = $path;

            DprWorkPhoto::create([
                'dpr_work_item_id' => null,
                'work_done_item_id' =>
                    $workItem->id,

                'uploaded_by' =>
                    auth()->id(),

                'photo_type' =>
                    $photoType,

                'file_path' =>
                    $path,

                'original_name' =>
                    $file->getClientOriginalName(),

                'mime_type' =>
                    $file->getMimeType(),

                'file_size' =>
                    $file->getSize(),

                'caption' =>
                    $caption,

                'sort_order' =>
                    $sequence,
            ]);

            $sequence++;
        }
    }

    private function formData(
        ?int $projectId = null,
        ?string $workDate = null,
        ?int $engineerId = null,
        ?int $headerId = null
    ): array {
        $materialQuery = MaterialConsumed::query()
            ->with([
                'items.materialType',
                'items.brand',
                'items.specification',
                'items.grade',
                'items.unit',
            ])
            ->whereNull('work_done_item_id');

        if ($projectId) {
            $materialQuery->where(
                'project_id',
                $projectId
            );
        }

        if ($workDate) {
            $materialQuery->whereDate(
                'consumed_date',
                $workDate
            );
        }

        if ($engineerId) {
            $materialQuery->where(
                'user_id',
                $engineerId
            );
        }

        if ($headerId) {
            $itemIds =
                WorkDoneItem::query()
                    ->where(
                        'work_done_header_id',
                        $headerId
                    )
                    ->pluck('id');

            $materialQuery->orWhereIn(
                'work_done_item_id',
                $itemIds
            );
        }

        return [
            'projects' =>
                $this->availableProjects(),

            'workStages' =>
                WorkStage::query()
                    ->where('is_active', true)
                    ->orderBy('sequence')
                    ->orderBy('name')
                    ->get(),

            'activityDivisions' =>
                ActivityDivision::query()
                    ->where('is_active', true)
                    ->orderBy('sequence')
                    ->get(),

            'activities' =>
                Activity::query()
                    ->where('is_active', true)
                    ->orderBy('activity_name')
                    ->get(),

            'activityMappings' =>
                ActivityMapping::query()
                    ->with('division')
                    ->where('is_active', true)
                    ->orderBy('activity_name')
                    ->get(),

            'contractors' =>
                Contractor::query()
                    ->where('status', 1)
                    ->orderBy('contractor_name')
                    ->get(),

            'projectBlocks' =>
                ProjectBlock::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'projectFloors' =>
                ProjectFloor::query()
                    ->where('is_active', true)
                    ->orderBy('sequence')
                    ->orderBy('name')
                    ->get(),

            'projectUnits' =>
                ProjectUnit::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'projectRooms' =>
                ProjectRoom::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'projectSubspaces' =>
                ProjectSubspace::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),

            'materialConsumptions' =>
                $materialQuery
                    ->orderByDesc('consumed_date')
                    ->orderByDesc('id')
                    ->get(),

            'photoTypes' =>
                self::PHOTO_TYPES,

            'executionStatuses' =>
                self::EXECUTION_STATUSES,
        ];
    }

    private function availableProjects()
    {
        if ($this->isEngineer()) {
            return auth()->user()
                ->projects()
                ->whereNotIn(
                    'projects.status',
                    [
                        'Completed',
                        'Handed Over',
                        'Closed',
                    ]
                )
                ->orderBy('project_name')
                ->get();
        }

        return Project::query()
            ->whereNotIn(
                'status',
                [
                    'Completed',
                    'Handed Over',
                    'Closed',
                ]
            )
            ->orderBy('project_name')
            ->get();
    }

    private function headerRelationships(): array
    {
        return [
            'project',
            'engineer',

            'items' => fn ($itemQuery) =>
                $itemQuery
                    ->orderBy('sort_order')
                    ->orderBy('id'),

            'items.dpr',
            'items.workStage',
            'items.activityDivision',
            'items.activity',
            'items.workActivity',
            'items.activityMapping.division',
            'items.contractor',

            'items.block',
            'items.floor',
            'items.unitLocation',
            'items.room',
            'items.subspace',


            'items.materialConsumptions.items.materialType',
            'items.materialConsumptions.items.brand',
            'items.materialConsumptions.items.specification',
            'items.materialConsumptions.items.grade',
            'items.materialConsumptions.items.unit',

            'items.photos.uploader',
        ];
    }

    private function ensureHeaderAccess(
        WorkDoneHeader $header
    ): void {
        if (! $this->isEngineer()) {
            return;
        }

        abort_unless(
            (int) $header->user_id
                === (int) auth()->id(),
            403,
            'Unauthorized Work Done access.'
        );
    }

    private function ensureProjectAccess(
        int $projectId
    ): void {
        if (! $this->isEngineer()) {
            return;
        }

        $hasAccess = auth()->user()
            ->projects()
            ->where(
                'projects.id',
                $projectId
            )
            ->exists();

        abort_unless(
            $hasAccess,
            403,
            'You are not assigned to this project.'
        );
    }

    private function auditHeaderValues(
        WorkDoneHeader $header
    ): array {
        $header->loadMissing(
            $this->headerRelationships()
        );

        return [
            'id' => $header->id,

            'project_id' =>
                $header->project_id,

            'project_name' =>
                $header->project?->project_name,

            'user_id' =>
                $header->user_id,

            'engineer' =>
                $header->engineer?->name,

            'work_date' =>
                $header->work_date?->format(
                    'Y-m-d'
                ),

            'status' =>
                $header->status,

            'remarks' =>
                $header->remarks,

            'works' =>
                $header->items
                    ->map(
                        fn (WorkDoneItem $item) => [
                            'id' =>
                                $item->id,

                            'dpr_id' =>
                                $item->dpr_id,

                            'work_stage_id' =>
                                $item->work_stage_id,

                            'activity_division_id' =>
                                $item->activity_division_id,

                            'activity_id' =>
                                $item->activity_id,

                            'work_activity_id' =>
                                $item->work_activity_id,

                            'activity_name' =>
                                $item->activity_name,

                            'activity_mapping_id' =>
                                $item->activity_mapping_id,

                            'location' =>
                                $item->location_path,

                            'contractor_id' =>
                                $item->contractor_id,

                            'quantity_completed' =>
                                $item->quantity_completed,

                            'unit' =>
                                $item->unit,

                            'progress_percentage' =>
                                $item->progress_percentage,

                            'execution_status' =>
                                $item->execution_status,

                            'remarks' =>
                                $item->remarks,

                            'material_consumed_ids' =>
                                $item
                                    ->materialConsumptions
                                    ->pluck('id')
                                    ->values()
                                    ->all(),

                            'photo_ids' =>
                                $item
                                    ->photos
                                    ->pluck('id')
                                    ->values()
                                    ->all(),
                        ]
                    )
                    ->values()
                    ->all(),
        ];
    }

    private function normalizePhotoType(
        mixed $photoType
    ): string {
        $photoType = trim(
            (string) $photoType
        );

        return in_array(
            $photoType,
            self::PHOTO_TYPES,
            true
        )
            ? $photoType
            : 'Other';
    }

    private function filenamePart(
        string $value,
        int $maxLength
    ): string {
        $slug = Str::slug(
            Str::limit(
                trim($value),
                $maxLength,
                ''
            ),
            '-'
        );

        return $slug !== ''
            ? $slug
            : 'NA';
    }

    private function deleteStoredPaths(
        array $paths
    ): void {
        $paths = collect($paths)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')
                ->delete($paths);
        }
    }

    private function nullableTrim(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $trimmed = trim(
            (string) $value
        );

        return $trimmed === ''
            ? null
            : $trimmed;
    }

    private function formatQuantity(
        mixed $quantity
    ): string {
        $formatted = number_format(
            (float) $quantity,
            3,
            '.',
            ''
        );

        return rtrim(
            rtrim(
                $formatted,
                '0'
            ),
            '.'
        );
    }

    private function isEngineer(): bool
    {
        return auth()->user()?->role?->name
            === 'Engineer';
    }
}
