<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DprMachineryTool extends Model
{
    protected $fillable = [

        'dpr_id',

        'machinery_tool_id',

        'quantity',

        'usage_hours',

        'working_condition',

        'remarks'

    ];

    public function dpr()
    {
        return $this->belongsTo(Dpr::class);
    }

    public function machineryTool()
    {
        return $this->belongsTo(
            MachineryTool::class
        );
    }
}