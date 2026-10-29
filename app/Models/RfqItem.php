<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqItem extends Model
{
    protected $table = 'rfq_items';
    protected $fillable = ['rfq_id', 'material_requirement_item_id', 'material_type_id', 'material_variant_id', 'brand_master_id', 'material_specification_id', 'material_grade_id', 'unit_master_id', 'product_name', 'specification_text', 'requested_quantity', 'sort_order', 'remarks'];

}
