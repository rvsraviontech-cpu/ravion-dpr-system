<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectBlock extends Model
{
    protected $fillable = [
        'project_id', 'name', 'code', 'type', 'spatial_block_type_id',
        'identifier', 'is_active', 'remarks',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function floors() { return $this->hasMany(ProjectFloor::class, 'project_block_id'); }
    public function spatialBlockType() { return $this->belongsTo(SpatialBlockType::class); }
}
