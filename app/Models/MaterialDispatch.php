<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialDispatch extends Model
{
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_DISPATCHED = 'Dispatched';
    public const STATUS_PARTIALLY_RECEIVED = 'Partially Received';
    public const STATUS_RECEIVED = 'Received';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'dispatch_number',
        'project_id',
        'dispatch_date',
        'expected_delivery_date',

        'dispatch_from',
        'dispatch_from_address',

        'project_name',
        'delivery_address',

        'transport_mode',
        'vehicle_number',
        'driver_name',
        'driver_mobile',
        'transporter_name',
        'challan_number',
        'reference_number',

        'status',

        'dispatch_notes',
        'internal_remarks',

        'created_by',
        'dispatched_by',
        'dispatched_at',

        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'expected_delivery_date' => 'date',
        'dispatched_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialDispatchItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getTotalDispatchedQuantityAttribute(): float
    {
        return (float) $this->items->sum('dispatched_quantity');
    }

    public function getTotalAcceptedQuantityAttribute(): float
    {
        return (float) $this->items->sum('accepted_quantity');
    }

    public function getTotalShortQuantityAttribute(): float
    {
        return (float) $this->items->sum('short_quantity');
    }

    public function getTotalDamagedQuantityAttribute(): float
    {
        return (float) $this->items->sum('damaged_quantity');
    }

    public function getTotalRejectedQuantityAttribute(): float
    {
        return (float) $this->items->sum('rejected_quantity');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isDispatched(): bool
    {
        return $this->status === self::STATUS_DISPATCHED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
    public function receipts(): HasMany
{
    return $this->hasMany(MaterialDispatchReceipt::class);
}
}