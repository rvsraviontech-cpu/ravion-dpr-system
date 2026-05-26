<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectRoom;

class ProjectUnit extends Model
{
    protected $fillable = [
        'project_id',
        'project_block_id',
        'project_floor_id',
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
    public function rooms()
{
    return $this->hasMany(
        ProjectRoom::class,
        'project_unit_id'
    );
}
}