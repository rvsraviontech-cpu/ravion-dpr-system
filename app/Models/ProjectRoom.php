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
        'spatial_space_type_id',
        'spatial_space_subtype_id',
        'identifier',
        'name',
        'code',
        'area_sqft',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'area_sqft' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function project(){ return $this->belongsTo(Project::class); }
    public function block(){ return $this->belongsTo(ProjectBlock::class, 'project_block_id'); }
    public function floor(){ return $this->belongsTo(ProjectFloor::class, 'project_floor_id'); }
    public function unit(){ return $this->belongsTo(ProjectUnit::class, 'project_unit_id'); }
    public function subspaces(){ return $this->hasMany(ProjectSubspace::class, 'project_room_id'); }

    public function spatialSpaceType(){ return $this->belongsTo(SpatialSpaceType::class, 'spatial_space_type_id'); }
    public function spatialSpaceSubtype(){ return $this->belongsTo(SpatialSpaceSubtype::class, 'spatial_space_subtype_id'); }

    public function geometry(){ return $this->hasOne(ProjectRoomGeometry::class, 'project_room_id'); }
    public function walls(){ return $this->hasMany(ProjectRoomWall::class, 'project_room_id'); }
    public function measurementZones(){ return $this->hasMany(ProjectMeasurementZone::class, 'project_room_id'); }
    public function incomingOpenings(){ return $this->hasMany(ProjectWallOpening::class, 'connects_to_project_room_id'); }
}
