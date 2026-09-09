<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingMaterialClassification extends Model
{
    protected $fillable=['temporary_material_code','source_module','source_table','source_header_id','source_item_id','material_received_id','project_id','raw_material_name','raw_brand','raw_specification','raw_grade','raw_unit','unit_master_id','suggested_work_package_id','status','mapped_material_type_id','mapped_material_variant_id','mapped_brand_master_id','mapped_unit_master_id','mapped_work_package_id','requested_by','resolved_by','resolved_at','resolution_notes','remarks'];
    protected $casts=['source_header_id'=>'integer','source_item_id'=>'integer','material_received_id'=>'integer','project_id'=>'integer','unit_master_id'=>'integer','suggested_work_package_id'=>'integer','mapped_material_type_id'=>'integer','mapped_material_variant_id'=>'integer','mapped_brand_master_id'=>'integer','mapped_unit_master_id'=>'integer','mapped_work_package_id'=>'integer','requested_by'=>'integer','resolved_by'=>'integer','resolved_at'=>'datetime'];
    public function materialReceived(): BelongsTo { return $this->belongsTo(MaterialReceived::class,'material_received_id'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function unit(): BelongsTo { return $this->belongsTo(UnitMaster::class,'unit_master_id'); }
    public function suggestedWorkPackage(): BelongsTo { return $this->belongsTo(ConstructionWorkPackage::class,'suggested_work_package_id'); }
    public function mappedMaterialType(): BelongsTo { return $this->belongsTo(MaterialType::class,'mapped_material_type_id'); }
    public function mappedProduct(): BelongsTo { return $this->mappedMaterialType(); }
    public function mappedVariant(): BelongsTo { return $this->belongsTo(MaterialVariant::class,'mapped_material_variant_id'); }
    public function mappedBrand(): BelongsTo { return $this->belongsTo(BrandMaster::class,'mapped_brand_master_id'); }
    public function mappedUnit(): BelongsTo { return $this->belongsTo(UnitMaster::class,'mapped_unit_master_id'); }
    public function mappedWorkPackage(): BelongsTo { return $this->belongsTo(ConstructionWorkPackage::class,'mapped_work_package_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class,'requested_by'); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class,'resolved_by'); }
    public function scopePending(Builder $query): Builder { return $query->where('status','Pending'); }
    public function scopeResolved(Builder $query): Builder { return $query->where('status','Resolved'); }
    public function scopeForSourceModule(Builder $query,string $sourceModule): Builder { return $query->where('source_module',$sourceModule); }
    public function getIsPendingAttribute(): bool { return $this->status === 'Pending'; }
    public function getIsResolvedAttribute(): bool { return $this->status === 'Resolved'; }
    public function getDisplayNameAttribute(): string { return collect([$this->raw_brand,$this->raw_specification,$this->raw_grade,$this->raw_material_name])->filter()->implode(' '); }
    public function getResolvedDisplayNameAttribute(): ?string { if (!$this->mappedMaterialType) return null; return collect([$this->mappedBrand?->brand_name,$this->mappedVariant?->display_name,$this->mappedMaterialType?->material_type_name])->filter()->unique()->implode(' '); }
}
