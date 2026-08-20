<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Dashboards
            ['module' => 'Dashboard', 'name' => 'dashboard.view'],
            ['module' => 'Dashboard', 'name' => 'dashboard.executive'],
            ['module' => 'Dashboard', 'name' => 'dashboard.project_health'],
            ['module' => 'Dashboard', 'name' => 'dashboard.pmo_exception'],

            // Masters
            ['module' => 'Projects', 'name' => 'projects.view'],
            ['module' => 'Projects', 'name' => 'projects.manage'],

            ['module' => 'Activities', 'name' => 'activities.view'],
            ['module' => 'Activities', 'name' => 'activities.manage'],

            ['module' => 'Activity Mapping', 'name' => 'activity_mappings.view'],
            ['module' => 'Activity Mapping', 'name' => 'activity_mappings.manage'],

            ['module' => 'Materials', 'name' => 'materials.view'],
            ['module' => 'Materials', 'name' => 'materials.manage'],

            ['module' => 'Material Categories', 'name' => 'material_categories.view'],
            ['module' => 'Material Categories', 'name' => 'material_categories.manage'],

            ['module' => 'Contractors', 'name' => 'contractors.view'],
            ['module' => 'Contractors', 'name' => 'contractors.manage'],

            ['module' => 'Vendors', 'name' => 'vendors.view'],
            ['module' => 'Vendors', 'name' => 'vendors.manage'],

            ['module' => 'Machinery', 'name' => 'machinery_tools.view'],
            ['module' => 'Machinery', 'name' => 'machinery_tools.manage'],

            ['module' => 'Location Masters', 'name' => 'location_masters.view'],
            ['module' => 'Location Masters', 'name' => 'location_masters.manage'],

            ['module' => 'Labour Types', 'name' => 'labour_types.view'],
            ['module' => 'Labour Types', 'name' => 'labour_types.manage'],
            ['module' => 'Labour Master Data', 'name' => 'labour_master_data.view'],
            ['module' => 'Labour Master Data', 'name' => 'labour_master_data.manage'],

            // DPR
            ['module' => 'DPR', 'name' => 'dpr.view'],
            ['module' => 'DPR', 'name' => 'dpr.create'],
            ['module' => 'DPR', 'name' => 'dpr.edit'],
            ['module' => 'DPR', 'name' => 'dpr.submit'],
            ['module' => 'DPR', 'name' => 'dpr.approve'],
            ['module' => 'DPR', 'name' => 'dpr.reject'],

            // Labour
            ['module' => 'Labour Reports', 'name' => 'labour_reports.view'],
            ['module' => 'Labour Reports', 'name' => 'labour_reports.create'],
            ['module' => 'Labour Reports', 'name' => 'labour_reports.edit'],
            ['module' => 'Labour Reports', 'name' => 'labour_reports.approve'],

            // Labour Master
            ['module' => 'Labour Master','name' => 'labour_masters.view',],
            [ 'module' => 'Labour Master',   'name' => 'labour_masters.create',],
            [    'module' => 'Labour Master',    'name' => 'labour_masters.edit',],
            
[
    'module' => 'Labour Master',
    'name' => 'labour_masters.toggle_status',
],

[
    'module' => 'Labour Master',
    'name' => 'labour_masters.financial_view',
],

[
    'module' => 'Labour Master',
    'name' => 'labour_masters.financial_manage',
    
],
// Labour Attendance

[
    'module' => 'Labour Attendance',
    'name' => 'labour_attendances.view',
],

[
    'module' => 'Labour Attendance',
    'name' => 'labour_attendances.create',
],

[
    'module' => 'Labour Attendance',
    'name' => 'labour_attendances.edit',
],

[
    'module' => 'Labour Attendance',
    'name' => 'labour_attendances.submit',
],

[
    'module' => 'Labour Attendance',
    'name' => 'labour_attendances.approve',
],

[
    'module' => 'Labour Attendance',
    'name' => 'labour_attendances.reject',
],

[
    'module' => 'Labour Attendance',
    'name' => 'labour_attendances.toggle_status',
],

/*
|--------------------------------------------------------------------------
| Attendance Corrections
|--------------------------------------------------------------------------
*/

[
    'module' => 'Attendance Corrections',
    'name' => 'attendance_corrections.view',
],

[
    'module' => 'Attendance Corrections',
    'name' => 'attendance_corrections.create',
],

[
    'module' => 'Attendance Corrections',
    'name' => 'attendance_corrections.edit',
],

[
    'module' => 'Attendance Corrections',
    'name' => 'attendance_corrections.submit',
],

[
    'module' => 'Attendance Corrections',
    'name' => 'attendance_corrections.approve',
],

[
    'module' => 'Attendance Corrections',
    'name' => 'attendance_corrections.apply',
],

[
    'module' => 'Attendance Corrections',
    'name' => 'attendance_corrections.delete',
],


 // Attendance Register
[
    'module' => 'Attendance Register',
    'name' => 'attendance_register.view',
],

[
    'module' => 'Attendance Register',
    'name' => 'attendance_register.export',
],

// Weekly Wage Sheets

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.view',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.create',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.calculate',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.edit',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.submit',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.approve',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.reject',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.mark_paid',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.export',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.manage_charges',
],

[
    'module' => 'Weekly Wage Sheets',
    'name' => 'weekly_wage_sheets.manage_adjustments',
],


            // Material Received
            ['module' => 'Material Received', 'name' => 'material_received.view'],
            ['module' => 'Material Received', 'name' => 'material_received.create'],
            ['module' => 'Material Received', 'name' => 'material_received.edit'],
            ['module' => 'Material Received', 'name' => 'material_received.approve'],

            // Material Consumed
            ['module' => 'Material Consumed', 'name' => 'material_consumed.view'],
            ['module' => 'Material Consumed', 'name' => 'material_consumed.create'],
            ['module' => 'Material Consumed', 'name' => 'material_consumed.edit'],
            ['module' => 'Material Consumed', 'name' => 'material_consumed.approve'],

            // Material Required
            ['module' => 'Material Required', 'name' => 'material_required.view'],
            ['module' => 'Material Required', 'name' => 'material_required.create'],
            ['module' => 'Material Required', 'name' => 'material_required.edit'],
            ['module' => 'Material Required', 'name' => 'material_required.approve'],

            // Planning
            ['module' => 'Weekly Plans', 'name' => 'weekly_plans.view'],
            ['module' => 'Weekly Plans', 'name' => 'weekly_plans.manage'],

            ['module' => 'Monthly Plans', 'name' => 'monthly_plans.view'],
            ['module' => 'Monthly Plans', 'name' => 'monthly_plans.manage'],

            ['module' => 'Tomorrow Plans', 'name' => 'tomorrow_plans.view'],
            ['module' => 'Tomorrow Plans', 'name' => 'tomorrow_plans.manage'],

            ['module' => 'Plan Vs Actual', 'name' => 'plan_vs_actual.view'],

            // PMO
            ['module' => 'DPR Review', 'name' => 'dpr_reviews.view'],
            ['module' => 'DPR Review', 'name' => 'dpr_reviews.approve'],
            ['module' => 'DPR Review', 'name' => 'dpr_reviews.reject'],

            ['module' => 'Material Verification', 'name' => 'material_verification.view'],
            ['module' => 'Material Verification', 'name' => 'material_verification.verify'],

            ['module' => 'Mapping Queue', 'name' => 'mapping_queue.view'],
            ['module' => 'Mapping Queue', 'name' => 'mapping_queue.manage'],

            // Reports
            ['module' => 'Reports', 'name' => 'reports.view'],
            ['module' => 'Reports', 'name' => 'reports.dpr'],
            ['module' => 'Reports', 'name' => 'reports.labour'],
            ['module' => 'Reports', 'name' => 'reports.material'],
            ['module' => 'Reports', 'name' => 'reports.project_summary'],

            // Administration
            // Administration

// Users
['module' => 'Users', 'name' => 'users.view'],
['module' => 'Users', 'name' => 'users.manage'],

// Departments
['module' => 'Departments', 'name' => 'departments.view'],
['module' => 'Departments', 'name' => 'departments.manage'],

// Employee Designations
['module' => 'Employee Designations', 'name' => 'employee_designations.view'],
['module' => 'Employee Designations', 'name' => 'employee_designations.manage'],

// Roles
['module' => 'Roles', 'name' => 'roles.view'],
['module' => 'Roles', 'name' => 'roles.manage'],

// Permissions
['module' => 'Permissions', 'name' => 'permissions.view'],
['module' => 'Permissions', 'name' => 'permissions.manage'],

            // Future Modules
            ['module' => 'Audit Trail', 'name' => 'audit_trail.view'],
            ['module' => 'System Settings', 'name' => 'system_settings.manage'],

            // Site Issues
            ['module' => 'Site Issues', 'name' => 'site_issues.view'],
            ['module' => 'Site Issues', 'name' => 'site_issues.create'],
            ['module' => 'Site Issues', 'name' => 'site_issues.edit'],
            ['module' => 'Site Issues', 'name' => 'site_issues.close'],

            // Project Dashboards
['module' => 'Project Dashboard', 'name' => 'project_dashboard.view'],
['module' => 'Project Progress Dashboard', 'name' => 'project_progress_dashboard.view'],
['module' => 'Project Health Dashboard', 'name' => 'project_health_dashboard.view'],
['module' => 'PMO Exception Dashboard', 'name' => 'pmo_exception_dashboard.view'],

// Activity Progress
['module' => 'Activity Progress', 'name' => 'activity_progress.view'],

// Material Reports / Ledger
['module' => 'Material Ledger', 'name' => 'material_ledger.view'],
['module' => 'Material Shortage Report', 'name' => 'material_shortage_report.view'],

// Tomorrow Plans
['module' => 'Tomorrow Plans', 'name' => 'tomorrow_plans.create'],
['module' => 'Tomorrow Plans', 'name' => 'tomorrow_plans.edit'],
['module' => 'Tomorrow Plans', 'name' => 'tomorrow_plans.approve'],

// Role Permissions
['module' => 'Role Permissions', 'name' => 'role_permissions.view'],
['module' => 'Role Permissions', 'name' => 'role_permissions.manage'],

// Future Cost / Productivity Modules
['module' => 'Cost Dashboard', 'name' => 'cost_dashboard.view'],
['module' => 'Contractor Performance', 'name' => 'contractor_performance.view'],
['module' => 'Productivity Dashboard', 'name' => 'productivity_dashboard.view'],
['module' => 'Material Balance Dashboard', 'name' => 'material_balance_dashboard.view'],

// Future Commercial / Controls Modules
['module' => 'BOQ', 'name' => 'boq.manage'],
['module' => 'Purchase Requisitions', 'name' => 'purchase_requisitions.manage'],
['module' => 'Work Orders', 'name' => 'work_orders.manage'],
['module' => 'Client Billing', 'name' => 'client_billing.manage'],
['module' => 'Vendor Billing', 'name' => 'vendor_billing.manage'],

// Future Governance Modules
['module' => 'Quality Checklists', 'name' => 'quality_checklists.manage'],
['module' => 'Safety Observations', 'name' => 'safety_observations.manage'],
['module' => 'Documents', 'name' => 'documents.manage'],

// Role Dashboards
['module' => 'Role Dashboards', 'name' => 'engineer_dashboard.view'],
['module' => 'Role Dashboards', 'name' => 'accountant_dashboard.view'],
['module' => 'Role Dashboards', 'name' => 'ceo_dashboard.view'],
['module' => 'Role Dashboards', 'name' => 'pmo_dashboard.view'],

// Audit Trail
['module' => 'Audit Trail', 'name' => 'audit_trail.view'],
        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'module' => $permission['module'],
                    'is_active' => true,
                ]
            );
        }
    }
}