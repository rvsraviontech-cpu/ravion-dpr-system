<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourCategory extends Model
{
    protected $fillable = [
        'category_name',
        'is_active',
        'remarks',
    ];

    public function labourTypes()
    {
        return $this->hasMany(LabourType::class);
    }
}