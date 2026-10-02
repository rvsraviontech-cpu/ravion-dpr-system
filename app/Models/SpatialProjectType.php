<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialProjectType extends Model
{
    protected $fillable = ['spatial_project_category_id','code','name','description','aliases','sort_order','is_system','is_active'];

    protected $casts=['aliases'=>'array','is_system'=>'boolean','is_active'=>'boolean'];
public function category(){ return $this->belongsTo(SpatialProjectCategory::class,'spatial_project_category_id'); }
}
