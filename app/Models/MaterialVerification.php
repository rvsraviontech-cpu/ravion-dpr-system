<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialVerification extends Model
{
    protected $fillable = [

        'material_received_id',

        'project_id',
        'project_block_id',

        'material_category_id',
        'material_id',

        'received_quantity',
        'accepted_quantity',
        'short_quantity',
        'damaged_quantity',
        'rejected_quantity',

        'unit',

        'verification_status',

        'verified_by',
        'verified_at',

        'verification_remarks'
    ];

    public function materialReceived()
    {
        return $this->belongsTo(MaterialReceived::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}