<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [

        'vendor_name',

        'contact_person',

        'mobile',

        'email',

        'address'

    ];
}