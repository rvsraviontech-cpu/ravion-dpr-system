<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFloor extends Model
{
    protected $fillable = [
        'project_id', 'project_block_id', 'name', 'sequence', 'usage_type',
        'spatial_level_type_id', 'spatial_floor_usage_id', 'level_identifier',
        'is_active', 'remarks',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function block() { return $this->belongsTo(ProjectBlock::class, 'project_block_id'); }
    public function units() { return $this->hasMany(ProjectUnit::class, 'project_floor_id'); }
    public function rooms() { return $this->hasMany(ProjectRoom::class, 'project_floor_id'); }
    public function subspaces() { return $this->hasMany(ProjectSubspace::class, 'project_floor_id'); }
    public function spatialLevelType() { return $this->belongsTo(SpatialLevelType::class); }
    public function spatialFloorUsage() { return $this->belongsTo(SpatialFloorUsage::class); }
}
