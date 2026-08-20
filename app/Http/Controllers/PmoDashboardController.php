<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PmoDashboardController extends Controller
{
    public function index(): View
    {
        $dprPending = DB::table('dprs')
            ->join('projects', 'projects.id', '=', 'dprs.project_id')
            ->leftJoin('users', 'users.id', '=', 'dprs.user_id')
            ->where('dprs.status', 'Pending')
            ->select('dprs.id','dprs.dpr_date as date','dprs.status','projects.project_name','projects.location','users.name as engineer_name')
            ->orderByDesc('dprs.dpr_date')->orderByDesc('dprs.id')->limit(5)->get();

        $labourAttendance = DB::table('labour_attendances')
            ->join('projects', 'projects.id', '=', 'labour_attendances.project_id')
            ->leftJoin('users', 'users.id', '=', 'labour_attendances.recorded_by')
            ->where('labour_attendances.status', 'submitted')
            ->whereNull('labour_attendances.deleted_at')
            ->select('labour_attendances.id','labour_attendances.attendance_date as date','labour_attendances.status','labour_attendances.total_labours','labour_attendances.present_count','labour_attendances.absent_count','projects.project_name','projects.location','users.name as engineer_name')
            ->orderByDesc('labour_attendances.attendance_date')->orderByDesc('labour_attendances.id')->limit(5)->get();

        $attendanceCorrections = DB::table('attendance_corrections')
            ->join('projects', 'projects.id', '=', 'attendance_corrections.project_id')
            ->where('attendance_corrections.status', 'submitted')
            ->whereNull('attendance_corrections.deleted_at')
            ->select('attendance_corrections.id','attendance_corrections.correction_number','attendance_corrections.attendance_date as date','attendance_corrections.status','projects.project_name')
            ->orderByDesc('attendance_corrections.attendance_date')->orderByDesc('attendance_corrections.id')->limit(5)->get();

        $materialReceived = DB::table('material_receiveds')
            ->join('projects', 'projects.id', '=', 'material_receiveds.project_id')
            ->leftJoin('users', 'users.id', '=', 'material_receiveds.user_id')
            ->where('material_receiveds.status', 'Submitted')
            ->select('material_receiveds.id','material_receiveds.received_date as date','material_receiveds.status','material_receiveds.material_name','material_receiveds.brand','material_receiveds.specification','material_receiveds.quantity_received','material_receiveds.unit','material_receiveds.pmo_verification_status','projects.project_name','users.name as engineer_name')
            ->orderByDesc('material_receiveds.received_date')->orderByDesc('material_receiveds.id')->limit(5)->get();

        $materialVerification = DB::table('material_verifications')
            ->join('material_receiveds', 'material_receiveds.id', '=', 'material_verifications.material_received_id')
            ->join('projects', 'projects.id', '=', 'material_verifications.project_id')
            ->leftJoin('materials', 'materials.id', '=', 'material_verifications.material_id')
            ->whereNull('material_verifications.verified_at')
            ->select('material_verifications.id','material_verifications.material_received_id','material_verifications.created_at as date','material_verifications.verification_status as status','material_verifications.received_quantity','material_verifications.unit','projects.project_name','materials.material_name as material_name')
            ->orderByDesc('material_verifications.created_at')->orderByDesc('material_verifications.id')->limit(5)->get();

        $materialConsumed = DB::table('material_consumeds')
            ->join('projects', 'projects.id', '=', 'material_consumeds.project_id')
            ->leftJoin('materials', 'materials.id', '=', 'material_consumeds.material_id')
            ->leftJoin('users', 'users.id', '=', 'material_consumeds.user_id')
            ->where('material_consumeds.status', 'Submitted')
            ->select('material_consumeds.id','material_consumeds.consumed_date as date','material_consumeds.status','material_consumeds.quantity_consumed','material_consumeds.unit','projects.project_name','materials.material_name as material_name','users.name as engineer_name')
            ->orderByDesc('material_consumeds.consumed_date')->orderByDesc('material_consumeds.id')->limit(5)->get();

        $materialRequirements = DB::table('material_requirements')
            ->join('projects', 'projects.id', '=', 'material_requirements.project_id')
            ->leftJoin('materials', 'materials.id', '=', 'material_requirements.material_id')
            ->leftJoin('users', 'users.id', '=', 'material_requirements.created_by')
            ->where('material_requirements.status', 'Submitted')
            ->select('material_requirements.id','material_requirements.required_date as date','material_requirements.status','material_requirements.priority','material_requirements.required_quantity','material_requirements.fulfilled_quantity','material_requirements.unit','projects.project_name','materials.material_name as material_name','users.name as engineer_name')
            ->orderBy('material_requirements.required_date')->orderByDesc('material_requirements.id')->limit(5)->get();

        $mappingPending = DB::table('dpr_work_items')
            ->join('projects', 'projects.id', '=', 'dpr_work_items.project_id')
            ->leftJoin('activities', 'activities.id', '=', 'dpr_work_items.activity_id')
            ->leftJoin('users', 'users.id', '=', 'dpr_work_items.user_id')
            ->whereNull('dpr_work_items.activity_mapping_id')
            ->select('dpr_work_items.id','dpr_work_items.work_date as date','dpr_work_items.status','projects.project_name','activities.activity_name as activity_name','users.name as engineer_name')
            ->orderByDesc('dpr_work_items.work_date')->orderByDesc('dpr_work_items.id')->limit(5)->get();

        $siteIssues = DB::table('site_issues')
            ->join('projects', 'projects.id', '=', 'site_issues.project_id')
            ->leftJoin('users', 'users.id', '=', 'site_issues.created_by')
            ->where('site_issues.status', 'Open')
            ->where('site_issues.escalated_to_pmo', 1)
            ->select('site_issues.id','site_issues.issue_date as date','site_issues.title','site_issues.issue_type','site_issues.priority','site_issues.status','site_issues.target_closure_date','site_issues.escalated_to_pmo','projects.project_name','users.name as engineer_name')
            ->orderByDesc('site_issues.issue_date')->orderByDesc('site_issues.id')->limit(5)->get();

        $tomorrowPlans = DB::table('tomorrow_plans')
            ->join('projects', 'projects.id', '=', 'tomorrow_plans.project_id')
            ->leftJoin('activities', 'activities.id', '=', 'tomorrow_plans.activity_id')
            ->leftJoin('users', 'users.id', '=', 'tomorrow_plans.created_by')
            ->where('tomorrow_plans.status', 'Submitted')
            ->select('tomorrow_plans.id','tomorrow_plans.planned_date as date','tomorrow_plans.status','tomorrow_plans.priority','tomorrow_plans.planned_quantity','tomorrow_plans.unit','projects.project_name','activities.activity_name as activity_name','users.name as engineer_name')
            ->orderBy('tomorrow_plans.planned_date')->orderByDesc('tomorrow_plans.id')->limit(5)->get();

        $counts = [
            'dpr_pending' => DB::table('dprs')->where('status', 'Pending')->count(),
            'attendance_pending' => DB::table('labour_attendances')->where('status', 'submitted')->whereNull('deleted_at')->count(),
            'attendance_corrections' => DB::table('attendance_corrections')->where('status', 'submitted')->whereNull('deleted_at')->count(),
            'material_received' => DB::table('material_receiveds')->where('status', 'Submitted')->count(),
            'material_verification' => DB::table('material_verifications')->whereNull('verified_at')->count(),
            'material_consumed' => DB::table('material_consumeds')->where('status', 'Submitted')->count(),
            'material_requirements' => DB::table('material_requirements')->where('status', 'Submitted')->count(),
            'mapping_pending' => DB::table('dpr_work_items')->whereNull('activity_mapping_id')->count(),
            'open_issues' => DB::table('site_issues')->where('status', 'Open')->where('escalated_to_pmo', 1)->count(),
            'tomorrow_plans' => DB::table('tomorrow_plans')->where('status', 'Submitted')->count(),
        ];

        $counts['total_actions'] = array_sum($counts);

        // Temporary compatibility values for the current Blade.
        $missingDpr = collect();
        $workDone = collect();
        $sitePhotos = collect();
        $missingAttendance = collect();
        $weeklyAttendance = collect();
        $weeklyWageSheets = collect();
        $materialShortages = collect();
        $criticalIssues = collect();
        $machinery = collect();
        $missingTomorrowPlans = collect();
        $weeklyPlans = collect();
        $monthlyPlans = collect();

        $counts += [
            'missing_dpr' => 0,
            'missing_attendance' => 0,
            'material_shortages' => 0,
            'critical_issues' => 0,
            'missing_tomorrow_plans' => 0,
            'weekly_plans' => 0,
            'monthly_plans' => 0,
        ];

        return view('pmo.index', compact(
            'counts',
            'dprPending',
            'mappingPending',
            'labourAttendance',
            'attendanceCorrections',
            'materialReceived',
            'materialVerification',
            'materialConsumed',
            'materialRequirements',
            'siteIssues',
            'tomorrowPlans',
            'missingDpr',
            'workDone',
            'sitePhotos',
            'missingAttendance',
            'weeklyAttendance',
            'weeklyWageSheets',
            'materialShortages',
            'criticalIssues',
            'machinery',
            'missingTomorrowPlans',
            'weeklyPlans',
            'monthlyPlans'
        ));
    }
}
