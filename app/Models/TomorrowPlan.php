<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TomorrowPlan extends Model
{
    protected $fillable = [
        'dpr_id',
        'project_id',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',
        'activity_id',
        'contractor_id',
        'planned_quantity',
        'unit',
        'planned_labour',
        'required_skilled_labour',
        'required_semiskilled_labour',
        'required_helpers',
        'materials_required',
        'machinery_required',
        'drawing_required',
        'client_approval_required',
        'responsible_person',
        'planned_date',
        'priority',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'risks_constraints',
        'remarks',
    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function block()
    {
        return $this->belongsTo(
            ProjectBlock::class,
            'project_block_id'
        );
    }

    public function floor()
    {
        return $this->belongsTo(
            ProjectFloor::class,
            'project_floor_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Project Unit
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | This relationship must NOT be named "unit" because tomorrow_plans also
    | contains a database column named "unit" for values such as Nos, Sqft,
    | Cum, etc.
    |
    */

    public function projectUnit()
    {
        return $this->belongsTo(
            ProjectUnit::class,
            'project_unit_id'
        );
    }

    public function room()
    {
        return $this->belongsTo(
            ProjectRoom::class,
            'project_room_id'
        );
    }

    public function subspace()
    {
        return $this->belongsTo(
            ProjectSubspace::class,
            'project_subspace_id'
        );
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}