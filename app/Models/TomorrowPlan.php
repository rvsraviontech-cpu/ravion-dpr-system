<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TomorrowPlan extends Model
{
    protected $fillable = [

        'dpr_id',

        'activity_id',

        'planned_quantity',

        'unit',

        'planned_labour',

        'materials_required',

        'machinery_required',

        'risks_constraints',

        'remarks'

    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}