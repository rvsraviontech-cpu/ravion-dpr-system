<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = Permission::pluck('id')->toArray();

        $rolePermissions = [

            'Admin' => Permission::pluck('name')->toArray(),

            'Engineer' => [
                'dashboard.view',
                'engineer_dashboard.view',

                'dpr.view',
                'dpr.create',
                'dpr.edit',
                'dpr.submit',

                'labour_reports.view',
                'labour_reports.create',
                'labour_reports.edit',

                'material_received.view',
                'material_received.create',
                'material_received.edit',

                'material_consumed.view',
                'material_consumed.create',
                'material_consumed.edit',

                'material_required.view',
                'material_required.create',
                'material_required.edit',

                'incoming_materials.view',
                'incoming_materials.receive',
                'incoming_materials.confirm',

                'site_issues.view',
                'site_issues.create',
                'site_issues.edit',

                'tomorrow_plans.view',
                'tomorrow_plans.create',
                'tomorrow_plans.edit',
            ],

            'PMO' => [
                'dashboard.view',
                'dashboard.executive',
                'dashboard.project_health',
                'dashboard.pmo_exception',
                'project_progress_dashboard.view',
                'project_dashboard.view',
                'project_health_dashboard.view',
                'pmo_exception_dashboard.view',
                'pmo_dashboard.view',

                'projects.view',
                'activities.view',
                'activity_mappings.view',
                'location_masters.view',

                'dpr.view',
                'dpr_reviews.view',
                'dpr_reviews.approve',
                'dpr_reviews.reject',

                'labour_reports.view',
                'labour_reports.approve',

                'material_received.view',
                'material_consumed.view',
                'material_required.view',

                'purchase_orders.view',
                'purchase_orders.create',
                'purchase_orders.edit',
                'purchase_orders.place_order',

                'material_dispatches.view',
                'material_dispatches.create',
                'material_dispatches.edit',
                'material_dispatches.dispatch',

                'incoming_materials.view',
                'incoming_materials.receive',
                'incoming_materials.confirm',

                'construction_work_packages.view',
                'product_usage_mappings.view',
                'product_usage_mappings.manage',
                'pending_material_classifications.view',
                'pending_material_classifications.manage',

                'material_verification.view',
                'material_verification.verify',

                'mapping_queue.view',
                'mapping_queue.manage',

                'weekly_plans.view',
                'weekly_plans.manage',
                'monthly_plans.view',
                'monthly_plans.manage',
                'tomorrow_plans.view',
                'tomorrow_plans.approve',
                'plan_vs_actual.view',
                'activity_progress.view',

                'site_issues.view',
                'site_issues.close',

                'reports.view',
                'reports.dpr',
                'reports.labour',
                'reports.material',
                'reports.project_summary',
            ],

            'DGM' => [
                'dashboard.view',
                'dashboard.executive',
                'dashboard.project_health',
                'dashboard.pmo_exception',
                'project_progress_dashboard.view',
                'project_dashboard.view',
                'project_health_dashboard.view',
                'pmo_exception_dashboard.view',

                'projects.view',
'activities.view',
'activity_mappings.view',
'construction_work_packages.view',
'location_masters.view',

                'dpr.view',
                'dpr_reviews.view',
                'dpr_reviews.approve',
                'dpr_reviews.reject',

                'labour_reports.view',
                'labour_reports.approve',

                'material_received.view',
                'material_consumed.view',
                'material_required.view',
                'purchase_orders.view',
                'material_dispatches.view',
                'incoming_materials.view',
                'product_usage_mappings.view',
                'pending_material_classifications.view',
                'material_verification.view',
                'mapping_queue.view',

                'weekly_plans.view',
                'monthly_plans.view',
                'tomorrow_plans.view',
                'plan_vs_actual.view',
                'activity_progress.view',

                'site_issues.view',

                'reports.view',
                'reports.dpr',
                'reports.labour',
                'reports.material',
                'reports.project_summary',
            ],

            'Accountant' => [
                'dashboard.view',
                'accountant_dashboard.view',

                'material_received.view',
                'material_consumed.view',
                'material_required.view',
                'purchase_orders.view',
                'incoming_materials.view',

                'material_verification.view',
                'material_ledger.view',
                'material_shortage_report.view',

                'reports.view',
                'reports.material',
                'reports.project_summary',
            ],

            'CEO' => [
                'dashboard.view',
                'ceo_dashboard.view',
                'dashboard.executive',
                'dashboard.project_health',
                'dashboard.pmo_exception',
                'project_progress_dashboard.view',
                'project_dashboard.view',
                'project_health_dashboard.view',
                'pmo_exception_dashboard.view',

                'purchase_orders.view',
                'material_dispatches.view',
                'incoming_materials.view',

                'reports.view',
                'reports.dpr',
                'reports.labour',
                'reports.material',
                'reports.project_summary',
            ],

        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {

            $role = Role::where('name', $roleName)->first();

            if (!$role) {
                continue;
            }

            if ($roleName === 'Admin') {
                $role->permissions()->sync($allPermissions);
                continue;
            }

            $permissionIds = Permission::whereIn('name', $permissionNames)
                ->pluck('id')
                ->toArray();

            $role->permissions()->sync($permissionIds);
        }
    }
}