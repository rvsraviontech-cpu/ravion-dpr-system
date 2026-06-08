<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityDivision extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sequence',
        'is_active',
        'remarks',
    ];

    public function activityMappings()
    {
        return $this->hasMany(ActivityMapping::class);
    }
}