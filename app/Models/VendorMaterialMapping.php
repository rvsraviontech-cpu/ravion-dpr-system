<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorMaterialMapping extends Model
{
    protected $table = 'vendor_material_mappings';
    protected $fillable = ['vendor_id', 'material_type_id', 'is_active'];

}
