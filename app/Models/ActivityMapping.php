<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityMapping extends Model
{
    protected $fillable = [
        'activity_id',
        'division_code',
        'rh_cost_code',
        'odoo_type_code',
        'odoo_type',
        'unit',
        'project_type',
        'structure_type',
        'work_stage',
        'activity_name',
        'boq_item_id',
        'material_group',
        'contractor_type',
        'productivity_norm',
        'quality_checklist_id',
        'odoo_analytic_account_code',
        'odoo_analytic_tag_code',
        'inventory_expense_bucket',
        'procurement_mode',
        'is_active',
        'remarks',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}