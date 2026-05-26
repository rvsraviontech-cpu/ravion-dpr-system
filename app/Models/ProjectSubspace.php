<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSubspace extends Model
{
    protected $fillable = [
        'project_id',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'name',
        'type',
        'is_active',
        'remarks',
    ];

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
}