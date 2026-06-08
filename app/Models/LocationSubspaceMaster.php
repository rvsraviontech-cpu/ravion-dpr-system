<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationSubspaceMaster extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
        'remarks',
    ];
}