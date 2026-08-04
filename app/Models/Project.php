<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    protected $fillable = [
        'project_code',
        'project_name',
        'division_code',
        'client_name',
        'client_mobile',
        'client_email',
        'client_address',
        'location',
        'google_map_link',
        'project_type',
        'structure_type',
        'contract_value',
        'assigned_pmo_id',
        'start_date',
        'target_completion_date',
        'status',
        'odoo_analytic_account_code',
        'remarks',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_completion_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | User Relationships
    |--------------------------------------------------------------------------
    */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function assignedPmo(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_pmo_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DPR Relationships
    |--------------------------------------------------------------------------
    */

    public function dprs(): HasMany
    {
        return $this->hasMany(Dpr::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Project Location Relationships
    |--------------------------------------------------------------------------
    */

    public function blocks(): HasMany
    {
        return $this->hasMany(ProjectBlock::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(ProjectFloor::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProjectUnit::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(ProjectRoom::class);
    }

    public function subspaces(): HasMany
    {
        return $this->hasMany(ProjectSubspace::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Labour Relationships
    |--------------------------------------------------------------------------
    */

    public function currentLabours(): HasMany
    {
        return $this->hasMany(
            Labour::class,
            'current_project_id'
        );
    }

    public function labourAttendances(): HasMany
    {
        return $this->hasMany(
            LabourAttendance::class
        );
    }

    /**
 * Scope: projects currently available for operational use.
 */
public function scopeActive(Builder $query): Builder
{
    return $query->where('status', 'Active');
}

    
}