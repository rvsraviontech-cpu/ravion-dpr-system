<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteIssue extends Model
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
        'issue_date',
        'issue_type',
        'title',
        'related_activity',
        'description',
        'root_cause',
        'responsible_person',
        'target_closure_date',
        'actual_closure_date',
        'priority',
        'status',
        'escalated_to_pmo',
        'escalated_to_management',
        'resolution',
        'created_by',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}