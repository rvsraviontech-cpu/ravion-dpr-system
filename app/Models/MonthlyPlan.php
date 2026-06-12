<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPlan extends Model
{
    protected $fillable = [
        'project_id',
        'activity_id',
        'user_id',
        'plan_month',
        'plan_year',
        'month_start_date',
        'month_end_date',
        'planned_quantity',
        'unit',
        'planned_labour',
        'materials_required',
        'machinery_required',
        'risks_constraints',
        'status',
        'remarks',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}