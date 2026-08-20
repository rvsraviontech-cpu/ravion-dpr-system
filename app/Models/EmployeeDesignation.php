<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeDesignation extends Model
{
    protected $fillable = [
        'code',
        'name',
        'department_id',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(
            User::class,
            'employee_designation_id'
        );
    }
}