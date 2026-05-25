<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteIssue extends Model
{
    protected $fillable = [

        'dpr_id',

        'issue_type',

        'related_activity',

        'description',

        'responsible_person',

        'priority',

        'status',

        'remarks'

    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }
}