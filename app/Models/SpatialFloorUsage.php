<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialFloorUsage extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'aliases',
        'sort_order', 'is_system', 'is_active',
    ];

    protected $casts = [
        'aliases' => 'array',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];
}
