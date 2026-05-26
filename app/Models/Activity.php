<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'activity_name',
        'unit',
        'work_stage',
        'is_active'
    ];

    public function mapping()
    {
        return $this->hasOne(ActivityMapping::class);
    }
}