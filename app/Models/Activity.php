<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'activity_division_id',
        'activity_name',
        'unit',
        'work_stage',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function division()
    {
        return $this->belongsTo(ActivityDivision::class, 'activity_division_id');
    }

    public function mapping()
    {
        return $this->hasOne(ActivityMapping::class);
    }
}