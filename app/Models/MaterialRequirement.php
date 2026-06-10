<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequirement extends Model
{
    protected $fillable = [
        'project_id',
        'project_block_id',
        'material_category_id',
        'material_id',
        'required_quantity',
        'unit',
        'required_date',
        'priority',
        'status',
        'remarks',
        'created_by',
        'approved_by',
        'approved_at',
        'fulfilled_quantity'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function block()
    {
        return $this->belongsTo(ProjectBlock::class, 'project_block_id');
    }

    public function materialCategory()
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}