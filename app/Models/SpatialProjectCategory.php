<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialProjectCategory extends Model
{
    protected $fillable = ['code','name','description','aliases','sort_order','is_system','is_active'];

    protected $casts=['aliases'=>'array','is_system'=>'boolean','is_active'=>'boolean'];
public function projectTypes(){ return $this->hasMany(SpatialProjectType::class); }
public function spaceCategories(){ return $this->hasMany(SpatialSpaceCategory::class); }
}
