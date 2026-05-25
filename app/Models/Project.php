<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Project extends Model
{
    protected $fillable = [
        'project_code',
        'project_name',
        'client_name',
        'location',
        'start_date',
        'target_completion_date',
        'status'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function dprs()
{
    return $this->hasMany(Dpr::class);
}
}