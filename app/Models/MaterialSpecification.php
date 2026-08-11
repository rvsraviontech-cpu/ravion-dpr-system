<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialSpecification extends Model
{
    protected $fillable = [
        'activity_id',       // Legacy
        'material_type_id',  // New
        'specification_code',
        'specification_name',
        'sequence',
        'is_active',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'is_active' => 'boolean',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class,
            'activity_id'
        );
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(
            MaterialType::class,
            'material_type_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}