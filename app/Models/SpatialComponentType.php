<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialComponentType extends Model
{
    protected $fillable = ['code','name','component_group','description','aliases','supports_openings','supports_connection','is_measurable','sort_order','is_system','is_active'];

    protected $casts=['aliases'=>'array','supports_openings'=>'boolean','supports_connection'=>'boolean','is_measurable'=>'boolean','is_system'=>'boolean','is_active'=>'boolean'];
public function mappings(){ return $this->hasMany(SpatialSpaceComponentMapping::class); }
public function roomWalls(){ return $this->hasMany(ProjectRoomWall::class); }
}
