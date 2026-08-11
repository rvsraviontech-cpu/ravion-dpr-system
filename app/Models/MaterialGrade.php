<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialGrade extends Model
{
    protected $fillable = [
        'material_type_id',
        'grade_name',
        'grade_code',
        'sequence',
        'is_active',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'is_active' => 'boolean',
    ];

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

    public function getDisplayNameAttribute(): string
    {
        return $this->grade_name;
    }
}