<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialMeasurementZone extends Model
{
    protected $fillable = ['code','name','zone_group','measurement_basis','default_unit','description','aliases','sort_order','is_system','is_active'];

    protected $casts=['aliases'=>'array','is_system'=>'boolean','is_active'=>'boolean'];
public function mappings(){ return $this->hasMany(SpatialSpaceComponentMapping::class); }
public function projectZones(){ return $this->hasMany(ProjectMeasurementZone::class); }
}
