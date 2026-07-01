<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityDivision;

class ContractorServiceCategory extends Model
{
    protected $fillable = [
    'work_stage_id',
    'activity_division_id',
    'code',
    'name',
    'is_active',
    'remarks',
];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workStage()
    {
        return $this->belongsTo(WorkStage::class);
    }


public function division()
{
    return $this->belongsTo(ActivityDivision::class, 'activity_division_id');
}
}