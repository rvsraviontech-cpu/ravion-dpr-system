<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialSpaceComponentMapping extends Model
{
    protected $fillable = ['spatial_space_type_id','spatial_space_subtype_id','spatial_component_type_id','spatial_opening_type_id','spatial_measurement_zone_id','context_group','is_default','is_required','sort_order','is_active'];

    protected $casts=['is_default'=>'boolean','is_required'=>'boolean','is_active'=>'boolean'];
public function spaceType(){ return $this->belongsTo(SpatialSpaceType::class,'spatial_space_type_id'); }
public function spaceSubtype(){ return $this->belongsTo(SpatialSpaceSubtype::class,'spatial_space_subtype_id'); }
public function componentType(){ return $this->belongsTo(SpatialComponentType::class,'spatial_component_type_id'); }
public function openingType(){ return $this->belongsTo(SpatialOpeningType::class,'spatial_opening_type_id'); }
public function measurementZone(){ return $this->belongsTo(SpatialMeasurementZone::class,'spatial_measurement_zone_id'); }
}
