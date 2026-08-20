<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function designations(): HasMany
    {
        return $this->hasMany(EmployeeDesignation::class)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}