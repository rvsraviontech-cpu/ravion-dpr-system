<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialReceivedItem extends Model
{
    protected $fillable = [
        'material_received_id',

        // Legacy / execution classification
        'activity_division_id',
        'activity_id',

        // Material Received V3 classification
        'construction_work_package_id',
        'pending_material_classification_id',

        // Material variant identity
        'material_type_id',
        'brand_master_id',
        'material_specification_id',
        'material_grade_id',

        // Quantity / unit
        'quantity_received',
        'unit_master_id',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',
        'material_condition',

        // Receipt usage / notes
'purpose_used_for',
'sort_order',
'remarks',
    ];

    protected $casts = [
        'material_received_id' => 'integer',
        'activity_division_id' => 'integer',
        'activity_id' => 'integer',
        'construction_work_package_id' => 'integer',
        'pending_material_classification_id' => 'integer',
        'material_type_id' => 'integer',
        'brand_master_id' => 'integer',
        'material_specification_id' => 'integer',
        'material_grade_id' => 'integer',
        'unit_master_id' => 'integer',

        'quantity_received' => 'decimal:3',
        'accepted_quantity' => 'decimal:3',
        'short_quantity' => 'decimal:3',
        'damaged_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',

        'sort_order' => 'integer',
    ];

    public function materialReceived(): BelongsTo
    {
        return $this->belongsTo(MaterialReceived::class, 'material_received_id');
    }

    public function workPackage(): BelongsTo
    {
        return $this->belongsTo(
            ConstructionWorkPackage::class,
            'construction_work_package_id'
        );
    }

    public function pendingClassification(): BelongsTo
    {
        return $this->belongsTo(
            PendingMaterialClassification::class,
            'pending_material_classification_id'
        );
    }

    public function activityDivision(): BelongsTo
    {
        return $this->belongsTo(ActivityDivision::class, 'activity_division_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class, 'material_type_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandMaster::class, 'brand_master_id');
    }

    public function specification(): BelongsTo
    {
        return $this->belongsTo(
            MaterialSpecification::class,
            'material_specification_id'
        );
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(MaterialGrade::class, 'material_grade_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitMaster::class, 'unit_master_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(
            MaterialReceivedPhoto::class,
            'material_received_item_id'
        )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->pendingClassification) {
            return $this->pendingClassification->display_name;
        }

        return collect([
            $this->brand?->brand_name,
            $this->specification?->specification_name,
            $this->grade?->grade_name,
            $this->materialType?->material_type_name,
        ])->filter()->implode(' ');
    }

    public function getWorkPackageDisplayNameAttribute(): ?string
    {
        $package = $this->workPackage
            ?? $this->pendingClassification?->suggestedWorkPackage;

        if (! $package) {
            return null;
        }

        return trim($package->code . ' — ' . $package->name);
    }

    public function getIsPendingClassificationAttribute(): bool
    {
        return ! empty($this->pending_material_classification_id)
            && $this->pendingClassification?->status === 'Pending';
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
