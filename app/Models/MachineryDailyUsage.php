<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineryDailyUsage extends Model
{
    use HasFactory;

    public const SHIFT_GENERAL = 'general';
    public const SHIFT_DAY = 'day';
    public const SHIFT_NIGHT = 'night';
    public const SHIFT_CUSTOM = 'custom';

    public const CONDITION_WORKING = 'working';
    public const CONDITION_IDLE = 'idle';
    public const CONDITION_BREAKDOWN = 'breakdown';
    public const CONDITION_MAINTENANCE = 'maintenance';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'project_id',
        'machinery_equipment_id',
        'usage_date',

        'quantity_used',
        'unit',

        'shift',
        'start_time',
        'end_time',
        'site_hours',

        'meter_type',
        'opening_meter_reading',
        'closing_meter_reading',
        'meter_operating_hours',
        'manual_operating_hours',
        'final_operating_hours',
        'manual_hours_reason',

        'idle_hours',
        'breakdown_hours',

        'working_condition',

        'operator_user_id',
        'operator_name',
        'operator_mobile',

        'work_done_item_id',
        'work_activity_id',

        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',

        'fuel_energy_quantity',
        'fuel_energy_unit',

        'work_description',
        'remarks',

        'status',

        'submitted_by',
        'submitted_at',

        'verified_by',
        'verified_at',
        'verification_remarks',

        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'usage_date' => 'date',

        'quantity_used' => 'decimal:3',
        'site_hours' => 'decimal:2',

        'opening_meter_reading' => 'decimal:3',
        'closing_meter_reading' => 'decimal:3',

        'meter_operating_hours' => 'decimal:2',
        'manual_operating_hours' => 'decimal:2',
        'final_operating_hours' => 'decimal:2',

        'idle_hours' => 'decimal:2',
        'breakdown_hours' => 'decimal:2',

        'fuel_energy_quantity' => 'decimal:3',

        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(
            MachineryEquipment::class,
            'machinery_equipment_id'
        );
    }

    public function operatorUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'operator_user_id'
        );
    }

    public function workDoneItem(): BelongsTo
    {
        return $this->belongsTo(
            WorkDoneItem::class,
            'work_done_item_id'
        );
    }

    public function workActivity(): BelongsTo
    {
        return $this->belongsTo(
            WorkActivity::class,
            'work_activity_id'
        );
    }

    public function projectBlock(): BelongsTo
    {
        return $this->belongsTo(
            ProjectBlock::class,
            'project_block_id'
        );
    }

    public function projectFloor(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFloor::class,
            'project_floor_id'
        );
    }

    public function projectUnit(): BelongsTo
    {
        return $this->belongsTo(
            ProjectUnit::class,
            'project_unit_id'
        );
    }

    public function projectRoom(): BelongsTo
    {
        return $this->belongsTo(
            ProjectRoom::class,
            'project_room_id'
        );
    }

    public function projectSubspace(): BelongsTo
    {
        return $this->belongsTo(
            ProjectSubspace::class,
            'project_subspace_id'
        );
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public static function shifts(): array
    {
        return [
            self::SHIFT_GENERAL => 'General',
            self::SHIFT_DAY => 'Day',
            self::SHIFT_NIGHT => 'Night',
            self::SHIFT_CUSTOM => 'Custom',
        ];
    }

    public static function workingConditions(): array
    {
        return [
            self::CONDITION_WORKING => 'Working',
            self::CONDITION_IDLE => 'Idle',
            self::CONDITION_BREAKDOWN => 'Breakdown',
            self::CONDITION_MAINTENANCE => 'Maintenance',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeVerified(): bool
    {
        return $this->isSubmitted();
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft() || $this->isSubmitted();
    }

    public function usesHourMeter(): bool
    {
        return $this->meter_type === 'hour_meter';
    }

    public function usesOdometer(): bool
    {
        return $this->meter_type === 'odometer';
    }

    public function hasManualHoursOverride(): bool
    {
        return $this->manual_operating_hours !== null;
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForEquipment($query, int $equipmentId)
    {
        return $query->where(
            'machinery_equipment_id',
            $equipmentId
        );
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('usage_date', $date);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopePendingVerification($query)
    {
        return $query->where(
            'status',
            self::STATUS_SUBMITTED
        );
    }
}