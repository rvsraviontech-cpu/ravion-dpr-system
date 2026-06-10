<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialConsumed extends Model
{
    protected $fillable = [
        'dpr_id',
        'project_id',
        'user_id',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',
        'activity_division_id',
        'activity_id',
        'activity_mapping_id',
        'material_category_id',
        'material_id',
        'contractor_id',
        'quantity_consumed',
        'unit',
        'related_work_output_quantity',
        'wastage_quantity',
        'wastage_reason',
        'consumed_date',
        'consumed_time',
        'status',
        'remarks',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function engineer() { return $this->belongsTo(User::class, 'user_id'); }
    public function block() { return $this->belongsTo(ProjectBlock::class, 'project_block_id'); }
    public function floor() { return $this->belongsTo(ProjectFloor::class, 'project_floor_id'); }
    public function unit() { return $this->belongsTo(ProjectUnit::class, 'project_unit_id'); }
    public function room() { return $this->belongsTo(ProjectRoom::class, 'project_room_id'); }
    public function subspace() { return $this->belongsTo(ProjectSubspace::class, 'project_subspace_id'); }
    public function activityDivision() { return $this->belongsTo(ActivityDivision::class, 'activity_division_id'); }
    public function activity() { return $this->belongsTo(Activity::class); }
    public function activityMapping() { return $this->belongsTo(ActivityMapping::class); }
    public function materialCategory() { return $this->belongsTo(MaterialCategory::class, 'material_category_id'); }
    public function material() { return $this->belongsTo(Material::class, 'material_id'); }
    public function contractor() { return $this->belongsTo(Contractor::class); }
    public function dpr() { return $this->belongsTo(Dpr::class); }
}