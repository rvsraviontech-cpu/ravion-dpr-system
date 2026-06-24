<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function assignedPmo()
    {
        return $this->belongsTo(User::class, 'assigned_pmo_id');
    }

    public function dprs()
    {
        return $this->hasMany(Dpr::class);
    }

    public function blocks()
    {
        return $this->hasMany(ProjectBlock::class);
    }

    public function floors()
    {
        return $this->hasMany(ProjectFloor::class);
    }

    public function units()
    {
        return $this->hasMany(ProjectUnit::class);
    }

    public function rooms()
    {
        return $this->hasMany(ProjectRoom::class);
    }

    public function subspaces()
    {
        return $this->hasMany(ProjectSubspace::class);
    }
}