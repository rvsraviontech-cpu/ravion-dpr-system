<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqQuotation extends Model
{
    protected $table = 'rfq_quotations';
    protected $fillable = ['rfq_vendor_invitation_id', 'revision_number', 'status', 'valid_until', 'payment_terms', 'delivery_terms', 'vendor_remarks', 'product_amount', 'tax_amount', 'other_charges', 'net_total_amount', 'submitted_at'];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RfqQuotationItem::class, 'rfq_quotation_id');
    }

}
