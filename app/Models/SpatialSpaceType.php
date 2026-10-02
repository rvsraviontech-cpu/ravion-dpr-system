<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialSpaceType extends Model
{
    protected $fillable = ['spatial_space_category_id','code','name','description','aliases','default_location_level','allows_children','allows_geometry','sort_order','is_system','is_active'];

    protected $casts=['aliases'=>'array','allows_children'=>'boolean','allows_geometry'=>'boolean','is_system'=>'boolean','is_active'=>'boolean'];
public function category(){ return $this->belongsTo(SpatialSpaceCategory::class,'spatial_space_category_id'); }
public function subtypes(){ return $this->hasMany(SpatialSpaceSubtype::class); }
public function componentMappings(){ return $this->hasMany(SpatialSpaceComponentMapping::class); }
public function geometries(){ return $this->hasMany(ProjectRoomGeometry::class); }
}
