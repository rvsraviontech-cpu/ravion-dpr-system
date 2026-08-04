<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyWageSheetCharge extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'weekly_wage_sheet_id',

        'charge_type',
        'description',
        'amount',

        'activity_id',
        'contractor_id',

        'remarks',
        'sort_order',

        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function weeklyWageSheet(): BelongsTo
    {
        return $this->belongsTo(
            WeeklyWageSheet::class
        );
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class
        );
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(
            Contractor::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getDisplayLabelAttribute(): string
    {
        if ($this->description) {
            return "{$this->charge_type} - {$this->description}";
        }

        return $this->charge_type;
    }
}