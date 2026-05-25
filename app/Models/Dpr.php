<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}