<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourType extends Model
{
    protected $fillable = [
        'labour_category_id',
        'labour_type_name',
        'status',
    ];

    public function labourCategory()
    {
        return $this->belongsTo(
            LabourCategory::class,
            'labour_category_id'
        );
    }
}