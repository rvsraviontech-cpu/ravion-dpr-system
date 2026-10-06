<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineryEquipment extends Model
{
    public const OWNERSHIP_TYPES = [
        'company_owned' => 'Company Owned',
        'rented' => 'Rented',
        'contractor_provided' => 'Contractor Provided',
    ];

    public const STATUSES = [
        'available' => 'Available',
        'allocated' => 'Allocated',
        'working' => 'Working',
        'idle' => 'Idle',
        'breakdown' => 'Breakdown',
        'maintenance' => 'Maintenance',
        'returned' => 'Returned / Off-Hire',
        'inactive' => 'Inactive',
    ];

    protected $table = 'machinery_equipment';

    protected $fillable = [
        'machinery_tool_id',
        'equipment_code',
        'asset_number',
        'equipment_name',
        'tracking_mode',
        'quantity',
        'unit',
        'ownership_type',
        'vendor_id',
        'contractor_id',
        'make',
        'model',
        'serial_number',
        'registration_number',
        'manufacture_year',
        'capacity',
        'capacity_unit',
        'meter_type',
        'fuel_type',
        'current_meter_reading',
        'purchase_date',
        'purchase_reference',
        'purchase_value',
        'current_project_id',
        'status',
        'commissioned_date',
        'decommissioned_date',
        'remarks',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'capacity' => 'decimal:3',
        'current_meter_reading' => 'decimal:2',
        'purchase_value' => 'decimal:2',
        'purchase_date' => 'date',
        'commissioned_date' => 'date',
        'decommissioned_date' => 'date',
        'manufacture_year' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function machineryTool(): BelongsTo
    {
        return $this->belongsTo(MachineryTool::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function currentProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'current_project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeIndividual(Builder $query): Builder
    {
        return $query->where('tracking_mode', 'individual');
    }

    public function scopePooled(Builder $query): Builder
    {
        return $query->where('tracking_mode', 'pooled');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getOwnershipLabelAttribute(): string
    {
        return self::OWNERSHIP_TYPES[$this->ownership_type]
            ?? ucwords(str_replace('_', ' ', $this->ownership_type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]
            ?? ucwords(str_replace('_', ' ', $this->status));
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->equipment_name) {
            return $this->equipment_name;
        }

        return $this->machineryTool?->machine_name ?? $this->equipment_code;
    }

    public function isCompanyOwned(): bool
    {
        return $this->ownership_type === 'company_owned';
    }

    public function isRented(): bool
    {
        return $this->ownership_type === 'rented';
    }

    public function isContractorProvided(): bool
    {
        return $this->ownership_type === 'contractor_provided';
    }

    public function isPooled(): bool
    {
        return $this->tracking_mode === 'pooled';
    }
    public function allocations(): HasMany
{
    return $this->hasMany(
        MachineryEquipmentAllocation::class,
        'machinery_equipment_id'
    );
}

public function dailyUsages()
{
    return $this->hasMany(
        MachineryDailyUsage::class,
        'machinery_equipment_id'
    );
}
}