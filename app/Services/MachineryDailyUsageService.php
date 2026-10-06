<?php

namespace App\Services;

use App\Models\MachineryDailyUsage;
use App\Models\MachineryEquipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MachineryDailyUsageService
{
    public function __construct(
        protected MachineryEquipmentAllocationService $allocationService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Equipment Availability
    |--------------------------------------------------------------------------
    */

    /**
     * Confirmed physical quantity of equipment at a project.
     *
     * Daily Usage uses CONFIRMED physical location, not merely "available for
     * another transfer". Pending/in-transit movements do not relocate equipment
     * until they are received.
     */
    public function confirmedQuantityAtProject(
        MachineryEquipment $equipment,
        int $projectId
    ): float {
        return round(
            $this->allocationService->confirmedQuantityAt(
                $equipment,
                $projectId
            ),
            3
        );
    }

    /**
     * Equipment that physically exists at the selected project.
     */
    public function equipmentAvailableForProject(int $projectId)
    {
        return MachineryEquipment::query()
            ->with('machineryTool')
            ->where('is_active', true)
            ->orderBy('equipment_name')
            ->get()
            ->filter(function (MachineryEquipment $equipment) use ($projectId) {
                return $this->confirmedQuantityAtProject(
                    $equipment,
                    $projectId
                ) > 0;
            })
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Update
    |--------------------------------------------------------------------------
    */

    public function create(array $data): MachineryDailyUsage
    {
        return DB::transaction(function () use ($data) {
            $equipment = MachineryEquipment::query()
                ->lockForUpdate()
                ->findOrFail($data['machinery_equipment_id']);

            $prepared = $this->prepareData(
                $equipment,
                $data
            );

            $prepared['created_by'] = Auth::id();
            $prepared['updated_by'] = Auth::id();

            $usage = MachineryDailyUsage::create($prepared);

            /*
             * Keep the equipment's latest hour-meter snapshot synchronized.
             * Only hour-meter equipment updates this field from Daily Usage.
             */
            $this->syncEquipmentMeterSnapshot(
                $equipment,
                $usage
            );

            return $usage->fresh();
        });
    }

    public function update(
        MachineryDailyUsage $usage,
        array $data
    ): MachineryDailyUsage {
        return DB::transaction(function () use ($usage, $data) {
            $usage = MachineryDailyUsage::query()
                ->lockForUpdate()
                ->findOrFail($usage->id);

            if ($usage->status === MachineryDailyUsage::STATUS_VERIFIED) {
                throw ValidationException::withMessages([
                    'status' =>
                        'A verified machinery usage record cannot be edited.',
                ]);
            }

            if ($usage->status === MachineryDailyUsage::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'status' =>
                        'A cancelled machinery usage record cannot be edited.',
                ]);
            }

            $equipment = MachineryEquipment::query()
                ->lockForUpdate()
                ->findOrFail($data['machinery_equipment_id']);

            $prepared = $this->prepareData(
                $equipment,
                $data,
                $usage
            );

            $prepared['updated_by'] = Auth::id();

            $usage->update($prepared);

            $this->syncEquipmentMeterSnapshot(
                $equipment,
                $usage->fresh()
            );

            return $usage->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Data Preparation
    |--------------------------------------------------------------------------
    */

    public function prepareData(
        MachineryEquipment $equipment,
        array $data,
        ?MachineryDailyUsage $existingUsage = null
    ): array {
        $projectId = (int) $data['project_id'];

        $this->validateEquipmentAtProject(
            $equipment,
            $projectId
        );

        $quantityUsed = $this->validateQuantity(
            $equipment,
            $projectId,
            $data['quantity_used'] ?? 1
        );

        $siteHours = $this->calculateSiteHours(
            $data['start_time'] ?? null,
            $data['end_time'] ?? null
        );

        $meterType = $equipment->meter_type ?: 'none';

        $openingMeter = $this->nullableFloat(
            $data['opening_meter_reading'] ?? null
        );

        $closingMeter = $this->nullableFloat(
            $data['closing_meter_reading'] ?? null
        );

        $meterOperatingHours = null;

        if ($meterType === 'hour_meter') {
            $meterOperatingHours = $this->calculateHourMeterHours(
                $openingMeter,
                $closingMeter
            );

            if ($meterType === 'hour_meter') {
    $meterOperatingHours = $this->calculateHourMeterHours(
        $openingMeter,
        $closingMeter
    );

    $this->validateMeterContinuity(
        $equipment,
        $openingMeter,
        $existingUsage
    );

    /*
     * When editing an existing record, also protect continuity
     * with the next chronological meter record.
     */
    if ($existingUsage) {
        $this->validateNextMeterContinuity(
            $equipment,
            $closingMeter,
            $existingUsage
        );
    }
} else {
    /*
     * Odometer delta is distance, not operating hours.
     * We deliberately leave meter_operating_hours NULL.
     */
    $meterOperatingHours = null;
}
        } else {
            /*
             * Odometer delta is distance, not operating hours.
             * We deliberately leave meter_operating_hours NULL.
             */
            $meterOperatingHours = null;
        }

        $manualHours = $this->nullableFloat(
            $data['manual_operating_hours'] ?? null
        );

        $manualReason = isset($data['manual_hours_reason'])
            ? trim((string) $data['manual_hours_reason'])
            : null;

        if ($manualReason === '') {
            $manualReason = null;
        }

        $finalOperatingHours = $this->resolveFinalOperatingHours(
            $meterType,
            $meterOperatingHours,
            $manualHours,
            $manualReason
        );

        $idleHours = round(
            max(
                0,
                (float) ($data['idle_hours'] ?? 0)
            ),
            2
        );

        $breakdownHours = round(
            max(
                0,
                (float) ($data['breakdown_hours'] ?? 0)
            ),
            2
        );

        $this->validateHours(
            $siteHours,
            $finalOperatingHours,
            $idleHours,
            $breakdownHours
        );

        $this->validateLocationHierarchy(
            $projectId,
            $data
        );

        $this->validateWorkDoneLink(
            $projectId,
            $data
        );

        return [
            'project_id' => $projectId,
            'machinery_equipment_id' => $equipment->id,
            'usage_date' => $data['usage_date'],

            'quantity_used' => $quantityUsed,
            'unit' => $equipment->unit,

            'shift' =>
                $data['shift']
                ?? MachineryDailyUsage::SHIFT_GENERAL,

            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'site_hours' => $siteHours,

            'meter_type' => $meterType,

            'opening_meter_reading' => $openingMeter,
            'closing_meter_reading' => $closingMeter,
            'meter_operating_hours' => $meterOperatingHours,

            'manual_operating_hours' => $manualHours,
            'final_operating_hours' => $finalOperatingHours,
            'manual_hours_reason' => $manualReason,

            'idle_hours' => $idleHours,
            'breakdown_hours' => $breakdownHours,

            'working_condition' =>
                $data['working_condition']
                ?? MachineryDailyUsage::CONDITION_WORKING,

            'operator_user_id' =>
                $this->nullableInt(
                    $data['operator_user_id'] ?? null
                ),

            'operator_name' =>
                $this->nullableString(
                    $data['operator_name'] ?? null
                ),

            'operator_mobile' =>
                $this->nullableString(
                    $data['operator_mobile'] ?? null
                ),

            'work_done_item_id' =>
                $this->nullableInt(
                    $data['work_done_item_id'] ?? null
                ),

            'work_activity_id' =>
                $this->nullableInt(
                    $data['work_activity_id'] ?? null
                ),

            'project_block_id' =>
                $this->nullableInt(
                    $data['project_block_id'] ?? null
                ),

            'project_floor_id' =>
                $this->nullableInt(
                    $data['project_floor_id'] ?? null
                ),

            'project_unit_id' =>
                $this->nullableInt(
                    $data['project_unit_id'] ?? null
                ),

            'project_room_id' =>
                $this->nullableInt(
                    $data['project_room_id'] ?? null
                ),

            'project_subspace_id' =>
                $this->nullableInt(
                    $data['project_subspace_id'] ?? null
                ),

            'fuel_energy_quantity' =>
                $this->nullableFloat(
                    $data['fuel_energy_quantity'] ?? null
                ),

            'fuel_energy_unit' =>
                $this->nullableString(
                    $data['fuel_energy_unit'] ?? null
                ),

            'work_description' =>
                $this->nullableString(
                    $data['work_description'] ?? null
                ),

            'remarks' =>
                $this->nullableString(
                    $data['remarks'] ?? null
                ),

            'status' =>
                $data['status']
                ?? MachineryDailyUsage::STATUS_SUBMITTED,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Allocation / Quantity Validation
    |--------------------------------------------------------------------------
    */

    protected function validateEquipmentAtProject(
        MachineryEquipment $equipment,
        int $projectId
    ): void {
        if (!$equipment->is_active) {
            throw ValidationException::withMessages([
                'machinery_equipment_id' =>
                    'Inactive equipment cannot be reported for daily usage.',
            ]);
        }

        $confirmed = $this->confirmedQuantityAtProject(
            $equipment,
            $projectId
        );

        if ($confirmed <= 0) {
            throw ValidationException::withMessages([
                'machinery_equipment_id' =>
                    'This equipment is not currently allocated to the selected project.',
            ]);
        }
    }

    protected function validateQuantity(
        MachineryEquipment $equipment,
        int $projectId,
        mixed $quantity
    ): float {
        $quantity = round((float) $quantity, 3);

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity_used' =>
                    'Quantity used must be greater than zero.',
            ]);
        }

        if ($equipment->tracking_mode === 'individual') {
            if (abs($quantity - 1.0) > 0.0005) {
                throw ValidationException::withMessages([
                    'quantity_used' =>
                        'Individual equipment must be reported as quantity 1.',
                ]);
            }

            return 1.0;
        }

        $confirmed = $this->confirmedQuantityAtProject(
            $equipment,
            $projectId
        );

        if ($quantity > $confirmed) {
            throw ValidationException::withMessages([
                'quantity_used' =>
                    sprintf(
                        'Only %s %s of this equipment is located at the selected project.',
                        $this->formatQuantity($confirmed),
                        $equipment->unit
                    ),
            ]);
        }

        return $quantity;
    }

    /*
    |--------------------------------------------------------------------------
    | Time Calculations
    |--------------------------------------------------------------------------
    */

    public function calculateSiteHours(
        ?string $startTime,
        ?string $endTime
    ): ?float {
        if (!$startTime || !$endTime) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat(
                'H:i',
                substr($startTime, 0, 5)
            );

            $end = Carbon::createFromFormat(
                'H:i',
                substr($endTime, 0, 5)
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'start_time' =>
                    'Start time or end time is invalid.',
            ]);
        }

        /*
         * End <= start is interpreted as crossing midnight.
         * Example: 20:00 -> 05:00 = 9 hours.
         */
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $minutes = $start->diffInMinutes($end);

        if ($minutes > 1440) {
            throw ValidationException::withMessages([
                'end_time' =>
                    'A machinery usage session cannot exceed 24 hours.',
            ]);
        }

        return round($minutes / 60, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Meter Logic
    |--------------------------------------------------------------------------
    */

    protected function calculateHourMeterHours(
        ?float $opening,
        ?float $closing
    ): ?float {
        if ($opening === null && $closing === null) {
            return null;
        }

        if ($opening === null || $closing === null) {
            throw ValidationException::withMessages([
                'closing_meter_reading' =>
                    'Both opening and closing meter readings are required for hour-meter equipment.',
            ]);
        }

        if ($closing < $opening) {
            throw ValidationException::withMessages([
                'closing_meter_reading' =>
                    'Closing meter reading cannot be lower than opening meter reading.',
            ]);
        }

        return round($closing - $opening, 2);
    }

    protected function validateMeterContinuity(
    MachineryEquipment $equipment,
    ?float $openingReading,
    ?MachineryDailyUsage $existingUsage = null
): void {
    if ($openingReading === null) {
        return;
    }

    /*
     * Determine the chronological point of the record being created/edited.
     *
     * For an edit, continuity must be checked against usage records that
     * occurred BEFORE this record — not against the equipment snapshot,
     * because that snapshot may already include this record or later records.
     */
    $query = MachineryDailyUsage::query()
        ->where(
            'machinery_equipment_id',
            $equipment->id
        )
        ->where('meter_type', 'hour_meter')
        ->whereNotNull('closing_meter_reading')
        ->whereNotIn('status', [
            MachineryDailyUsage::STATUS_CANCELLED,
        ]);

    if ($existingUsage) {
        $query->where(function ($q) use ($existingUsage) {
            $q->whereDate(
                'usage_date',
                '<',
                $existingUsage->usage_date
            )
            ->orWhere(function ($sameDate) use ($existingUsage) {
                $sameDate
                    ->whereDate(
                        'usage_date',
                        '=',
                        $existingUsage->usage_date
                    )
                    ->where(
                        'id',
                        '<',
                        $existingUsage->id
                    );
            });
        });
    }

    $previousClosing = $query
        ->orderByDesc('usage_date')
        ->orderByDesc('id')
        ->value('closing_meter_reading');

    /*
     * For a brand-new usage record with no Daily Usage history,
     * the equipment register reading is our starting reference.
     *
     * During EDIT we deliberately do NOT fall back to the equipment
     * snapshot because it may already contain this record's closing
     * reading or a later record's reading.
     */
    if (
        !$existingUsage
        && $previousClosing === null
    ) {
        $previousClosing =
            $equipment->current_meter_reading;
    }

    if ($previousClosing === null) {
        return;
    }

    $previousClosing = (float) $previousClosing;

    /*
     * A higher opening is legitimate because the machine may have
     * operated outside this workflow between reports.
     *
     * Moving backwards is not legitimate.
     */
    if ($openingReading + 0.0005 < $previousClosing) {
        throw ValidationException::withMessages([
            'opening_meter_reading' =>
                sprintf(
                    'Opening meter reading cannot be lower than the previous recorded reading of %s.',
                    $this->formatQuantity(
                        $previousClosing
                    )
                ),
        ]);
    }
}

protected function validateNextMeterContinuity(
    MachineryEquipment $equipment,
    ?float $closingReading,
    MachineryDailyUsage $existingUsage
): void {
    if ($closingReading === null) {
        return;
    }

    $nextUsage = MachineryDailyUsage::query()
        ->where(
            'machinery_equipment_id',
            $equipment->id
        )
        ->where('meter_type', 'hour_meter')
        ->whereNotNull('opening_meter_reading')
        ->whereNotIn('status', [
            MachineryDailyUsage::STATUS_CANCELLED,
        ])
        ->where(function ($query) use ($existingUsage) {
            $query
                ->whereDate(
                    'usage_date',
                    '>',
                    $existingUsage->usage_date
                )
                ->orWhere(function ($sameDate) use ($existingUsage) {
                    $sameDate
                        ->whereDate(
                            'usage_date',
                            '=',
                            $existingUsage->usage_date
                        )
                        ->where(
                            'id',
                            '>',
                            $existingUsage->id
                        );
                });
        })
        ->orderBy('usage_date')
        ->orderBy('id')
        ->first();

    if (!$nextUsage) {
        return;
    }

    $nextOpening =
        (float) $nextUsage->opening_meter_reading;

    if ($closingReading > ($nextOpening + 0.0005)) {
        throw ValidationException::withMessages([
            'closing_meter_reading' =>
                sprintf(
                    'Closing meter reading cannot exceed the next recorded opening reading of %s.',
                    $this->formatQuantity(
                        $nextOpening
                    )
                ),
        ]);
    }
}

    protected function resolveFinalOperatingHours(
        string $meterType,
        ?float $meterHours,
        ?float $manualHours,
        ?string $manualReason
    ): ?float {
        if ($manualHours !== null && $manualHours < 0) {
            throw ValidationException::withMessages([
                'manual_operating_hours' =>
                    'Manual operating hours cannot be negative.',
            ]);
        }

        if ($meterType === 'hour_meter') {
            if ($manualHours !== null) {
                if (!$manualReason) {
                    throw ValidationException::withMessages([
                        'manual_hours_reason' =>
                            'A reason is required when manually overriding hour-meter operating hours.',
                    ]);
                }

                return round($manualHours, 2);
            }

            return $meterHours !== null
                ? round($meterHours, 2)
                : null;
        }

        /*
         * none / odometer:
         * operating hours are manually recorded.
         */
        return $manualHours !== null
            ? round($manualHours, 2)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Hours Validation
    |--------------------------------------------------------------------------
    */

    protected function validateHours(
        ?float $siteHours,
        ?float $operatingHours,
        float $idleHours,
        float $breakdownHours
    ): void {
        if ($operatingHours !== null && $operatingHours < 0) {
            throw ValidationException::withMessages([
                'manual_operating_hours' =>
                    'Operating hours cannot be negative.',
            ]);
        }

        if ($siteHours === null) {
            return;
        }

        if (
            $operatingHours !== null
            && $operatingHours > ($siteHours + 0.01)
        ) {
            throw ValidationException::withMessages([
                'manual_operating_hours' =>
                    'Operating hours cannot exceed the recorded site session hours.',
            ]);
        }

        if ($idleHours > ($siteHours + 0.01)) {
            throw ValidationException::withMessages([
                'idle_hours' =>
                    'Idle hours cannot exceed the recorded site session hours.',
            ]);
        }

        if ($breakdownHours > ($siteHours + 0.01)) {
            throw ValidationException::withMessages([
                'breakdown_hours' =>
                    'Breakdown hours cannot exceed the recorded site session hours.',
            ]);
        }

        $classified =
            ($operatingHours ?? 0)
            + $idleHours
            + $breakdownHours;

        if ($classified > ($siteHours + 0.02)) {
            throw ValidationException::withMessages([
                'idle_hours' =>
                    'Operating, idle and breakdown hours together cannot exceed the recorded site session hours.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Location Validation
    |--------------------------------------------------------------------------
    */

    protected function validateLocationHierarchy(
        int $projectId,
        array $data
    ): void {
        $blockId = $this->nullableInt(
            $data['project_block_id'] ?? null
        );

        $floorId = $this->nullableInt(
            $data['project_floor_id'] ?? null
        );

        $unitId = $this->nullableInt(
            $data['project_unit_id'] ?? null
        );

        $roomId = $this->nullableInt(
            $data['project_room_id'] ?? null
        );

        $subspaceId = $this->nullableInt(
            $data['project_subspace_id'] ?? null
        );

        if ($blockId !== null) {
            $exists = DB::table('project_blocks')
                ->where('id', $blockId)
                ->where('project_id', $projectId)
                ->exists();

            if (!$exists) {
                throw ValidationException::withMessages([
                    'project_block_id' =>
                        'The selected block does not belong to the selected project.',
                ]);
            }
        }

        if ($floorId !== null) {
            $query = DB::table('project_floors')
                ->where('id', $floorId)
                ->where('project_id', $projectId);

            if ($blockId !== null) {
                $query->where(
                    'project_block_id',
                    $blockId
                );
            }

            if (!$query->exists()) {
                throw ValidationException::withMessages([
                    'project_floor_id' =>
                        'The selected floor does not belong to the selected project/block.',
                ]);
            }
        }

        if ($unitId !== null) {
            $query = DB::table('project_units')
                ->where('id', $unitId)
                ->where('project_id', $projectId);

            if ($blockId !== null) {
                $query->where(
                    'project_block_id',
                    $blockId
                );
            }

            if ($floorId !== null) {
                $query->where(
                    'project_floor_id',
                    $floorId
                );
            }

            if (!$query->exists()) {
                throw ValidationException::withMessages([
                    'project_unit_id' =>
                        'The selected unit does not belong to the selected project location.',
                ]);
            }
        }

        if ($roomId !== null) {
            $query = DB::table('project_rooms')
                ->where('id', $roomId)
                ->where('project_id', $projectId);

            if ($blockId !== null) {
                $query->where(
                    'project_block_id',
                    $blockId
                );
            }

            if ($floorId !== null) {
                $query->where(
                    'project_floor_id',
                    $floorId
                );
            }

            if ($unitId !== null) {
                $query->where(
                    'project_unit_id',
                    $unitId
                );
            }

            if (!$query->exists()) {
                throw ValidationException::withMessages([
                    'project_room_id' =>
                        'The selected room does not belong to the selected project location.',
                ]);
            }
        }

        if ($subspaceId !== null) {
            $query = DB::table('project_subspaces')
                ->where('id', $subspaceId)
                ->where('project_id', $projectId);

            if ($blockId !== null) {
                $query->where(
                    'project_block_id',
                    $blockId
                );
            }

            if ($floorId !== null) {
                $query->where(
                    'project_floor_id',
                    $floorId
                );
            }

            if ($unitId !== null) {
                $query->where(
                    'project_unit_id',
                    $unitId
                );
            }

            if ($roomId !== null) {
                $query->where(
                    'project_room_id',
                    $roomId
                );
            }

            if (!$query->exists()) {
                throw ValidationException::withMessages([
                    'project_subspace_id' =>
                        'The selected sub-space does not belong to the selected project location.',
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Work Done Validation
    |--------------------------------------------------------------------------
    */

    protected function validateWorkDoneLink(
        int $projectId,
        array $data
    ): void {
        $workDoneItemId = $this->nullableInt(
            $data['work_done_item_id'] ?? null
        );

        $workActivityId = $this->nullableInt(
            $data['work_activity_id'] ?? null
        );

        if ($workActivityId !== null) {
            $activityExists = DB::table('work_activities')
                ->where('id', $workActivityId)
                ->where('is_active', true)
                ->where('allow_equipment', true)
                ->exists();

            if (!$activityExists) {
                throw ValidationException::withMessages([
                    'work_activity_id' =>
                        'The selected work activity is not active or does not allow equipment.',
                ]);
            }
        }

        if ($workDoneItemId === null) {
            return;
        }

        /*
         * Project is stored on work_done_headers, not work_done_items.
         */
        $item = DB::table('work_done_items as wdi')
            ->join(
                'work_done_headers as wdh',
                'wdh.id',
                '=',
                'wdi.work_done_header_id'
            )
            ->where('wdi.id', $workDoneItemId)
            ->where('wdh.project_id', $projectId)
            ->select([
                'wdi.id',
                'wdi.work_activity_id',
            ])
            ->first();

        if (!$item) {
            throw ValidationException::withMessages([
                'work_done_item_id' =>
                    'The selected Work Done item does not belong to the selected project.',
            ]);
        }

        if (
            $workActivityId !== null
            && $item->work_activity_id !== null
            && (int) $item->work_activity_id !== $workActivityId
        ) {
            throw ValidationException::withMessages([
                'work_activity_id' =>
                    'The selected work activity does not match the Work Done item.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Equipment Meter Snapshot
    |--------------------------------------------------------------------------
    */

    protected function syncEquipmentMeterSnapshot(
    MachineryEquipment $equipment,
    MachineryDailyUsage $usage
): void {
    if ($usage->meter_type !== 'hour_meter') {
        return;
    }

    /*
     * Rebuild the snapshot from the latest active hour-meter
     * Daily Usage record.
     *
     * This is safer than only increasing the snapshot because an
     * edit to the latest record may legitimately correct its
     * closing reading downward.
     */
    $latestClosing = MachineryDailyUsage::query()
        ->where(
            'machinery_equipment_id',
            $equipment->id
        )
        ->where('meter_type', 'hour_meter')
        ->whereNotNull('closing_meter_reading')
        ->whereNotIn('status', [
            MachineryDailyUsage::STATUS_CANCELLED,
        ])
        ->orderByDesc('usage_date')
        ->orderByDesc('id')
        ->value('closing_meter_reading');

    /*
     * If no valid Daily Usage meter record exists, do not erase
     * the equipment register's existing meter reading. That value
     * may have been established during equipment registration or
     * migration.
     */
    if ($latestClosing === null) {
        return;
    }

    $latestClosing = round(
        (float) $latestClosing,
        3
    );

    $current =
        $equipment->current_meter_reading !== null
            ? round(
                (float) $equipment->current_meter_reading,
                3
            )
            : null;

    if (
        $current === null
        || abs($current - $latestClosing) > 0.0005
    ) {
        $equipment->update([
            'current_meter_reading' =>
                $latestClosing,

            'updated_by' =>
                Auth::id(),
        ]);
    }
}

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    protected function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }

    protected function formatQuantity(float $quantity): string
    {
        return rtrim(
            rtrim(
                number_format(
                    $quantity,
                    3,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }
}