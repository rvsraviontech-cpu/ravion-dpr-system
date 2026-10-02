<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkActivityProjectType extends Model
{
    protected $fillable = [
        'work_activity_id',
        'work_project_type_id',
    ];

    protected $casts = [
        'work_activity_id' => 'integer',
        'work_project_type_id' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            WorkActivity::class,
            'work_activity_id'
        );
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(
            WorkProjectType::class,
            'work_project_type_id'
        );
    }
}