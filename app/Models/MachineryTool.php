<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineryTool extends Model
{
    protected $fillable = [

        'machine_name',

        'ownership_type',

        'unit'

    ];
}