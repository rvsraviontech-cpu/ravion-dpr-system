<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourReportDetail extends Model
{
    protected $fillable = [
        'labour_report_id',
        'labour_type_id',
        'contractor_id',
        'male_count',
        'female_count',
        'local_count',
        'non_local_count',
        'total_count',
        'remarks',
    ];

    public function labourReport()
    {
        return $this->belongsTo(LabourReport::class);
    }

    public function labourType()
    {
        return $this->belongsTo(LabourType::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
}