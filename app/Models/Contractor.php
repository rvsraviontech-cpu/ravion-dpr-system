<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $fillable = [
        'contractor_name',
        'mobile',
        'work_category',
        'status'
    ];
}