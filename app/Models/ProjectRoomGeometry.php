<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRoomGeometry extends Model
{
    protected $fillable = ['project_room_id','spatial_space_type_id','spatial_space_subtype_id','shape_type','dimension_unit','area_unit','length','width','clear_height','calculated_floor_area','manual_floor_area','use_manual_floor_area','ceiling_area','perimeter','volume','geometry_notes'];

    protected $casts=['length'=>'decimal:3','width'=>'decimal:3','clear_height'=>'decimal:3','calculated_floor_area'=>'decimal:3','manual_floor_area'=>'decimal:3','use_manual_floor_area'=>'boolean','ceiling_area'=>'decimal:3','perimeter'=>'decimal:3','volume'=>'decimal:3'];
public function room(){ return $this->belongsTo(ProjectRoom::class,'project_room_id'); }
public function spaceType(){ return $this->belongsTo(SpatialSpaceType::class,'spatial_space_type_id'); }
public function spaceSubtype(){ return $this->belongsTo(SpatialSpaceSubtype::class,'spatial_space_subtype_id'); }
}
