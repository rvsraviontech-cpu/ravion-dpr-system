<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialOpeningType extends Model
{
    protected $fillable = ['code','name','opening_group','description','aliases','can_connect_spaces','deduct_from_wall_area','sort_order','is_system','is_active'];

    protected $casts=['aliases'=>'array','can_connect_spaces'=>'boolean','deduct_from_wall_area'=>'boolean','is_system'=>'boolean','is_active'=>'boolean'];
public function mappings(){ return $this->hasMany(SpatialSpaceComponentMapping::class); }
public function projectOpenings(){ return $this->hasMany(ProjectWallOpening::class); }
}
