<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRoomWall extends Model
{
    protected $fillable = ['project_room_id','spatial_component_type_id','wall_code','name','orientation','wall_function','dimension_unit','area_unit','length','height','gross_area','openings_area','net_area','is_external','is_shared','sort_order','is_active','remarks'];

    protected $casts=['length'=>'decimal:3','height'=>'decimal:3','gross_area'=>'decimal:3','openings_area'=>'decimal:3','net_area'=>'decimal:3','is_external'=>'boolean','is_shared'=>'boolean','is_active'=>'boolean'];
public function room(){ return $this->belongsTo(ProjectRoom::class,'project_room_id'); }
public function componentType(){ return $this->belongsTo(SpatialComponentType::class,'spatial_component_type_id'); }
public function openings(){ return $this->hasMany(ProjectWallOpening::class,'project_room_wall_id'); }
public function measurementZones(){ return $this->hasMany(ProjectMeasurementZone::class,'project_room_wall_id'); }
}
