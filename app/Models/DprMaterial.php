<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DprMaterial extends Model
{
    protected $fillable = [

        'dpr_id',

        'material_id',

        'quantity_used'

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