<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    protected $table = 'rfqs';
    protected $fillable = ['rfq_number', 'project_id', 'rfq_date', 'submission_deadline', 'status', 'delivery_address', 'delivery_contact_name', 'delivery_contact_phone', 'vendor_instructions', 'internal_remarks', 'created_by', 'approved_by', 'approved_at'];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RfqItem::class, 'rfq_id');
    }

    public function invitations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RfqVendorInvitation::class, 'rfq_id');
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

}
