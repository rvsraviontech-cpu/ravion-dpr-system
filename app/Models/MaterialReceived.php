<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialReceived extends Model
{
    protected $fillable = [
        'dpr_id',
        'project_id',
        'user_id',

        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'storage_location',

        // Legacy single-material fields
        'material_category_id',
        'material_id',
        'material_category',
        'material_name',
        'specification',
        'brand',
        'quantity_received',
        'unit',

        'vendor_id',
        'vendor_name',

        'supplied_by_contractor',
        'contractor_id',

        'vehicle_number',
        'driver_name',
        'challan_number',
        'bill_number',

        'received_date',
        'received_time',
        'material_condition',

        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',

        'site_engineer_verification_status',
        'pmo_verification_status',
        'accountant_verification_status',

        'status',
        'remarks',

        'submitted_at',
        'approved_at',
        'approved_by',

        'accountant_verified_by',
        'accountant_verified_at',
    ];

    protected $casts = [
        'received_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'accountant_verified_at' => 'datetime',

        'supplied_by_contractor' => 'boolean',

        'quantity_received' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'short_quantity' => 'decimal:3',
        'damaged_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    /*
    |--------------------------------------------------------------------------
    | Header Relationships
    |--------------------------------------------------------------------------
    */

    public function dpr(): BelongsTo
    {
        return $this->belongsTo(
            Dpr::class,
            'dpr_id'
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->engineer();
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(
            ProjectBlock::class,
            'project_block_id'
        );
    }

    public function projectBlock(): BelongsTo
    {
        return $this->block();
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFloor::class,
            'project_floor_id'
        );
    }

    public function projectFloor(): BelongsTo
    {
        return $this->floor();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            ProjectUnit::class,
            'project_unit_id'
        );
    }

    public function projectUnit(): BelongsTo
    {
        return $this->unit();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(
            Vendor::class,
            'vendor_id'
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(
            Contractor::class,
            'contractor_id'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function accountantVerifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'accountant_verified_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Material Items
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(
            MaterialReceivedItem::class,
            'material_received_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Material Receipt Photos
    |--------------------------------------------------------------------------
    */

    public function photos(): HasMany
    {
        return $this->hasMany(
            MaterialReceivedPhoto::class,
            'material_received_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function receiptLevelPhotos(): HasMany
    {
        return $this->hasMany(
            MaterialReceivedPhoto::class,
            'material_received_id'
        )
            ->whereNull('material_received_item_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy Relationships
    |--------------------------------------------------------------------------
    */

    public function materialCategory(): BelongsTo
    {
        return $this->belongsTo(
            MaterialCategory::class,
            'material_category_id'
        );
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(
            Material::class,
            'material_id'
        );
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(
            MaterialVerification::class,
            'material_received_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Quantity Helpers
    |--------------------------------------------------------------------------
    */

    public function getTotalQuantityReceivedAttribute(): float
    {
        if (
            $this->relationLoaded('items')
            && $this->items->isNotEmpty()
        ) {
            return (float) $this->items->sum(
                'quantity_received'
            );
        }

        if ($this->items()->exists()) {
            return (float) $this->items()->sum(
                'quantity_received'
            );
        }

        return (float) ($this->quantity_received ?? 0);
    }

    public function getTotalAcceptedQuantityAttribute(): float
    {
        if (
            $this->relationLoaded('items')
            && $this->items->isNotEmpty()
        ) {
            return (float) $this->items->sum(
                'accepted_quantity'
            );
        }

        if ($this->items()->exists()) {
            return (float) $this->items()->sum(
                'accepted_quantity'
            );
        }

        return (float) ($this->accepted_quantity ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Record Helpers
    |--------------------------------------------------------------------------
    */

    public function getHasItemRowsAttribute(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->isNotEmpty();
        }

        return $this->items()->exists();
    }

    public function getHasMultipleItemsAttribute(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->count() > 1;
        }

        return $this->items()->count() > 1;
    }

    public function getHasPhotosAttribute(): bool
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->isNotEmpty();
        }

        return $this->photos()->exists();
    }

    public function getPhotoCountAttribute(): int
    {
        if ($this->relationLoaded('photos')) {
            return $this->photos->count();
        }

        return $this->photos()->count();
    }
}