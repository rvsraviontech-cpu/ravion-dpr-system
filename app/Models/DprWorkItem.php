<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DprWorkItem extends Model
{
    protected $fillable = [
        'dpr_id',
        'activity_id',
        'contractor_id',
        'quantity_completed',
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

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
}