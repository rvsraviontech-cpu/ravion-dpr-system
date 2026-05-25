<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DprMaterialRequired extends Model
{
    protected $fillable = [

        'dpr_id',

        'material_id',

        'required_quantity',

        'required_date',

        'priority',

        'reason',

        'remarks'

    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}