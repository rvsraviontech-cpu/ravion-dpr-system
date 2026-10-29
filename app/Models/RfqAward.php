<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqAward extends Model
{
    protected $table = 'rfq_awards';
    protected $fillable = ['rfq_item_id', 'rfq_quotation_item_id', 'vendor_id', 'awarded_quantity', 'agreed_rate', 'purchase_order_item_id', 'awarded_by', 'awarded_at', 'remarks'];

}
