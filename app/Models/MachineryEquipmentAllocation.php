<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineryEquipmentAllocation extends Model
{
    public const TYPE_INITIAL = 'initial_allocation';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_RETURN = 'return';
    public const TYPE_TEMPORARY = 'temporary_transfer';
    public const TYPE_ADJUSTMENT = 'quantity_adjustment';

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'machinery_equipment_id',
        'allocation_number',
        'movement_type',

        'from_project_id',
        'to_project_id',

        'quantity',
        'unit',

        'movement_date',
        'movement_time',

        'expected_return_date',
        'actual_return_date',

        'reference_number',
        'challan_number',

        'vehicle_number',
        'driver_name',
        'driver_mobile',

        'status',

        'transferred_by',
        'transferred_at',

        'received_by',
        'received_at',

        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',

        'remarks',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',

        'movement_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',

        'transferred_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(
            MachineryEquipment::class,
            'machinery_equipment_id'
        );
    }

    public function fromProject(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'from_project_id'
        );
    }

    public function toProject(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'to_project_id'
        );
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'transferred_by'
        );
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'received_by'
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeReceived(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_RECEIVED
        );
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeInTransit(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_IN_TRANSIT
        );
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where(
            'status',
            '!=',
            self::STATUS_CANCELLED
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function movementTypes(): array
    {
        return [
            self::TYPE_INITIAL => 'Initial Allocation',
            self::TYPE_TRANSFER => 'Project Transfer',
            self::TYPE_RETURN => 'Return / Off Site',
            self::TYPE_TEMPORARY => 'Temporary Transfer',
            self::TYPE_ADJUSTMENT => 'Quantity Adjustment',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}