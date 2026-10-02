<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkActivityAlias extends Model
{
    protected $fillable = [
        'work_activity_id',
        'alias',
        'normalized_alias',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'work_activity_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            WorkActivity::class,
            'work_activity_id'
        );
    }
}