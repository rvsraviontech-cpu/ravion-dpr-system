<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkDoneItemLabour extends Model
{
    protected $fillable = [
        'work_done_item_id',
        'designation_role_id',
        'quantity',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function workDoneItem(): BelongsTo
    {
        return $this->belongsTo(
            WorkDoneItem::class,
            'work_done_item_id'
        );
    }

    public function designationRole(): BelongsTo
    {
        return $this->belongsTo(
            DesignationRole::class,
            'designation_role_id'
        );
    }

    public function getDesignationNameAttribute(): string
    {
        return $this->designationRole?->name
            ?? 'Unknown Designation';
    }
}