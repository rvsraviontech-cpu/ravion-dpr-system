<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialReceived extends Model
{
    protected $fillable = [
        'dpr_id',
        'project_id',
        'user_id',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'storage_location',
        'material_category',
        'material_name',
        'specification',
        'brand',
        'quantity_received',
        'unit',
        'vendor_name',
        'supplied_by_contractor',
        'contractor_id',
        'vehicle_number',
        'driver_name',
        'challan_number',
        'bill_number',
        'received_date',
        'received_time',
        'material_condition',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',
        'site_engineer_verification_status',
        'pmo_verification_status',
        'accountant_verification_status',
        'status',
        'remarks',
        'material_category_id',
        'material_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function engineer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function block()
    {
        return $this->belongsTo(ProjectBlock::class, 'project_block_id');
    }

    public function floor()
    {
        return $this->belongsTo(ProjectFloor::class, 'project_floor_id');
    }

    public function unit()
    {
        return $this->belongsTo(ProjectUnit::class, 'project_unit_id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }

public function materialCategory()
{
    return $this->belongsTo(MaterialCategory::class, 'material_category_id');
}

public function material()
{
    return $this->belongsTo(Material::class, 'material_id');
}
}