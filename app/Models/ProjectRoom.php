<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRoom extends Model
{
    protected $fillable = [

        'project_id',

        'project_block_id',

        'project_floor_id',

        'project_unit_id',

        'room_type',

        'name',

        'code',

        'area_sqft',

        'is_active',

        'remarks'

    ];

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

    public function unit()
    {
        return $this->belongsTo(
            ProjectUnit::class,
            'project_unit_id'
        );
    }
    public function subspaces()
{
    return $this->hasMany(ProjectSubspace::class, 'project_room_id');
}
}