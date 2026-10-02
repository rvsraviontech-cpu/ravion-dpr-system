<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectWallOpening extends Model
{
    protected $fillable = ['project_room_wall_id','spatial_opening_type_id','opening_code','name','opening_group','dimension_unit','area_unit','width','height','sill_height','lintel_height','quantity','calculated_area','connects_to_project_room_id','connects_to_project_subspace_id','material_type','operation_type','deduct_from_wall_area','sort_order','is_active','remarks'];

    protected $casts=['width'=>'decimal:3','height'=>'decimal:3','sill_height'=>'decimal:3','lintel_height'=>'decimal:3','quantity'=>'integer','calculated_area'=>'decimal:3','deduct_from_wall_area'=>'boolean','is_active'=>'boolean'];
public function wall(){ return $this->belongsTo(ProjectRoomWall::class,'project_room_wall_id'); }
public function openingType(){ return $this->belongsTo(SpatialOpeningType::class,'spatial_opening_type_id'); }
public function connectedRoom(){ return $this->belongsTo(ProjectRoom::class,'connects_to_project_room_id'); }
public function connectedSubspace(){ return $this->belongsTo(ProjectSubspace::class,'connects_to_project_subspace_id'); }
}
