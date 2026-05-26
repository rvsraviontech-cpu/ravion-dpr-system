<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectFloor;

class ProjectBlock extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'code',
        'type',
        'is_active',
        'remarks',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function floors()
{
    return $this->hasMany(
        ProjectFloor::class,
        'project_block_id'
    );
}
}