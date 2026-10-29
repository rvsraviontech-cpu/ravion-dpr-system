<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqQuotationItem extends Model
{
    protected $table = 'rfq_quotation_items';
    protected $fillable = ['rfq_quotation_id', 'rfq_item_id', 'offered_quantity', 'rate', 'discount_amount', 'tax_percent', 'product_amount', 'tax_amount', 'line_total', 'remarks'];

}
