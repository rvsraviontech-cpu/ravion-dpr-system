<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialDispatchReceipt extends Model
{
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_RECEIVED = 'Received';
    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'material_dispatch_id',
        'receipt_number',
        'project_id',
        'receipt_date',

        'dispatch_number',
        'project_name',
        'dispatch_from',

        'challan_number',
        'vehicle_number',
        'driver_name',

        'status',

        'receipt_remarks',
        'internal_remarks',

        'created_by',
        'received_by',
        'received_at',

        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function materialDispatch(): BelongsTo
    {
        return $this->belongsTo(MaterialDispatch::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialDispatchReceiptItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getTotalReceivedQuantityAttribute(): float
    {
        return (float) $this->items->sum('received_quantity');
    }

    public function getTotalAcceptedQuantityAttribute(): float
    {
        return (float) $this->items->sum('accepted_quantity');
    }

    public function getTotalDamagedQuantityAttribute(): float
    {
        return (float) $this->items->sum('damaged_quantity');
    }

    public function getTotalRejectedQuantityAttribute(): float
    {
        return (float) $this->items->sum('rejected_quantity');
    }

    public function getTotalShortQuantityAttribute(): float
    {
        return (float) $this->items->sum('short_quantity');
    }
}