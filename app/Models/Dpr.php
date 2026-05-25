<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DprMaterialReceived;
use App\Models\DprMaterialRequired;
use App\Models\DprMachineryTool;
use App\Models\SiteIssue;
use App\Models\TomorrowPlan;

class Dpr extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'dpr_date',
        'weather',
        'remarks',
        'status',
        'pmo_remarks'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workItems()
    {
        return $this->hasMany(DprWorkItem::class);
    }
    public function photos()
{
    return $this->hasMany(DprPhoto::class);
}
public function labours()
{
    return $this->hasMany(DprLabour::class);
}
public function materials()
{
    return $this->hasMany(DprMaterial::class);
}
public function materialReceived()
{
    return $this->hasMany(
        DprMaterialReceived::class
    );
}
public function materialRequired()
{
    return $this->hasMany(
        DprMaterialRequired::class
    );
}
public function machineryTools()
{
    return $this->hasMany(
        DprMachineryTool::class
    );
}
public function siteIssues()
{
    return $this->hasMany(
        SiteIssue::class
    );
}
public function tomorrowPlans()
{
    return $this->hasMany(
        TomorrowPlan::class
    );
}
}