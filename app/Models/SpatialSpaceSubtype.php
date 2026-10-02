<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialSpaceSubtype extends Model
{
    protected $fillable = ['spatial_space_type_id','code','name','description','aliases','sort_order','is_system','is_active'];

    protected $casts=['aliases'=>'array','is_system'=>'boolean','is_active'=>'boolean'];
public function spaceType(){ return $this->belongsTo(SpatialSpaceType::class,'spatial_space_type_id'); }
public function componentMappings(){ return $this->hasMany(SpatialSpaceComponentMapping::class); }
}
