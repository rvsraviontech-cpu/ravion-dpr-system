<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkStage extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sequence',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}