<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialConsumed extends Model
{
    protected $fillable = [
        'dpr_id',
        'work_done_item_id',

        'project_id',
        'user_id',

        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',

        'contractor_id',

        'consumed_date',
        'consumed_time',

        'related_work_output_quantity',

        'status',
        'remarks',

        /*
         * Legacy single-material fields retained for old records.
         */
        'activity_division_id',
        'activity_id',
        'activity_mapping_id',
        'material_category_id',
        'material_id',
        'quantity_consumed',
        'unit',
        'wastage_quantity',
        'wastage_reason',
    ];

    protected $casts = [
        'dpr_id' => 'integer',
        'work_done_item_id' => 'integer',
        'project_id' => 'integer',
        'user_id' => 'integer',

        'consumed_date' => 'date',

        'quantity_consumed' => 'decimal:3',
        'related_work_output_quantity' => 'decimal:3',
        'wastage_quantity' => 'decimal:3',
    ];

    /*
    |--------------------------------------------------------------------------
    | Header Relationships
    |--------------------------------------------------------------------------
    */

    public function dpr(): BelongsTo
    {
        return $this->belongsTo(Dpr::class);
    }

    public function workDoneItem(): BelongsTo
    {
        return $this->belongsTo(
            WorkDoneItem::class,
            'work_done_item_id'
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

    public function block(): BelongsTo
    {
        return $this->belongsTo(
            ProjectBlock::class,
            'project_block_id'
        );
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFloor::class,
            'project_floor_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            ProjectUnit::class,
            'project_unit_id'
        );
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(
            ProjectRoom::class,
            'project_room_id'
        );
    }

    public function subspace(): BelongsTo
    {
        return $this->belongsTo(
            ProjectSubspace::class,
            'project_subspace_id'
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(
            Contractor::class,
            'contractor_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | New Multi-item Relationship
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(
            MaterialConsumedItem::class,
            'material_consumed_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy Single-material Relationships
    |--------------------------------------------------------------------------
    */

    public function activityDivision(): BelongsTo
    {
        return $this->belongsTo(
            ActivityDivision::class,
            'activity_division_id'
        );
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'activity_id'
        );
    }

    public function activityMapping(): BelongsTo
    {
        return $this->belongsTo(
            ActivityMapping::class,
            'activity_mapping_id'
        );
    }

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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getTotalQuantityConsumedAttribute(): float
    {
        if (
            $this->relationLoaded('items')
            && $this->items->isNotEmpty()
        ) {
            return (float) $this->items
                ->sum('quantity_consumed');
        }

        if ($this->items()->exists()) {
            return (float) $this->items()
                ->sum('quantity_consumed');
        }

        return (float) ($this->quantity_consumed ?? 0);
    }

    public function getTotalWastageQuantityAttribute(): float
    {
        if (
            $this->relationLoaded('items')
            && $this->items->isNotEmpty()
        ) {
            return (float) $this->items
                ->sum('wastage_quantity');
        }

        if ($this->items()->exists()) {
            return (float) $this->items()
                ->sum('wastage_quantity');
        }

        return (float) ($this->wastage_quantity ?? 0);
    }

    public function getTotalIssuedQuantityAttribute(): float
    {
        return $this->total_quantity_consumed
            + $this->total_wastage_quantity;
    }

    public function getHasItemRowsAttribute(): bool
    {
        return $this->items()->exists();
    }

    public function getHasMultipleItemsAttribute(): bool
    {
        return $this->items()->count() > 1;
    }

    public function getIsWorkLinkedAttribute(): bool
    {
        return ! empty($this->work_done_item_id);
    }

    public function getIsDprLinkedAttribute(): bool
    {
        return ! empty($this->dpr_id);
    }
}