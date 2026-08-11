<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequirement extends Model
{
    protected $fillable = [
        'dpr_id',

        'project_id',
        'project_block_id',

        'required_date',
        'priority',
        'status',
        'remarks',

        'created_by',
        'approved_by',
        'approved_at',

        /*
         * Legacy single-material fields retained for existing records.
         */
        'material_category_id',
        'material_id',
        'required_quantity',
        'fulfilled_quantity',
        'unit',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at' => 'datetime',

        'required_quantity' => 'decimal:3',
        'fulfilled_quantity' => 'decimal:3',
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

    public function block(): BelongsTo
    {
        return $this->belongsTo(
            ProjectBlock::class,
            'project_block_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
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
            MaterialRequirementItem::class,
            'material_requirement_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy Single-material Relationships
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

    /*
    |--------------------------------------------------------------------------
    | Quantity Helpers
    |--------------------------------------------------------------------------
    */

    public function getTotalRequiredQuantityAttribute(): float
    {
        if (
            $this->relationLoaded('items')
            && $this->items->isNotEmpty()
        ) {
            return (float) $this->items->sum(
                'required_quantity'
            );
        }

        if ($this->items()->exists()) {
            return (float) $this->items()->sum(
                'required_quantity'
            );
        }

        return (float) ($this->required_quantity ?? 0);
    }

    public function getTotalFulfilledQuantityAttribute(): float
    {
        if (
            $this->relationLoaded('items')
            && $this->items->isNotEmpty()
        ) {
            return (float) $this->items->sum(
                'fulfilled_quantity'
            );
        }

        if ($this->items()->exists()) {
            return (float) $this->items()->sum(
                'fulfilled_quantity'
            );
        }

        return (float) ($this->fulfilled_quantity ?? 0);
    }

    public function getTotalPendingQuantityAttribute(): float
    {
        return max(
            0,
            $this->total_required_quantity
                - $this->total_fulfilled_quantity
        );
    }

    public function getFulfilmentPercentageAttribute(): float
    {
        $required = $this->total_required_quantity;

        if ($required <= 0) {
            return 0;
        }

        return min(
            100,
            round(
                ($this->total_fulfilled_quantity / $required)
                * 100,
                2
            )
        );
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

    public function getIsDprLinkedAttribute(): bool
    {
        return ! empty($this->dpr_id);
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'Draft';
    }

    public function getIsSubmittedAttribute(): bool
    {
        return $this->status === 'Submitted';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'Approved';
    }
}
