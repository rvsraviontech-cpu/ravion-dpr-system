<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DprPhoto extends Model
{
    protected $fillable = [
        'dpr_id',
        'photo_path'
    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }
}