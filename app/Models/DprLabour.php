<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DprLabour extends Model
{
    protected $fillable = [

        'dpr_id',

        'labour_type_id',

        'male_count',

        'female_count',

        'local_count',

        'non_local_count',

        'total_count'
    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }

    public function labourType()
{
    return $this->belongsTo(LabourType::class);
}
}