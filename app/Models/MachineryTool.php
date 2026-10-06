<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineryTool extends Model
{
    public const CATEGORIES = [
        'Earthmoving & Excavation',
        'Loading & Material Handling',
        'Transportation & Haulage',
        'Lifting & Hoisting',
        'Concrete Production & Placement',
        'Concrete Compaction',
        'Rebar Processing',
        'Masonry & Plastering Equipment',
        'Compaction Equipment',
        'Road & External Development',
        'Piling & Foundation Equipment',
        'Dewatering & Pumping',
        'Power Generation',
        'Electrical Equipment',
        'Welding & Cutting',
        'Air Compressors & Pneumatic Equipment',
        'Scaffolding & Access Equipment',
        'Demolition Equipment',
        'Drilling & Coring Equipment',
        'Carpentry & Woodworking Equipment',
        'Flooring & Finishing Equipment',
        'Painting Equipment',
        'Waterproofing Equipment',
        'Plumbing Equipment',
        'HVAC Installation Equipment',
        'Fire Fighting Installation Equipment',
        'Surveying & Engineering Equipment',
        'Testing & Quality Equipment',
        'Material Handling Tools',
        'Power Tools',
        'Cleaning Equipment',
        'Safety & Rescue Equipment',
        'Temporary Site Equipment',
        'Specialized Building Equipment',
        'Other Machinery & Equipment',
    ];

    protected $fillable = [
        'code',
        'machine_name',
        'category',
        'description',

        // Legacy field retained for backward compatibility.
        'ownership_type',

        'unit',
        'tracking_mode',
        'meter_type',
        'fuel_type',
        'requires_operator',
        'requires_meter_reading',
        'requires_fuel_tracking',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_operator' => 'boolean',
        'requires_meter_reading' => 'boolean',
        'requires_fuel_tracking' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function dprMachineryTools(): HasMany
    {
        return $this->hasMany(DprMachineryTool::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('category')
            ->orderBy('machine_name');
    }
}