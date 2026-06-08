<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationRoomMaster extends Model
{
    protected $fillable = [
        'name',
        'room_type',
        'is_active',
        'remarks',
    ];
}