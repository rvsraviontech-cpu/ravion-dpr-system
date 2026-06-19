<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitMaster extends Model
{
    protected $fillable = [
        'unit_name',
        'unit_code',
        'is_active',
        'remarks',
    ];
}