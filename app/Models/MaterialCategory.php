<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialCategory extends Model
{
    protected $fillable = [
        'category_name',
        'category_code',
        'is_active',
        'remarks',
    ];
}