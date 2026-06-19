<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [

        'vendor_code',

        'material_category_id',

        'vendor_name',

        'contact_person',

        'mobile',

        'alternate_mobile',

        'email',

        'address',

        'city',

        'state',

        'pincode',

        'gst_number',

        'pan_number',

        'payment_terms',

        'credit_days',

        'remarks',

        'is_active',

    ];

    public function category()
    {
        return $this->belongsTo(
            MaterialCategory::class,
            'material_category_id'
        );
    }
}