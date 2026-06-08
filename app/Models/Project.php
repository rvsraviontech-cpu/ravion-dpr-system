<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ProjectBlock;

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
public function blocks()
{
    return $this->hasMany(ProjectBlock::class);
}
public function floors()
{
    return $this->hasMany(ProjectFloor::class);
}
public function units()
{
    return $this->hasMany(ProjectUnit::class);
}
public function rooms()
{
    return $this->hasMany(ProjectRoom::class);
}
public function subspaces()
{
    return $this->hasMany(ProjectSubspace::class);
}

}