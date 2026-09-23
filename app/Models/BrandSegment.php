<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrandSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'segment_code',
        'segment_name',
        'sort_order',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Brand Masters belonging to this segment.
     *
     * This relationship will become active after brand_segment_id
     * is added to brand_masters.
     */
    public function brands(): HasMany
    {
        return $this->hasMany(BrandMaster::class, 'brand_segment_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('segment_name');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->segment_name;
    }
}