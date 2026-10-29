<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqInvitationItem extends Model
{
    protected $table = 'rfq_invitation_items';
    protected $fillable = ['rfq_vendor_invitation_id', 'rfq_item_id'];

}
