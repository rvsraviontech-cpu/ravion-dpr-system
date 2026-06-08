<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationFloorMaster extends Model
{
    protected $fillable = [
        'name',
        'sequence',
        'is_active',
        'remarks',
    ];
}