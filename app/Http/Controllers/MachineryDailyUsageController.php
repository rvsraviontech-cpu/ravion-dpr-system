<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MachineryDailyUsage;
use App\Models\MachineryEquipment;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkActivity;
use App\Services\MachineryDailyUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MachineryDailyUsageController extends Controller
{
    public function __construct(
        protected MachineryDailyUsageService $usageService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
{
    $query = MachineryDailyUsage::query()
        ->with([
            'project:id,project_code,project_name',
            'equipment:id,machinery_tool_id,equipment_code,equipment_name,tracking_mode,quantity,unit,ownership_type,status',
            'equipment.machineryTool:id,machine_name,category',
            'workActivity:id,code,name',
            'projectBlock:id,name,code',
            'projectFloor:id,name',
            'projectUnit:id,name',
            'projectRoom:id,name,room_type',
            'projectSubspace:id,name',
            'createdBy:id,name',
        ])
        ->orderByDesc('usage_date')
        ->orderByDesc('id');

    if ($request->filled('project_id')) {
        $query->where(
            'project_id',
            (int) $request->project_id
        );
    }

    if ($request->filled('machinery_equipment_id')) {
        $query->where(
            'machinery_equipment_id',
            (int) $request->machinery_equipment_id
        );
    }

    if ($request->filled('usage_date')) {
        $query->whereDate(
            'usage_date',
            $request->usage_date
        );
    }

    if ($request->filled('from_date')) {
        $query->whereDate(
            'usage_date',
            '>=',
            $request->from_date
        );
    }

    if ($request->filled('to_date')) {
        $query->whereDate(
            'usage_date',
            '<=',
            $request->to_date
        );
    }

    if ($request->filled('working_condition')) {
        $query->where(
            'working_condition',
            $request->working_condition
        );
    }

    if ($request->filled('status')) {
        $query->where(
            'status',
            $request->status
        );
    }

    $usages = $query
        ->paginate(25)
        ->withQueryString();

    $projects = Project::query()
        ->select([
            'id',
            'project_code',
            'project_name',
            'status',
        ])
        ->orderBy('project_name')
        ->get();

    $equipment = MachineryEquipment::query()
        ->with([
            'machineryTool:id,machine_name,category',
        ])
        ->select([
            'id',
            'machinery_tool_id',
            'equipment_code',
            'equipment_name',
            'is_active',
        ])
        ->where('is_active', true)
        ->orderBy('equipment_code')
        ->get();

    $workingConditions =
        MachineryDailyUsage::workingConditions();

    $statuses =
        MachineryDailyUsage::statuses();

    return view(
        'machinery-daily-usages.index',
        compact(
            'usages',
            'projects',
            'equipment',
            'workingConditions',
            'statuses'
        )
    );
}
    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): View
    {
        $projects = Project::query()
            ->select([
                'id',
                'project_code',
                'project_name',
                'status',
            ])
            ->orderBy('project_name')
            ->get();

        $operators = User::query()
            ->select([
                'id',
                'employee_code',
                'name',
                'account_status',
            ])
            ->where('account_status', 'active')
            ->orderBy('name')
            ->get();

        $workActivities = WorkActivity::query()
            ->select([
                'id',
                'code',
                'name',
                'default_unit',
            ])
            ->where('is_active', true)
            ->where('allow_equipment', true)
            ->orderBy('name')
            ->get();

        $selectedProjectId = $request->filled('project_id')
            ? (int) $request->project_id
            : null;

        $availableEquipment = collect();

        if ($selectedProjectId) {
            $availableEquipment =
                $this->usageService
                    ->equipmentAvailableForProject(
                        $selectedProjectId
                    );
        }

        return view(
            'machinery-daily-usages.create',
            compact(
                'projects',
                'operators',
                'workActivities',
                'selectedProjectId',
                'availableEquipment'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUsageRequest($request);

        $usage = $this->usageService->create($validated);

        AuditHelper::log(
            'Machinery Daily Usage',
            'Created',
            MachineryDailyUsage::class,
            $usage->id,
            'Machinery daily usage record created.',
            null,
            $usage->toArray()
        );

        return redirect()
            ->route(
                'machinery-daily-usages.show',
                $usage
            )
            ->with(
                'success',
                'Machinery daily usage recorded successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

   public function show(MachineryDailyUsage $machineryDailyUsage)
{
    $machineryDailyUsage->load([
        'project',
        'equipment.machineryTool',
        'operatorUser',
        'workDoneItem',
        'workActivity',
        'projectBlock',
        'projectFloor',
        'projectUnit',
        'projectRoom',
        'projectSubspace',
        'createdBy',
        'updatedBy',
        'submittedBy',
'verifiedBy',
'cancelledBy',
    ]);

    return view('machinery-daily-usages.show', [
        'usage' => $machineryDailyUsage,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        MachineryDailyUsage $machineryDailyUsage
    ): View {
        if (
            $machineryDailyUsage->isVerified()
            || $machineryDailyUsage->isCancelled()
        ) {
            abort(
                403,
                'Verified or cancelled machinery usage records cannot be edited.'
            );
        }

        $projects = Project::query()
            ->select([
                'id',
                'project_code',
                'project_name',
                'status',
            ])
            ->orderBy('project_name')
            ->get();

        $operators = User::query()
            ->select([
                'id',
                'employee_code',
                'name',
                'account_status',
            ])
            ->where('account_status', 'active')
            ->orderBy('name')
            ->get();

        $workActivities = WorkActivity::query()
            ->select([
                'id',
                'code',
                'name',
                'default_unit',
            ])
            ->where('is_active', true)
            ->where('allow_equipment', true)
            ->orderBy('name')
            ->get();

        $availableEquipment =
            $this->usageService
                ->equipmentAvailableForProject(
                    $machineryDailyUsage->project_id
                );

        /*
         * During an edit, retain the currently selected equipment in the
         * dropdown even if its allocation changed after this record was made.
         */
        if (
            !$availableEquipment->contains(
                'id',
                $machineryDailyUsage->machinery_equipment_id
            )
        ) {
            $currentEquipment =
                MachineryEquipment::with('machineryTool')
                    ->find(
                        $machineryDailyUsage
                            ->machinery_equipment_id
                    );

            if ($currentEquipment) {
                $availableEquipment
                    ->prepend($currentEquipment);
            }
        }

        return view(
            'machinery-daily-usages.edit',
            compact(
                'machineryDailyUsage',
                'projects',
                'operators',
                'workActivities',
                'availableEquipment'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        MachineryDailyUsage $machineryDailyUsage
    ): RedirectResponse {
        $oldValues = $machineryDailyUsage->toArray();

        $validated = $this->validateUsageRequest(
            $request
        );

        $usage = $this->usageService->update(
            $machineryDailyUsage,
            $validated
        );

        AuditHelper::log(
            'Machinery Daily Usage',
            'Updated',
            MachineryDailyUsage::class,
            $usage->id,
            'Machinery daily usage record updated.',
            $oldValues,
            $usage->toArray()
        );

        return redirect()
            ->route(
                'machinery-daily-usages.show',
                $usage
            )
            ->with(
                'success',
                'Machinery daily usage updated successfully.'
            );
    }

/*
|--------------------------------------------------------------------------
| PMO Verify
|--------------------------------------------------------------------------
*/

public function verify(
    Request $request,
    MachineryDailyUsage $machineryDailyUsage
): RedirectResponse {

if (!auth()->user()?->hasPermission('machinery_daily_usages.verify')) {
    abort(403, 'You do not have permission to verify machinery daily usage records.');
}
    if (!$machineryDailyUsage->isSubmitted()) {
        
        return back()->withErrors([
            'status' =>
                'Only submitted machinery usage records can be verified.',
        ]);
    }

    $validated = $request->validate([
        'verification_remarks' => [
            'nullable',
            'string',
            'max:2000',
        ],
    ]);

    $oldValues = $machineryDailyUsage->toArray();

    DB::transaction(function () use (
        $machineryDailyUsage,
        $validated
    ) {
        $machineryDailyUsage->update([
            'status' =>
                MachineryDailyUsage::STATUS_VERIFIED,

            'verified_by' => auth()->id(),
            'verified_at' => now(),

            'verification_remarks' =>
                $validated['verification_remarks'] ?? null,

            'updated_by' => auth()->id(),
        ]);
    });

    AuditHelper::log(
        'Machinery Daily Usage',
        'Verified',
        MachineryDailyUsage::class,
        $machineryDailyUsage->id,
        'Machinery daily usage verified by PMO.',
        $oldValues,
        $machineryDailyUsage->fresh()->toArray()
    );

    return redirect()
        ->route(
            'machinery-daily-usages.show',
            $machineryDailyUsage
        )
        ->with(
            'success',
            'Machinery daily usage verified successfully.'
        );
}

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        MachineryDailyUsage $machineryDailyUsage
    ): RedirectResponse {

    if (!auth()->user()?->hasPermission('machinery_daily_usages.cancel')) {
    abort(403, 'You do not have permission to cancel machinery daily usage records.');
}
        if ($machineryDailyUsage->isVerified()) {
            return back()->withErrors([
                'status' =>
                    'A verified machinery usage record cannot be cancelled.',
            ]);
        }

        if ($machineryDailyUsage->isCancelled()) {
            return back()->with(
                'success',
                'This machinery usage record is already cancelled.'
            );
        }

        $request->validate([
            'cancellation_reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $oldValues = $machineryDailyUsage->toArray();

        $reason = trim(
            (string) $request->cancellation_reason
        );

        $existingRemarks = trim(
            (string) ($machineryDailyUsage->remarks ?? '')
        );

        $cancellationNote =
            'Cancelled: ' . $reason;

        $machineryDailyUsage->update([
            'status' =>
                MachineryDailyUsage::STATUS_CANCELLED,

            'remarks' =>
                $existingRemarks !== ''
                    ? $existingRemarks
                        . PHP_EOL
                        . $cancellationNote
                    : $cancellationNote,

            'updated_by' => auth()->id(),
        ]);

        AuditHelper::log(
            'Machinery Daily Usage',
            'Cancelled',
            MachineryDailyUsage::class,
            $machineryDailyUsage->id,
            'Machinery daily usage record cancelled.',
            $oldValues,
            $machineryDailyUsage->fresh()->toArray()
        );

        return redirect()
            ->route(
                'machinery-daily-usages.show',
                $machineryDailyUsage
            )
            ->with(
                'success',
                'Machinery daily usage cancelled successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX — Equipment Available At Project
    |--------------------------------------------------------------------------
    */

    public function projectEquipment(
        Project $project
    ): JsonResponse {
        $equipment =
            $this->usageService
                ->equipmentAvailableForProject(
                    $project->id
                )
                ->map(function (
                    MachineryEquipment $equipment
                ) use ($project) {
                    return [
                        'id' => $equipment->id,

                        'equipment_code' =>
                            $equipment->equipment_code,

                        'equipment_name' =>
    $equipment->equipment_name
    ?: $equipment->machineryTool?->machine_name
    ?: 'Equipment',

                        'machine_name' =>
                            $equipment->machineryTool
                                ?->machine_name,

                        'category' =>
                            $equipment->machineryTool
                                ?->category,

                        'tracking_mode' =>
                            $equipment->tracking_mode,

                        'ownership_type' =>
                            $equipment->ownership_type,

                        'unit' =>
                            $equipment->unit,

                        'meter_type' =>
                            $equipment->meter_type,

                        'fuel_type' =>
                            $equipment->fuel_type,

                        'current_meter_reading' =>
                            $equipment
                                ->current_meter_reading,

                        'confirmed_quantity' =>
                            $this->usageService
                                ->confirmedQuantityAtProject(
                                    $equipment,
                                    $project->id
                                ),
                    ];
                })
                ->values();

        return response()->json([
            'project' => [
                'id' => $project->id,
                'project_code' =>
                    $project->project_code,
                'project_name' =>
                    $project->project_name,
            ],
            'equipment' => $equipment,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX — Single Equipment Details
    |--------------------------------------------------------------------------
    */

    public function equipmentDetails(
        Project $project,
        MachineryEquipment $machineryEquipment
    ): JsonResponse {
        $confirmed =
            $this->usageService
                ->confirmedQuantityAtProject(
                    $machineryEquipment,
                    $project->id
                );

        if ($confirmed <= 0) {
            return response()->json([
                'message' =>
                    'This equipment is not currently allocated to the selected project.',
            ], 422);
        }

        $machineryEquipment->load(
            'machineryTool'
        );

        return response()->json([
            'id' =>
                $machineryEquipment->id,

            'equipment_code' =>
                $machineryEquipment->equipment_code,

            'equipment_name' =>
                $machineryEquipment->equipment_name,

            'machine_name' =>
                $machineryEquipment->machineryTool
                    ?->machine_name,

            'category' =>
                $machineryEquipment->machineryTool
                    ?->category,

            'tracking_mode' =>
                $machineryEquipment->tracking_mode,

            'ownership_type' =>
                $machineryEquipment->ownership_type,

            'unit' =>
                $machineryEquipment->unit,

            'meter_type' =>
                $machineryEquipment->meter_type,

            'fuel_type' =>
                $machineryEquipment->fuel_type,

            'current_meter_reading' =>
                $machineryEquipment
                    ->current_meter_reading,

            'confirmed_quantity' =>
                $confirmed,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX — Project Structure
    |--------------------------------------------------------------------------
    */

    public function projectStructure(
        Project $project
    ): JsonResponse {
        $blocks = DB::table('project_blocks')
            ->where('project_id', $project->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
            ]);

        return response()->json([
            'blocks' => $blocks,
        ]);
    }

    public function floors(
        Project $project,
        int $block
    ): JsonResponse {
        $floors = DB::table('project_floors')
            ->where('project_id', $project->id)
            ->where('project_block_id', $block)
            ->where('is_active', true)
            ->orderBy('sequence')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sequence',
                'usage_type',
            ]);

        return response()->json([
            'floors' => $floors,
        ]);
    }

    public function units(
        Project $project,
        int $floor
    ): JsonResponse {
        $units = DB::table('project_units')
            ->where('project_id', $project->id)
            ->where('project_floor_id', $floor)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'project_block_id',
                'project_floor_id',
                'name',
                'type',
            ]);

        return response()->json([
            'units' => $units,
        ]);
    }

    public function rooms(
        Project $project,
        int $unit
    ): JsonResponse {
        $rooms = DB::table('project_rooms')
            ->where('project_id', $project->id)
            ->where('project_unit_id', $unit)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'project_block_id',
                'project_floor_id',
                'project_unit_id',
                'name',
                'room_type',
            ]);

        return response()->json([
            'rooms' => $rooms,
        ]);
    }

    public function subspaces(
        Project $project,
        int $room
    ): JsonResponse {
        $subspaces = DB::table(
            'project_subspaces'
        )
            ->where('project_id', $project->id)
            ->where('project_room_id', $room)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'project_block_id',
                'project_floor_id',
                'project_unit_id',
                'project_room_id',
                'name',
                'type',
            ]);

        return response()->json([
            'subspaces' => $subspaces,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX — Work Done Items
    |--------------------------------------------------------------------------
    */

    public function workDoneItems(
        Project $project,
        Request $request
    ): JsonResponse {
        $query = DB::table(
            'work_done_items as wdi'
        )
            ->join(
                'work_done_headers as wdh',
                'wdh.id',
                '=',
                'wdi.work_done_header_id'
            )
            ->leftJoin(
                'work_activities as wa',
                'wa.id',
                '=',
                'wdi.work_activity_id'
            )
            ->where(
                'wdh.project_id',
                $project->id
            )
            ->where(function ($q) {
                $q->whereNull(
                    'wdi.work_activity_id'
                )
                    ->orWhere(
                        'wa.allow_equipment',
                        true
                    );
            });

        if ($request->filled('usage_date')) {
            $query->whereDate(
                'wdh.work_date',
                $request->usage_date
            );
        }

        if ($request->filled('work_activity_id')) {
            $query->where(
                'wdi.work_activity_id',
                (int) $request->work_activity_id
            );
        }

        $items = $query
            ->orderByDesc('wdh.work_date')
            ->orderByDesc('wdi.id')
            ->limit(100)
            ->get([
                'wdi.id',
                'wdi.work_done_header_id',
                'wdh.work_date',
                'wdi.work_activity_id',
                'wa.code as activity_code',
                'wa.name as activity_name',
                'wdi.quantity_completed',
                'wdi.unit',
                'wdi.execution_status',
                'wdi.project_block_id',
                'wdi.project_floor_id',
                'wdi.project_unit_id',
                'wdi.project_room_id',
                'wdi.project_subspace_id',
            ]);

        return response()->json([
            'items' => $items,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function validateUsageRequest(
        Request $request
    ): array {
        return $request->validate([
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'machinery_equipment_id' => [
                'required',
                'integer',
                'exists:machinery_equipment,id',
            ],

            'usage_date' => [
                'required',
                'date',
            ],

            'quantity_used' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'shift' => [
                'required',
                Rule::in(
                    array_keys(
                        MachineryDailyUsage::shifts()
                    )
                ),
            ],

            'start_time' => [
                'nullable',
                'date_format:H:i',
                'required_with:end_time',
            ],

            'end_time' => [
                'nullable',
                'date_format:H:i',
                'required_with:start_time',
            ],

            'opening_meter_reading' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'closing_meter_reading' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'manual_operating_hours' => [
                'nullable',
                'numeric',
                'gte:0',
                'lte:24',
            ],

            'manual_hours_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'idle_hours' => [
                'nullable',
                'numeric',
                'gte:0',
                'lte:24',
            ],

            'breakdown_hours' => [
                'nullable',
                'numeric',
                'gte:0',
                'lte:24',
            ],

            'working_condition' => [
                'required',
                Rule::in(
                    array_keys(
                        MachineryDailyUsage
                            ::workingConditions()
                    )
                ),
            ],

            'operator_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'operator_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'operator_mobile' => [
                'nullable',
                'string',
                'max:30',
            ],

            'work_done_item_id' => [
                'nullable',
                'integer',
                'exists:work_done_items,id',
            ],

            'work_activity_id' => [
                'nullable',
                'integer',
                'exists:work_activities,id',
            ],

            'project_block_id' => [
                'nullable',
                'integer',
                'exists:project_blocks,id',
            ],

            'project_floor_id' => [
                'nullable',
                'integer',
                'exists:project_floors,id',
            ],

            'project_unit_id' => [
                'nullable',
                'integer',
                'exists:project_units,id',
            ],

            'project_room_id' => [
                'nullable',
                'integer',
                'exists:project_rooms,id',
            ],

            'project_subspace_id' => [
                'nullable',
                'integer',
                'exists:project_subspaces,id',
            ],

            'fuel_energy_quantity' => [
                'nullable',
                'numeric',
                'gte:0',
            ],

            'fuel_energy_unit' => [
                'nullable',
                'string',
                'max:30',
            ],

            'work_description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    MachineryDailyUsage::STATUS_DRAFT,
                    MachineryDailyUsage::STATUS_SUBMITTED,
                ]),
            ],
        ]);
    }
}