<?php

namespace App\Services;

use App\Models\MachineryEquipment;
use App\Models\MachineryEquipmentAllocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MachineryEquipmentAllocationService
{
    /**
     * Return confirmed quantity of an equipment record at a project.
     *
     * projectId = null represents Company Yard / Unallocated.
     */
    public function confirmedQuantityAt(
        MachineryEquipment $equipment,
        ?int $projectId
    ): float {
        $movements = MachineryEquipmentAllocation::query()
            ->where('machinery_equipment_id', $equipment->id)
            ->where('status', MachineryEquipmentAllocation::STATUS_RECEIVED)
            ->get([
                'from_project_id',
                'to_project_id',
                'quantity',
            ]);

        $balance = 0.0;

        foreach ($movements as $movement) {
            $quantity = (float) $movement->quantity;

            if ($this->sameLocation($movement->to_project_id, $projectId)) {
                $balance += $quantity;
            }

            if ($this->sameLocation($movement->from_project_id, $projectId)) {
                $balance -= $quantity;
            }
        }

        /*
         * Company Yard / Unallocated starts with the registered quantity.
         *
         * Projects start with zero and receive quantities through the ledger.
         */
        if ($projectId === null) {
            $balance += (float) $equipment->quantity;
        }

        return round($balance, 3);
    }

    /**
     * Quantity dispatched from a location but not yet finally received.
     *
     * This quantity must not remain available at the source.
     */
    public function quantityInTransitFrom(
        MachineryEquipment $equipment,
        ?int $projectId
    ): float {
        $quantity = MachineryEquipmentAllocation::query()
            ->where('machinery_equipment_id', $equipment->id)
            ->where('status', MachineryEquipmentAllocation::STATUS_IN_TRANSIT)
            ->where(function ($query) use ($projectId) {
                if ($projectId === null) {
                    $query->whereNull('from_project_id');
                } else {
                    $query->where('from_project_id', $projectId);
                }
            })
            ->sum('quantity');

        return round((float) $quantity, 3);
    }

    /**
     * Quantity reserved by pending movements.
     *
     * Pending transactions also cannot be double-allocated.
     */
    public function pendingQuantityFrom(
        MachineryEquipment $equipment,
        ?int $projectId
    ): float {
        $quantity = MachineryEquipmentAllocation::query()
            ->where('machinery_equipment_id', $equipment->id)
            ->where('status', MachineryEquipmentAllocation::STATUS_PENDING)
            ->where(function ($query) use ($projectId) {
                if ($projectId === null) {
                    $query->whereNull('from_project_id');
                } else {
                    $query->where('from_project_id', $projectId);
                }
            })
            ->sum('quantity');

        return round((float) $quantity, 3);
    }

    /**
     * Quantity currently available for another movement.
     */
    public function availableQuantityAt(
        MachineryEquipment $equipment,
        ?int $projectId
    ): float {
        $confirmed = $this->confirmedQuantityAt($equipment, $projectId);

        $reserved =
            $this->quantityInTransitFrom($equipment, $projectId)
            + $this->pendingQuantityFrom($equipment, $projectId);

        return round(max(0, $confirmed - $reserved), 3);
    }

    /**
     * Create a new allocation / transfer movement.
     *
     * $data expects:
     * movement_type
     * from_project_id
     * to_project_id
     * quantity
     * movement_date
     *
     * Optional:
     * movement_time
     * expected_return_date
     * reference_number
     * challan_number
     * vehicle_number
     * driver_name
     * driver_mobile
     * remarks
     * status
     */
    public function createMovement(
        MachineryEquipment $equipment,
        array $data
    ): MachineryEquipmentAllocation {
        return DB::transaction(function () use ($equipment, $data) {
            /*
             * Lock equipment so simultaneous requests cannot allocate
             * the same machine / quantity twice.
             */
            $equipment = MachineryEquipment::query()
                ->lockForUpdate()
                ->findOrFail($equipment->id);

            if (!$equipment->is_active) {
                throw ValidationException::withMessages([
                    'machinery_equipment_id' =>
                        'Inactive equipment cannot be allocated or transferred.',
                ]);
            }

            $fromProjectId = $this->nullableInt(
                $data['from_project_id'] ?? null
            );

            $toProjectId = $this->nullableInt(
                $data['to_project_id'] ?? null
            );

            $movementType = $data['movement_type']
                ?? MachineryEquipmentAllocation::TYPE_TRANSFER;

            $quantity = round(
                (float) ($data['quantity'] ?? 1),
                3
            );

            $status = $data['status']
                ?? MachineryEquipmentAllocation::STATUS_RECEIVED;

            $this->validateMovement(
                $equipment,
                $movementType,
                $fromProjectId,
                $toProjectId,
                $quantity,
                $status
            );

            /*
             * For received movements we check the confirmed source balance.
             *
             * For pending / in-transit movements we check available quantity,
             * which subtracts quantities already reserved by other open
             * movements.
             */
            $available = $status === MachineryEquipmentAllocation::STATUS_RECEIVED
                ? $this->confirmedQuantityAt($equipment, $fromProjectId)
                : $this->availableQuantityAt($equipment, $fromProjectId);

            if ($quantity > $available) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Only %s %s is available at the selected source location.',
                        $this->formatQuantity($available),
                        $equipment->unit
                    ),
                ]);
            }

            $userId = Auth::id();

            $allocation = MachineryEquipmentAllocation::create([
                'machinery_equipment_id' => $equipment->id,
                'allocation_number' => $this->nextAllocationNumber(),

                'movement_type' => $movementType,

                'from_project_id' => $fromProjectId,
                'to_project_id' => $toProjectId,

                'quantity' => $quantity,
                'unit' => $equipment->unit,

                'movement_date' => $data['movement_date'],
                'movement_time' => $data['movement_time'] ?? null,

                'expected_return_date' =>
                    $data['expected_return_date'] ?? null,

                'actual_return_date' =>
                    $data['actual_return_date'] ?? null,

                'reference_number' =>
                    $data['reference_number'] ?? null,

                'challan_number' =>
                    $data['challan_number'] ?? null,

                'vehicle_number' =>
                    $data['vehicle_number'] ?? null,

                'driver_name' =>
                    $data['driver_name'] ?? null,

                'driver_mobile' =>
                    $data['driver_mobile'] ?? null,

                'status' => $status,

'transferred_by' =>
    in_array(
        $status,
        [
            MachineryEquipmentAllocation::STATUS_IN_TRANSIT,
            MachineryEquipmentAllocation::STATUS_RECEIVED,
        ],
        true
    )
        ? $userId
        : null,

'transferred_at' =>
    in_array(
        $status,
        [
            MachineryEquipmentAllocation::STATUS_IN_TRANSIT,
            MachineryEquipmentAllocation::STATUS_RECEIVED,
        ],
        true
    )
        ? now()
        : null,

'received_by' =>
    $status === MachineryEquipmentAllocation::STATUS_RECEIVED
        ? $userId
        : null,

'received_at' =>
    $status === MachineryEquipmentAllocation::STATUS_RECEIVED
        ? now()
        : null,

                'remarks' => $data['remarks'] ?? null,

                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if ($allocation->status === MachineryEquipmentAllocation::STATUS_RECEIVED) {
                $this->refreshEquipmentSnapshot($equipment);
            }

            return $allocation->fresh([
                'equipment',
                'fromProject',
                'toProject',
            ]);
        });
    }

    /**
     * Dispatch a pending movement.
     */
    public function dispatch(
        MachineryEquipmentAllocation $allocation
    ): MachineryEquipmentAllocation {
        return DB::transaction(function () use ($allocation) {
            $allocation = MachineryEquipmentAllocation::query()
                ->lockForUpdate()
                ->findOrFail($allocation->id);

            if ($allocation->status !== MachineryEquipmentAllocation::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Only a pending movement can be dispatched.',
                ]);
            }

            $equipment = MachineryEquipment::query()
                ->lockForUpdate()
                ->findOrFail($allocation->machinery_equipment_id);

            /*
             * Exclude this pending transaction from reserved quantity before
             * checking whether the source can still support it.
             */
            $available = $this->availableQuantityAtExcluding(
                $equipment,
                $allocation->from_project_id,
                $allocation->id
            );

            if ((float) $allocation->quantity > $available) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'The source location no longer has sufficient available quantity.',
                ]);
            }

            $allocation->update([
                'status' => MachineryEquipmentAllocation::STATUS_IN_TRANSIT,
                'transferred_by' => Auth::id(),
                'transferred_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            return $allocation->fresh();
        });
    }

    /**
     * Receive a pending or in-transit movement.
     */
    public function receive(
        MachineryEquipmentAllocation $allocation
    ): MachineryEquipmentAllocation {
        return DB::transaction(function () use ($allocation) {
            $allocation = MachineryEquipmentAllocation::query()
                ->lockForUpdate()
                ->findOrFail($allocation->id);

            if (!in_array(
                $allocation->status,
                [
                    MachineryEquipmentAllocation::STATUS_PENDING,
                    MachineryEquipmentAllocation::STATUS_IN_TRANSIT,
                ],
                true
            )) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Only a pending or in-transit movement can be received.',
                ]);
            }

            $equipment = MachineryEquipment::query()
                ->lockForUpdate()
                ->findOrFail($allocation->machinery_equipment_id);

            /*
             * The current transaction is already reserving its own source
             * quantity, so exclude it from the availability calculation.
             */
            $available = $this->availableQuantityAtExcluding(
                $equipment,
                $allocation->from_project_id,
                $allocation->id
            );

            if ((float) $allocation->quantity > $available) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'The source location no longer has sufficient quantity to receive this movement.',
                ]);
            }

            $allocation->update([
                'status' => MachineryEquipmentAllocation::STATUS_RECEIVED,

                'received_by' => Auth::id(),
                'received_at' => now(),

                'actual_return_date' =>
                    $allocation->movement_type === MachineryEquipmentAllocation::TYPE_RETURN
                        ? now()->toDateString()
                        : $allocation->actual_return_date,

                'updated_by' => Auth::id(),
            ]);

            $this->refreshEquipmentSnapshot($equipment);

            return $allocation->fresh([
                'equipment',
                'fromProject',
                'toProject',
            ]);
        });
    }

    /**
     * Cancel a pending or in-transit movement.
     *
     * Received movements are permanent ledger transactions and should not
     * simply disappear. Corrections to received history should be made by
     * compensating movements later.
     */
    public function cancel(
        MachineryEquipmentAllocation $allocation,
        string $reason
    ): MachineryEquipmentAllocation {
        return DB::transaction(function () use ($allocation, $reason) {
            $allocation = MachineryEquipmentAllocation::query()
                ->lockForUpdate()
                ->findOrFail($allocation->id);

            if ($allocation->status === MachineryEquipmentAllocation::STATUS_RECEIVED) {
                throw ValidationException::withMessages([
                    'status' =>
                        'A received movement cannot be cancelled. Create a corrective movement instead.',
                ]);
            }

            if ($allocation->status === MachineryEquipmentAllocation::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'status' => 'This movement is already cancelled.',
                ]);
            }

            if (trim($reason) === '') {
                throw ValidationException::withMessages([
                    'cancellation_reason' =>
                        'Cancellation reason is required.',
                ]);
            }

            $allocation->update([
                'status' => MachineryEquipmentAllocation::STATUS_CANCELLED,

                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),

                'cancellation_reason' => $reason,
                'updated_by' => Auth::id(),
            ]);

            return $allocation->fresh();
        });
    }

    /**
     * Return balances for all projects plus Company Yard.
     */
    public function locationBalances(
        MachineryEquipment $equipment
    ): array {
        $movements = MachineryEquipmentAllocation::query()
            ->where('machinery_equipment_id', $equipment->id)
            ->where('status', MachineryEquipmentAllocation::STATUS_RECEIVED)
            ->get([
                'from_project_id',
                'to_project_id',
                'quantity',
            ]);

        /*
         * NULL key is awkward in PHP arrays because it becomes an empty
         * string, so use "yard" internally for Company Yard.
         */
        $balances = [
            'yard' => (float) $equipment->quantity,
        ];

        foreach ($movements as $movement) {
            $quantity = (float) $movement->quantity;

            $fromKey = $movement->from_project_id === null
                ? 'yard'
                : (string) $movement->from_project_id;

            $toKey = $movement->to_project_id === null
                ? 'yard'
                : (string) $movement->to_project_id;

            if (!array_key_exists($fromKey, $balances)) {
                $balances[$fromKey] = 0.0;
            }

            if (!array_key_exists($toKey, $balances)) {
                $balances[$toKey] = 0.0;
            }

            $balances[$fromKey] -= $quantity;
            $balances[$toKey] += $quantity;
        }

        foreach ($balances as $key => $quantity) {
            $balances[$key] = round($quantity, 3);

            if (abs($balances[$key]) < 0.0005) {
                $balances[$key] = 0.0;
            }
        }

        return $balances;
    }

    /**
     * Keep machinery_equipment.current_project_id as a convenient snapshot.
     *
     * Individual equipment:
     *     Project ID when located wholly at one project.
     *
     * Pooled equipment:
     *     Project ID only when the entire pool is at one project.
     *
     * Split pooled equipment:
     *     NULL because no single project represents its current location.
     */
    public function refreshEquipmentSnapshot(
        MachineryEquipment $equipment
    ): void {
        $balances = $this->locationBalances($equipment);

        $positiveProjectBalances = [];

        foreach ($balances as $location => $quantity) {
            if ($location === 'yard') {
                continue;
            }

            if ($quantity > 0) {
                $positiveProjectBalances[(int) $location] = $quantity;
            }
        }

        $totalQuantity = round((float) $equipment->quantity, 3);

        $newProjectId = null;

        if (count($positiveProjectBalances) === 1) {
            $projectId = array_key_first($positiveProjectBalances);
            $projectQuantity = round(
                (float) $positiveProjectBalances[$projectId],
                3
            );

            /*
             * Snapshot a project only when the complete equipment quantity
             * is located there.
             */
            if (abs($projectQuantity - $totalQuantity) < 0.0005) {
                $newProjectId = $projectId;
            }
        }

        $equipment->update([
            'current_project_id' => $newProjectId,
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Build a unique human-readable movement number.
     *
     * Example:
     * MEA-20261003-000001
     */
    public function nextAllocationNumber(): string
    {
        $date = now()->format('Ymd');

        $prefix = 'MEA-' . $date . '-';

        $last = MachineryEquipmentAllocation::query()
            ->where('allocation_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('allocation_number');

        $sequence = 1;

        if ($last) {
            $lastSequence = (int) substr(
                $last,
                strlen($prefix)
            );

            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad(
            (string) $sequence,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Internal validation
    |--------------------------------------------------------------------------
    */

    private function validateMovement(
        MachineryEquipment $equipment,
        string $movementType,
        ?int $fromProjectId,
        ?int $toProjectId,
        float $quantity,
        string $status
    ): void {
        if (!array_key_exists(
            $movementType,
            MachineryEquipmentAllocation::movementTypes()
        )) {
            throw ValidationException::withMessages([
                'movement_type' => 'Invalid equipment movement type.',
            ]);
        }

        if (!array_key_exists(
            $status,
            MachineryEquipmentAllocation::statuses()
        )) {
            throw ValidationException::withMessages([
                'status' => 'Invalid equipment movement status.',
            ]);
        }

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Movement quantity must be greater than zero.',
            ]);
        }

        if ($fromProjectId === $toProjectId) {
            throw ValidationException::withMessages([
                'to_project_id' =>
                    'Source and destination cannot be the same location.',
            ]);
        }

        if ($equipment->tracking_mode === 'individual') {
            if (abs($quantity - 1.0) > 0.0005) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Individual equipment must always move as quantity 1.',
                ]);
            }

            $openMovementExists = MachineryEquipmentAllocation::query()
                ->where('machinery_equipment_id', $equipment->id)
                ->whereIn('status', [
                    MachineryEquipmentAllocation::STATUS_PENDING,
                    MachineryEquipmentAllocation::STATUS_IN_TRANSIT,
                ])
                ->exists();

            if ($openMovementExists) {
                throw ValidationException::withMessages([
                    'machinery_equipment_id' =>
                        'This equipment already has a pending or in-transit movement.',
                ]);
            }
        }

        if (
            $movementType === MachineryEquipmentAllocation::TYPE_INITIAL
            && $fromProjectId !== null
        ) {
            throw ValidationException::withMessages([
                'from_project_id' =>
                    'Initial allocation must originate from Company Yard / Unallocated.',
            ]);
        }

        if (
            $movementType === MachineryEquipmentAllocation::TYPE_RETURN
            && $toProjectId !== null
        ) {
            throw ValidationException::withMessages([
                'to_project_id' =>
                    'A return movement must return equipment to Company Yard / Unallocated.',
            ]);
        }

        if (
            $movementType === MachineryEquipmentAllocation::TYPE_TRANSFER
            && $toProjectId === null
        ) {
            throw ValidationException::withMessages([
                'to_project_id' =>
                    'A project transfer requires a destination project.',
            ]);
        }

        if (
            $movementType === MachineryEquipmentAllocation::TYPE_TEMPORARY
            && $toProjectId === null
        ) {
            throw ValidationException::withMessages([
                'to_project_id' =>
                    'A temporary transfer requires a destination project.',
            ]);
        }
    }

    private function availableQuantityAtExcluding(
        MachineryEquipment $equipment,
        ?int $projectId,
        int $excludeAllocationId
    ): float {
        $confirmed = $this->confirmedQuantityAt(
            $equipment,
            $projectId
        );

        $reserved = MachineryEquipmentAllocation::query()
            ->where('machinery_equipment_id', $equipment->id)
            ->where('id', '!=', $excludeAllocationId)
            ->whereIn('status', [
                MachineryEquipmentAllocation::STATUS_PENDING,
                MachineryEquipmentAllocation::STATUS_IN_TRANSIT,
            ])
            ->where(function ($query) use ($projectId) {
                if ($projectId === null) {
                    $query->whereNull('from_project_id');
                } else {
                    $query->where('from_project_id', $projectId);
                }
            })
            ->sum('quantity');

        return round(
            max(0, $confirmed - (float) $reserved),
            3
        );
    }

    private function sameLocation(
        ?int $first,
        ?int $second
    ): bool {
        return $first === $second;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(
            rtrim(
                number_format($quantity, 3, '.', ''),
                '0'
            ),
            '.'
        );
    }
}