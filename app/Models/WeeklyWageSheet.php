<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyWageSheet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'wage_sheet_number',

        'project_id',

        'week_start_date',
        'week_end_date',

        'status',

        'total_labours',
        'total_full_days',
        'total_half_days',
        'total_payable_days',

        'total_normal_wages',
        'total_ot_hours',
        'total_ot_wages',

        'total_labour_additions',
        'total_labour_deductions',

        'total_site_charges',

        'gross_labour_wages',
        'net_labour_wages',
        'total_project_payable',

        'generated_by',
        'generated_at',

        'submitted_by',
        'submitted_at',

        'approved_by',
        'approved_at',

        'rejected_by',
        'rejected_at',
        'rejection_reason',

        'paid_by',
        'payment_date',
        'payment_method',
        'payment_reference',
        'paid_at',

        'remarks',

        'is_active',
    ];

    protected $casts = [

        'week_start_date' => 'date',
        'week_end_date'   => 'date',

        'generated_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'paid_at'      => 'datetime',

        'payment_date' => 'date',

        'total_full_days' => 'decimal:2',
        'total_half_days' => 'decimal:2',
        'total_payable_days' => 'decimal:2',

        'total_normal_wages' => 'decimal:2',
        'total_ot_hours' => 'decimal:2',
        'total_ot_wages' => 'decimal:2',

        'total_labour_additions' => 'decimal:2',
        'total_labour_deductions' => 'decimal:2',

        'total_site_charges' => 'decimal:2',

        'gross_labour_wages' => 'decimal:2',
        'net_labour_wages' => 'decimal:2',
        'total_project_payable' => 'decimal:2',

        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function details()
    {
        return $this->hasMany(
            WeeklyWageSheetDetail::class
        );
    }

    public function charges()
    {
        return $this->hasMany(
            WeeklyWageSheetCharge::class
        );
    }

    public function generatedBy()
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }

    public function submittedBy()
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function rejectedBy()
    {
        return $this->belongsTo(
            User::class,
            'rejected_by'
        );
    }

    public function paidBy()
    {
        return $this->belongsTo(
            User::class,
            'paid_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCalculated(): bool
    {
        return $this->status === 'calculated';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}