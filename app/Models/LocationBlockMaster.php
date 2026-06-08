<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationBlockMaster extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
        'remarks',
    ];
}