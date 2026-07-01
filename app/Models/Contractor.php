<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityDivision;

class Contractor extends Model
{
    protected $fillable = [
        'contractor_code',
        'contractor_name',
        'company_name',
        'mobile',
        'alternate_mobile',
        'email',
        'work_category',
        'city',
        'district',
        'state',
        'pincode',
        'address',
        'gst_number',
        'pan_number',
        'aadhaar_number',
        'license_number',
        'rating',
        'experience_years',
        'is_preferred',
        'status',
        'remarks',
    ];

    protected $casts = [
        'is_preferred' => 'boolean',
    ];

    public function serviceCategories()
    {
        return $this->belongsToMany(
            ContractorServiceCategory::class,
            'contractor_services',
            'contractor_id',
            'contractor_service_category_id'
        )->withTimestamps();
    }

    public function divisions()
{
    return $this->belongsToMany(
        ActivityDivision::class,
        'contractor_divisions',
        'contractor_id',
        'activity_division_id'
    )->withTimestamps();
}
}