<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMeasurementZone extends Model
{
    protected $fillable = ['project_room_id','project_room_wall_id','spatial_measurement_zone_id','name','measurement_basis','unit','length','width','height','quantity','calculated_value','manual_value','use_manual_value','sort_order','is_active','remarks'];

    protected $casts=['length'=>'decimal:3','width'=>'decimal:3','height'=>'decimal:3','quantity'=>'decimal:3','calculated_value'=>'decimal:3','manual_value'=>'decimal:3','use_manual_value'=>'boolean','is_active'=>'boolean'];
public function room(){ return $this->belongsTo(ProjectRoom::class,'project_room_id'); }
public function wall(){ return $this->belongsTo(ProjectRoomWall::class,'project_room_wall_id'); }
public function zoneType(){ return $this->belongsTo(SpatialMeasurementZone::class,'spatial_measurement_zone_id'); }
}
