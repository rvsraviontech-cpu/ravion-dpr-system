<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqVendorInvitation extends Model
{
    protected $table = 'rfq_vendor_invitations';
    protected $fillable = ['rfq_id', 'vendor_id', 'token_hash', 'status', 'expires_at', 'sent_at', 'viewed_at', 'submitted_at', 'revoked_at'];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RfqInvitationItem::class, 'rfq_vendor_invitation_id');
    }

    public function quotations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RfqQuotation::class, 'rfq_vendor_invitation_id');
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

}
