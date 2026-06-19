<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourReport extends Model
{
    protected $fillable = [
        'dpr_id',
        'project_id',
        'user_id',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',
        'activity_id',
        'activity_mapping_id',
        'contractor_id',
        'skilled_count',
        'semi_skilled_count',
        'helper_count',
        'semi_helper_count',
        'supervisor_count',
        'technician_count',
        'machine_operator_count',
        'male_count',
        'female_count',
        'local_count',
        'non_local_count',
        'total_labour',
        'shift',
        'work_output',
        'work_output_unit',
        'entry_date',
        'entry_time',
        'status',
        'remarks',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function block()
    {
        return $this->belongsTo(ProjectBlock::class, 'project_block_id');
    }

    public function floor()
    {
        return $this->belongsTo(ProjectFloor::class, 'project_floor_id');
    }

    public function unit()
    {
        return $this->belongsTo(ProjectUnit::class, 'project_unit_id');
    }

    public function room()
    {
        return $this->belongsTo(ProjectRoom::class, 'project_room_id');
    }

    public function subspace()
    {
        return $this->belongsTo(ProjectSubspace::class, 'project_subspace_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function activityMapping()
    {
        return $this->belongsTo(ActivityMapping::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }
    public function details()
{
    return $this->hasMany(\App\Models\LabourReportDetail::class);
}
}