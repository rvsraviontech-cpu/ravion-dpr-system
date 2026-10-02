<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpatialLevelType extends Model
{
    protected $fillable = [
        'code', 'name', 'name_pattern', 'requires_number', 'number_mode',
        'description', 'aliases', 'sort_order', 'is_system', 'is_active',
    ];

    protected $casts = [
        'aliases' => 'array',
        'requires_number' => 'boolean',
        'sort_order' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];
}
