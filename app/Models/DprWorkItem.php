<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectBlock;
use App\Models\ProjectFloor;
use App\Models\ProjectUnit;
use App\Models\ProjectRoom;
use App\Models\ProjectSubspace;
use App\Models\ActivityMapping;

class DprWorkItem extends Model
{
    protected $fillable = [
        'dpr_id',
        'activity_id',
        'contractor_id',
        'quantity_completed',
        'remarks',
        'project_block_id',
        'project_floor_id',
        'project_unit_id',
        'project_room_id',
        'project_subspace_id',
        'activity_mapping_id'
    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
    public function block()
{
    return $this->belongsTo(ProjectBlock::class, 'project_block_id');
}

public function floor()
{
    return $this->belongsTo(ProjectFloor::class, 'project_floor_id');
}

public function unit()
{
    return $this->belongsTo(ProjectUnit::class, 'project_unit_id');
}

public function room()
{
    return $this->belongsTo(ProjectRoom::class, 'project_room_id');
}

public function subspace()
{
    return $this->belongsTo(ProjectSubspace::class, 'project_subspace_id');
}

public function activityMapping()
{
    return $this->belongsTo(ActivityMapping::class);
}
}