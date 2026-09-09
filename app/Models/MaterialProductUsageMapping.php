<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialProductUsageMapping extends Model
{
    protected $fillable=['material_type_id','material_variant_id','activity_division_id','activity_id','construction_work_package_id','usage_type','is_primary','sort_order','is_active','source_code','source_name','source','remarks'];
    protected $casts=['material_type_id'=>'integer','material_variant_id'=>'integer','activity_division_id'=>'integer','activity_id'=>'integer','construction_work_package_id'=>'integer','is_primary'=>'boolean','sort_order'=>'integer','is_active'=>'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(MaterialType::class,'material_type_id'); }
    public function materialType(): BelongsTo { return $this->product(); }
    public function variant(): BelongsTo { return $this->belongsTo(MaterialVariant::class,'material_variant_id'); }
    public function activityDivision(): BelongsTo { return $this->belongsTo(ActivityDivision::class,'activity_division_id'); }
    public function activity(): BelongsTo { return $this->belongsTo(Activity::class,'activity_id'); }
    public function workPackage(): BelongsTo { return $this->belongsTo(ConstructionWorkPackage::class,'construction_work_package_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active',true); }
    public function scopePrimary(Builder $query): Builder { return $query->where('is_primary',true); }
    public function scopeOrdered(Builder $query): Builder { return $query->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id'); }
}
