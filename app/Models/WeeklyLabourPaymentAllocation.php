<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyLabourPaymentAllocation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'weekly_labour_payment_detail_id',
        'project_id',
        'project_name',
        'project_code',
        'full_days',
        'half_days',
        'payable_days',
        'normal_hours',
        'ot_hours',
        'normal_wage',
        'ot_wage',
        'total_wage',
        'attendance_dates',
        'is_active',
    ];

    protected $casts = [
        'full_days' => 'decimal:2',
        'half_days' => 'decimal:2',
        'payable_days' => 'decimal:2',
        'normal_hours' => 'decimal:2',
        'ot_hours' => 'decimal:2',
        'normal_wage' => 'decimal:2',
        'ot_wage' => 'decimal:2',
        'total_wage' => 'decimal:2',
        'attendance_dates' => 'array',
        'is_active' => 'boolean',
    ];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(
            WeeklyLabourPaymentDetail::class,
            'weekly_labour_payment_detail_id'
        );
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
