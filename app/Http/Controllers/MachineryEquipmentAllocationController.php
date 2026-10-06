<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\MachineryEquipment;
use App\Models\MachineryEquipmentAllocation;
use App\Models\Project;
use App\Services\MachineryEquipmentAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MachineryEquipmentAllocationController extends Controller
{
    public function __construct(
        private readonly MachineryEquipmentAllocationService $allocationService
    ) {
    }

    /**
     * Allocation / movement register.
     */
    public function index(Request $request): View
    {
        $query = MachineryEquipmentAllocation::query()
            ->with([
                'equipment:id,machinery_tool_id,equipment_code,equipment_name,tracking_mode,quantity,unit,ownership_type',
                'equipment.machineryTool:id,machine_name,category',
                'fromProject:id,project_code,project_name',
                'toProject:id,project_code,project_name',
                'transferredBy:id,name',
                'receivedBy:id,name',
            ]);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('allocation_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('challan_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhereHas('equipment', function ($equipmentQuery) use ($search) {
                        $equipmentQuery
                            ->where('equipment_code', 'like', "%{$search}%")
                            ->orWhere('equipment_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment.machineryTool', function ($toolQuery) use ($search) {
                        $toolQuery->where('machine_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('equipment_id')) {
            $query->where(
                'machinery_equipment_id',
                $request->integer('equipment_id')
            );
        }

        if ($request->filled('project_id')) {
            $projectId = $request->integer('project_id');

            $query->where(function ($q) use ($projectId) {
                $q->where('from_project_id', $projectId)
                    ->orWhere('to_project_id', $projectId);
            });
        }

        if ($request->filled('movement_type')) {
            $query->where(
                'movement_type',
                $request->string('movement_type')->toString()
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'movement_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'movement_date',
                '<=',
                $request->input('date_to')
            );
        }

        $allocations = $query
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('machinery-equipment-allocations.index', [
            'allocations' => $allocations,

            'equipment' => MachineryEquipment::query()
                ->active()
                ->with('machineryTool:id,machine_name')
                ->orderBy('equipment_code')
                ->get(),

            'projects' => Project::query()
                ->orderBy('project_name')
                ->get([
                    'id',
                    'project_code',
                    'project_name',
                ]),

            'movementTypes' =>
                MachineryEquipmentAllocation::movementTypes(),

            'statuses' =>
                MachineryEquipmentAllocation::statuses(),
        ]);
    }

    /**
     * New allocation / transfer form.
     */
    public function create(Request $request): View
    {
        $selectedEquipment = null;
        $locationBalances = [];

        if ($request->filled('equipment_id')) {
            $selectedEquipment = MachineryEquipment::query()
                ->active()
                ->with([
                    'machineryTool:id,machine_name,category',
                    'currentProject:id,project_code,project_name',
                ])
                ->findOrFail($request->integer('equipment_id'));

            $locationBalances = $this->allocationService
                ->locationBalances($selectedEquipment);
        }

        return view(
            'machinery-equipment-allocations.create',
            array_merge(
                $this->formData(),
                [
                    'selectedEquipment' => $selectedEquipment,
                    'locationBalances' => $locationBalances,
                ]
            )
        );
    }

    /**
     * Store a movement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedMovementData($request);

        $equipment = MachineryEquipment::query()
            ->active()
            ->findOrFail($validated['machinery_equipment_id']);

        /*
         * Individual equipment always moves as one physical machine.
         */
        if ($equipment->tracking_mode === 'individual') {
            $validated['quantity'] = 1;
        }

        $allocation = $this->allocationService->createMovement(
            $equipment,
            $validated
        );

        AuditHelper::log(
            'Machinery Equipment Allocation',
            'Created',
            MachineryEquipmentAllocation::class,
            $allocation->id,
            sprintf(
                'Created machinery movement %s for equipment %s',
                $allocation->allocation_number,
                $equipment->equipment_code
            )
        );

        return redirect()
            ->route(
                'machinery-equipment-allocations.show',
                $allocation
            )
            ->with(
                'success',
                'Equipment movement created successfully.'
            );
    }

    /**
     * Show movement details.
     */
    public function show(
        MachineryEquipmentAllocation $machineryEquipmentAllocation
    ): View {
        $machineryEquipmentAllocation->load([
            'equipment.machineryTool',
            'equipment.vendor',
            'equipment.contractor',
            'fromProject',
            'toProject',
            'transferredBy',
            'receivedBy',
            'cancelledBy',
            'createdBy',
            'updatedBy',
        ]);

        $balances = $this->allocationService->locationBalances(
            $machineryEquipmentAllocation->equipment
        );

        return view(
            'machinery-equipment-allocations.show',
            [
                'allocation' => $machineryEquipmentAllocation,
                'balances' => $balances,
            ]
        );
    }

    /**
     * Dispatch a pending movement.
     */
    public function dispatch(
        MachineryEquipmentAllocation $machineryEquipmentAllocation
    ): RedirectResponse {
        $allocation = $this->allocationService->dispatch(
            $machineryEquipmentAllocation
        );

        AuditHelper::log(
            'Machinery Equipment Allocation',
            'Dispatched',
            MachineryEquipmentAllocation::class,
            $allocation->id,
            'Dispatched machinery movement: '
                . $allocation->allocation_number
        );

        return redirect()
            ->route(
                'machinery-equipment-allocations.show',
                $allocation
            )
            ->with(
                'success',
                'Equipment movement marked as in transit.'
            );
    }

    /**
     * Receive a pending / in-transit movement.
     */
    public function receive(
        MachineryEquipmentAllocation $machineryEquipmentAllocation
    ): RedirectResponse {
        $allocation = $this->allocationService->receive(
            $machineryEquipmentAllocation
        );

        AuditHelper::log(
            'Machinery Equipment Allocation',
            'Received',
            MachineryEquipmentAllocation::class,
            $allocation->id,
            'Received machinery movement: '
                . $allocation->allocation_number
        );

        return redirect()
            ->route(
                'machinery-equipment-allocations.show',
                $allocation
            )
            ->with(
                'success',
                'Equipment movement received successfully.'
            );
    }

    /**
     * Cancel a pending / in-transit movement.
     */
    public function cancel(
        Request $request,
        MachineryEquipmentAllocation $machineryEquipmentAllocation
    ): RedirectResponse {
        $validated = $request->validate([
            'cancellation_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $allocation = $this->allocationService->cancel(
            $machineryEquipmentAllocation,
            $validated['cancellation_reason']
        );

        AuditHelper::log(
            'Machinery Equipment Allocation',
            'Cancelled',
            MachineryEquipmentAllocation::class,
            $allocation->id,
            'Cancelled machinery movement: '
                . $allocation->allocation_number
        );

        return redirect()
            ->route(
                'machinery-equipment-allocations.show',
                $allocation
            )
            ->with(
                'success',
                'Equipment movement cancelled successfully.'
            );
    }

    /**
     * JSON endpoint used by the create form.
     *
     * Returns the confirmed and available quantities at each location.
     */
    public function equipmentAvailability(
        MachineryEquipment $machineryEquipment
    ) {
        abort_unless(
            $machineryEquipment->is_active,
            404
        );

        $machineryEquipment->load([
            'machineryTool:id,machine_name,category',
            'currentProject:id,project_code,project_name',
        ]);

        $balances = $this->allocationService
            ->locationBalances($machineryEquipment);

        $locations = [];

        /*
         * Company Yard.
         */
        $yardConfirmed = (float) ($balances['yard'] ?? 0);

        $locations[] = [
            'project_id' => null,
            'project_code' => null,
            'project_name' => 'Company Yard / Unallocated',

            'confirmed_quantity' =>
                round($yardConfirmed, 3),

            'available_quantity' =>
                $this->allocationService->availableQuantityAt(
                    $machineryEquipment,
                    null
                ),
        ];

        $projectIds = collect(array_keys($balances))
            ->reject(fn ($key) => $key === 'yard')
            ->map(fn ($key) => (int) $key)
            ->filter()
            ->unique()
            ->values();

        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->get([
                'id',
                'project_code',
                'project_name',
            ])
            ->keyBy('id');

        foreach ($projectIds as $projectId) {
            $project = $projects->get($projectId);

            if (!$project) {
                continue;
            }

            $confirmed = (float) ($balances[(string) $projectId] ?? 0);

            /*
             * We only need locations that contain equipment or may have
             * reserved outgoing quantity.
             */
            $available = $this->allocationService
                ->availableQuantityAt(
                    $machineryEquipment,
                    $projectId
                );

            if ($confirmed <= 0 && $available <= 0) {
                continue;
            }

            $locations[] = [
                'project_id' => $project->id,
                'project_code' => $project->project_code,
                'project_name' => $project->project_name,

                'confirmed_quantity' =>
                    round($confirmed, 3),

                'available_quantity' =>
                    round($available, 3),
            ];
        }

        return response()->json([
            'equipment' => [
                'id' => $machineryEquipment->id,

                'equipment_code' =>
                    $machineryEquipment->equipment_code,

                'display_name' =>
                    $machineryEquipment->display_name,

                'type_name' =>
                    $machineryEquipment->machineryTool?->machine_name,

                'category' =>
                    $machineryEquipment->machineryTool?->category,

                'tracking_mode' =>
                    $machineryEquipment->tracking_mode,

                'registered_quantity' =>
                    (float) $machineryEquipment->quantity,

                'unit' =>
                    $machineryEquipment->unit,

                'ownership_type' =>
                    $machineryEquipment->ownership_type,
            ],

            'locations' => $locations,
        ]);
    }

    /**
     * Data shared by movement forms.
     */
    private function formData(): array
    {
        return [
            'equipment' => MachineryEquipment::query()
                ->active()
                ->with([
                    'machineryTool:id,machine_name,category',
                    'currentProject:id,project_code,project_name',
                ])
                ->orderBy('equipment_code')
                ->get(),

            'projects' => Project::query()
                ->orderBy('project_name')
                ->get([
                    'id',
                    'project_code',
                    'project_name',
                ]),

            /*
             * quantity_adjustment is intentionally excluded from the
             * operational create screen. It should later have a controlled
             * correction workflow rather than acting as an ordinary transfer.
             */
            'movementTypes' => [
                MachineryEquipmentAllocation::TYPE_INITIAL =>
                    'Initial Allocation',

                MachineryEquipmentAllocation::TYPE_TRANSFER =>
                    'Project Transfer',

                MachineryEquipmentAllocation::TYPE_RETURN =>
                    'Return to Company Yard',

                MachineryEquipmentAllocation::TYPE_TEMPORARY =>
                    'Temporary Transfer',
            ],

            /*
             * Normal operational creation allows:
             *
             * received = immediate/direct movement
             * pending  = approval/dispatch workflow
             *
             * in_transit must be produced by Dispatch.
             * cancelled must be produced by Cancel.
             */
            'creationStatuses' => [
                MachineryEquipmentAllocation::STATUS_RECEIVED =>
                    'Received / Complete',

                MachineryEquipmentAllocation::STATUS_PENDING =>
                    'Pending Transfer',
            ],
        ];
    }

    /**
     * Validate movement form input.
     */
    private function validatedMovementData(
        Request $request
    ): array {
        $validated = $request->validate([
            'machinery_equipment_id' => [
                'required',
                'integer',
                'exists:machinery_equipment,id',
            ],

            'movement_type' => [
                'required',
                Rule::in([
                    MachineryEquipmentAllocation::TYPE_INITIAL,
                    MachineryEquipmentAllocation::TYPE_TRANSFER,
                    MachineryEquipmentAllocation::TYPE_RETURN,
                    MachineryEquipmentAllocation::TYPE_TEMPORARY,
                ]),
            ],

            'from_project_id' => [
                'nullable',
                'integer',
                'exists:projects,id',
            ],

            'to_project_id' => [
                'nullable',
                'integer',
                'exists:projects,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.001',
            ],

            'movement_date' => [
                'required',
                'date',
            ],

            'movement_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'expected_return_date' => [
                'nullable',
                'date',
                'after_or_equal:movement_date',
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'challan_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'vehicle_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'driver_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'driver_mobile' => [
                'nullable',
                'string',
                'max:30',
            ],

            'status' => [
                'required',
                Rule::in([
                    MachineryEquipmentAllocation::STATUS_PENDING,
                    MachineryEquipmentAllocation::STATUS_RECEIVED,
                ]),
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        /*
         * Convert blank project selections into real nulls.
         */
        $validated['from_project_id'] =
            $request->filled('from_project_id')
                ? $request->integer('from_project_id')
                : null;

        $validated['to_project_id'] =
            $request->filled('to_project_id')
                ? $request->integer('to_project_id')
                : null;

        /*
         * Movement-specific validation.
         */
        if (
            $validated['from_project_id']
            === $validated['to_project_id']
        ) {
            throw ValidationException::withMessages([
                'to_project_id' =>
                    'Source and destination cannot be the same location.',
            ]);
        }

        if (
            $validated['movement_type']
            === MachineryEquipmentAllocation::TYPE_INITIAL
            && $validated['from_project_id'] !== null
        ) {
            throw ValidationException::withMessages([
                'from_project_id' =>
                    'Initial allocation must originate from Company Yard.',
            ]);
        }

        if (
            $validated['movement_type']
            === MachineryEquipmentAllocation::TYPE_RETURN
            && $validated['to_project_id'] !== null
        ) {
            throw ValidationException::withMessages([
                'to_project_id' =>
                    'Return movements must return equipment to Company Yard.',
            ]);
        }

        if (
            in_array(
                $validated['movement_type'],
                [
                    MachineryEquipmentAllocation::TYPE_TRANSFER,
                    MachineryEquipmentAllocation::TYPE_TEMPORARY,
                ],
                true
            )
            && $validated['to_project_id'] === null
        ) {
            throw ValidationException::withMessages([
                'to_project_id' =>
                    'A destination project is required for this movement.',
            ]);
        }

        return $validated;
    }
}