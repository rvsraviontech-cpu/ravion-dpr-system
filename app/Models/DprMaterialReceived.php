<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DprMaterialReceived extends Model
{
    protected $fillable = [

        'dpr_id',

        'material_id',

        'vendor_id',

        'quantity_received',

        'challan_number',

        'bill_number',

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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}