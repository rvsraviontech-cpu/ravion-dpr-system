<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\DprController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\LabourTypeController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\MachineryToolController;
use App\Http\Controllers\WeeklyPlanController;
use App\Http\Controllers\ActivityMappingController;
use App\Http\Controllers\ProjectLocationController;
use App\Http\Controllers\LocationBlockMasterController;
use App\Http\Controllers\LocationFloorMasterController;
use App\Http\Controllers\LocationUnitMasterController;
use App\Http\Controllers\LocationRoomMasterController;
use App\Http\Controllers\LocationSubspaceMasterController;
use App\Http\Controllers\LocationMasterController;
use App\Http\Controllers\LabourReportController;
use App\Http\Controllers\MaterialReceivedController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialConsumedController;
use App\Http\Controllers\StockRegisterController;
use App\Http\Controllers\MaterialLedgerController;
use App\Http\Controllers\MaterialRequirementController;
use App\Http\Controllers\MaterialShortageReportController;
use App\Http\Controllers\TomorrowPlanController;
use App\Http\Controllers\SiteIssueController;
use App\Http\Controllers\PlanVsActualController;
use App\Http\Controllers\MonthlyPlanController;
use App\Http\Controllers\MaterialVerificationController;
use App\Http\Controllers\MappingPendingQueueController;
use App\Http\Controllers\ProjectProgressDashboardController;
use App\Http\Controllers\ProjectDashboardController;
use App\Http\Controllers\ActivityProgressController;
use App\Http\Controllers\ProjectHealthDashboardController;
use App\Http\Controllers\PmoExceptionDashboardController;
use App\Http\Controllers\PmoDashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\LabourCategoryController;
use App\Http\Controllers\UnitMasterController;
use App\Http\Controllers\BrandMasterController;
use App\Http\Controllers\ActivityDivisionController;
use App\Http\Controllers\WorkStageController;
use App\Http\Controllers\ContractorServiceCategoryController;
use App\Http\Controllers\AttendanceStatusController;
use App\Http\Controllers\GenderController;
use App\Http\Controllers\ManpowerSourceController;
use App\Http\Controllers\SkillCategoryController;
use App\Http\Controllers\DesignationRoleController;
use App\Http\Controllers\WorkingStatusController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LabourController;
use App\Http\Controllers\LabourAttendanceController;
use App\Http\Controllers\LabourAttendanceCorrectionController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\LabourAttendanceRegisterController;
use App\Http\Controllers\WeeklyWageSheetController;
use App\Http\Controllers\WeeklyLabourPaymentController;
use App\Http\Controllers\MaterialSpecificationController;
use App\Http\Controllers\MaterialTypeController;
use App\Http\Controllers\MaterialGradeController;
use App\Http\Controllers\DprWorkItemController;
use App\Http\Controllers\WorkDoneController;
use App\Http\Controllers\LabourGroupController;
use App\Http\Controllers\WeeklyAttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeDesignationController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'permission:dashboard.view'])->name('dashboard');

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

        Route::get('/ajax/projects/{project}/blocks', [ProjectLocationController::class, 'ajaxBlocks'])->name('ajax.project.blocks');
Route::get('/ajax/blocks/{block}/floors', [ProjectLocationController::class, 'ajaxFloors'])->name('ajax.block.floors');
Route::get('/ajax/floors/{floor}/units', [ProjectLocationController::class, 'ajaxUnits'])->name('ajax.floor.units');
Route::get('/ajax/units/{unit}/rooms', [ProjectLocationController::class, 'ajaxRooms'])->name('ajax.unit.rooms');
Route::get('/ajax/rooms/{room}/subspaces', [ProjectLocationController::class, 'ajaxSubspaces'])->name('ajax.room.subspaces');
Route::resource('activity-divisions', ActivityDivisionController::class)
    ->middleware('permission:activities.manage');

Route::resource('work-stages', WorkStageController::class)
    ->middleware('permission:activities.manage');

    /*
    |--------------------------------------------------------------------------
    | Dashboards
    |--------------------------------------------------------------------------
    */

    Route::get('/admin-dashboard', [DashboardController::class, 'admin'])
    ->name('admin-dashboard')
    ->middleware('permission:dashboard.view');

    Route::get('/engineer-dashboard', [DashboardController::class, 'engineer'])
        ->name('engineer-dashboard')
        ->middleware('permission:engineer_dashboard.view');

    Route::get('/pmo-dashboard', [PmoDashboardController::class, 'index'])
        ->name('pmo-dashboard')
        ->middleware('permission:pmo_dashboard.view');

    Route::get('/ceo-dashboard', [DashboardController::class, 'ceo'])
        ->name('ceo-dashboard')
        ->middleware('permission:ceo_dashboard.view');

    Route::get('/accountant-dashboard', [DashboardController::class, 'accountant'])
        ->name('accountant-dashboard')
        ->middleware('permission:accountant_dashboard.view');

    Route::get('/project-progress-dashboard', [ProjectProgressDashboardController::class, 'index'])
        ->name('project-progress-dashboard.index')
        ->middleware('permission:project_progress_dashboard.view');

    Route::get('/project-dashboard/{project}', [ProjectDashboardController::class, 'show'])
        ->name('project-dashboard.show')
        ->middleware('permission:project_dashboard.view');

    Route::get('/project-health-dashboard', [ProjectHealthDashboardController::class, 'index'])
        ->name('project-health-dashboard.index')
        ->middleware('permission:project_health_dashboard.view');

    Route::get('/pmo-exception-dashboard', [PmoExceptionDashboardController::class, 'index'])
        ->name('pmo-exception-dashboard.index')
        ->middleware('permission:pmo_exception_dashboard.view');

    Route::get('/engineer-productivity', [DashboardController::class, 'engineerProductivity'])
        ->name('engineer-productivity')
        ->middleware('permission:reports.view');

Route::middleware(['auth'])->group(function () {

    Route::resource(
        'activity-material-mappings',
        App\Http\Controllers\ActivityMaterialMappingController::class
    );

});

Route::resource(
    'material-specifications',
    MaterialSpecificationController::class
);

Route::resource(
    'material-types',
    MaterialTypeController::class
);


Route::resource(
    'material-grades',
    MaterialGradeController::class
);

Route::patch(
    'material-received/{materialReceived}/accountant-verify',
    [MaterialReceivedController::class, 'accountantVerify']
)
    ->middleware('permission:material_received.accountant_verify')
    ->name('material-received.accountant-verify');

  /*  Route::prefix('work-done')
    ->name('work-done.')
    ->group(function () {
        Route::get('/', [DprWorkItemController::class, 'index'])
            ->name('index');

        Route::get('/create', [DprWorkItemController::class, 'create'])
            ->name('create');

        Route::post('/', [DprWorkItemController::class, 'store'])
            ->name('store');

        Route::get('/{workDone}', [DprWorkItemController::class, 'show'])
            ->name('show');

        Route::get('/{workDone}/edit', [DprWorkItemController::class, 'edit'])
            ->name('edit');

        Route::put('/{workDone}', [DprWorkItemController::class, 'update'])
            ->name('update');

        Route::delete('/{workDone}', [DprWorkItemController::class, 'destroy'])
            ->name('destroy');*/

        Route::delete(
            '/{workDone}/photos/{photo}',
            [DprWorkItemController::class, 'destroyPhoto']
        )->name('photos.destroy');

        
    // Labour Group Master
Route::get('/labour-groups', [LabourGroupController::class, 'index'])
    ->name('labour-groups.index');
Route::post('/labour-groups', [LabourGroupController::class, 'store'])
    ->name('labour-groups.store');
Route::put('/labour-groups/{labourGroup}', [LabourGroupController::class, 'update'])
    ->name('labour-groups.update');
Route::patch('/labour-groups/{labourGroup}/toggle-status', [LabourGroupController::class, 'toggleStatus'])
    ->name('labour-groups.toggle-status');
Route::get('/labour-groups/assignments/manage', [LabourGroupController::class, 'assignments'])
    ->name('labour-groups.assignments');
Route::put('/labour-groups/assignments/manage', [LabourGroupController::class, 'updateAssignments'])
    ->name('labour-groups.assignments.update');

// Admin / PMO Weekly Attendance bulk-entry tool.
Route::get('/weekly-attendance', [WeeklyAttendanceController::class, 'index'])
    ->name('weekly-attendance.index');
Route::post('/weekly-attendance', [WeeklyAttendanceController::class, 'store'])
    ->name('weekly-attendance.store');

    /*
|--------------------------------------------------------------------------
| Work Done v2 / Daily Work Execution
|--------------------------------------------------------------------------
|
| Replace the OLD work-done route group that points to DprWorkItemController
| with this block. Keep this inside your existing auth / role / permission
| middleware area.
|
*/

Route::prefix('work-done')
    ->name('work-done.')
    ->group(function () {

        Route::get(
            '/available-materials',
            [WorkDoneController::class, 'availableMaterials']
        )->name('available-materials');

        Route::get(
            '/',
            [WorkDoneController::class, 'index']
        )->name('index');

        Route::get(
            '/create',
            [WorkDoneController::class, 'create']
        )->name('create');

        Route::post(
            '/',
            [WorkDoneController::class, 'store']
        )->name('store');

        Route::get(
            '/{workDone}',
            [WorkDoneController::class, 'show']
        )->name('show');

        Route::get(
            '/{workDone}/edit',
            [WorkDoneController::class, 'edit']
        )->name('edit');

        Route::put(
            '/{workDone}',
            [WorkDoneController::class, 'update']
        )->name('update');

        Route::delete(
            '/{workDone}',
            [WorkDoneController::class, 'destroy']
        )->name('destroy');
    });

// BRAND MASTER CONTROLLER ROUTES

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/brand-masters',
        [BrandMasterController::class, 'index']
    )->name('brand-masters.index');

    Route::get(
        '/brand-masters/create',
        [BrandMasterController::class, 'create']
    )->name('brand-masters.create');

    Route::post(
        '/brand-masters',
        [BrandMasterController::class, 'store']
    )->name('brand-masters.store');

    Route::get(
        '/brand-masters/{brandMaster}/edit',
        [BrandMasterController::class, 'edit']
    )->name('brand-masters.edit');

    Route::put(
        '/brand-masters/{brandMaster}',
        [BrandMasterController::class, 'update']
    )->name('brand-masters.update');

    Route::patch(
        '/brand-masters/{brandMaster}/toggle-status',
        [BrandMasterController::class, 'toggleStatus']
    )->name('brand-masters.toggle-status');

});

    /*
    |--------------------------------------------------------------------------
    | Masters
    |--------------------------------------------------------------------------
    */

    Route::resource('projects', ProjectController::class)
        ->middleware('permission:projects.view');

    Route::get('/project-progress', [ProjectController::class, 'progress'])
        ->middleware('permission:projects.view');

    Route::resource('activities', ActivityController::class)
        ->middleware('permission:activities.view');

    Route::resource('contractors', ContractorController::class)
        ->middleware('permission:contractors.view');

    Route::resource('material-categories', MaterialCategoryController::class)
        ->middleware('permission:material_categories.view');

    Route::patch('/material-categories/{materialCategory}/toggle-status', [MaterialCategoryController::class, 'toggleStatus'])
        ->name('material-categories.toggle-status')
        ->middleware('permission:material_categories.view');

    Route::resource('materials', MaterialController::class)
        ->middleware('permission:materials.view');

    Route::patch('/materials/{material}/toggle-status', [MaterialController::class, 'toggleStatus'])
        ->name('materials.toggle-status')
        ->middleware('permission:materials.view');

    Route::resource('vendors', VendorController::class)
        ->middleware('permission:vendors.view');

    Route::patch('/vendors/{vendor}/toggle-status', [VendorController::class, 'toggleStatus'])
        ->name('vendors.toggle-status')
        ->middleware('permission:vendors.view');

    Route::resource('machinery-tools', MachineryToolController::class)
        ->middleware('permission:machinery_tools.view');

        Route::middleware([
    'permission:attendance_register.view',
])->group(function (): void {
    Route::get(
        '/labour-attendance-register',
        [LabourAttendanceRegisterController::class, 'index']
    )->name('labour-attendance-register.index');
});

Route::middleware([
    'permission:attendance_register.view',
])->group(function (): void {

    Route::get(
        '/labour-attendance-register',
        [LabourAttendanceRegisterController::class, 'index']
    )->name('labour-attendance-register.index');

    Route::get(
        '/labour-attendance-register/export/excel',
        [LabourAttendanceRegisterController::class, 'exportExcel']
    )
        ->middleware('permission:attendance_register.export')
        ->name('labour-attendance-register.export-excel');

    Route::get(
        '/labour-attendance-register/export/pdf',
        [LabourAttendanceRegisterController::class, 'exportPdf']
    )
        ->middleware('permission:attendance_register.export')
        ->name('labour-attendance-register.export-pdf');
});


    /*
    |--------------------------------------------------------------------------
    | Labour Categories & Labour Types
    |--------------------------------------------------------------------------
    */

    Route::get('/labour-categories', [LabourCategoryController::class, 'index'])
        ->name('labour-categories.index')
        ->middleware('permission:labour_types.view');

    Route::post('/labour-categories', [LabourCategoryController::class, 'store'])
        ->name('labour-categories.store')
        ->middleware('permission:labour_types.view');

    Route::get('/labour-categories/{labourCategory}/edit', [LabourCategoryController::class, 'edit'])
        ->name('labour-categories.edit')
        ->middleware('permission:labour_types.view');

    Route::put('/labour-categories/{labourCategory}', [LabourCategoryController::class, 'update'])
        ->name('labour-categories.update')
        ->middleware('permission:labour_types.view');

    Route::patch('/labour-categories/{labourCategory}/toggle-status', [LabourCategoryController::class, 'toggleStatus'])
        ->name('labour-categories.toggle-status')
        ->middleware('permission:labour_types.view');

    Route::resource('labour-types', LabourTypeController::class)
        ->middleware('permission:labour_types.view');

    Route::post('/labour-types/{labourType}/toggle', [LabourTypeController::class, 'toggle'])
        ->name('labour-types.toggle')
        ->middleware('permission:labour_types.view');

        Route::resource('contractor-service-categories', ContractorServiceCategoryController::class)
    ->middleware('permission:contractors.view');

    /*
|--------------------------------------------------------------------------
| Attendance Status Master
|--------------------------------------------------------------------------
*/

Route::resource('attendance-statuses', AttendanceStatusController::class)
    ->middleware('permission:labour_master_data.view');

Route::patch(
    '/attendance-statuses/{attendanceStatus}/toggle-status',
    [AttendanceStatusController::class, 'toggleStatus']
)
    ->name('attendance-statuses.toggle-status')
    ->middleware('permission:labour_master_data.manage');

    /*
|--------------------------------------------------------------------------
| Labour Attendance
|--------------------------------------------------------------------------
*/

Route::get(
    '/labour-attendances',
    [LabourAttendanceController::class, 'index']
)
    ->name('labour-attendances.index')
    ->middleware('permission:labour_attendances.view');

Route::get(
    '/labour-attendances/create',
    [LabourAttendanceController::class, 'create']
)
    ->name('labour-attendances.create')
    ->middleware('permission:labour_attendances.create');

Route::post(
    '/labour-attendances',
    [LabourAttendanceController::class, 'store']
)
    ->name('labour-attendances.store')
    ->middleware('permission:labour_attendances.create');

Route::get(
    '/labour-attendances/{labourAttendance}',
    [LabourAttendanceController::class, 'show']
)
    ->name('labour-attendances.show')
    ->middleware('permission:labour_attendances.view');

Route::get(
    '/labour-attendances/{labourAttendance}/edit',
    [LabourAttendanceController::class, 'edit']
)
    ->name('labour-attendances.edit')
    ->middleware('permission:labour_attendances.edit');

Route::put(
    '/labour-attendances/{labourAttendance}',
    [LabourAttendanceController::class, 'update']
)
    ->name('labour-attendances.update')
    ->middleware('permission:labour_attendances.edit');

Route::patch(
    '/labour-attendances/{labourAttendance}/submit',
    [LabourAttendanceController::class, 'submit']
)
    ->name('labour-attendances.submit')
    ->middleware('permission:labour_attendances.submit');

Route::patch(
    '/labour-attendances/{labourAttendance}/approve',
    [LabourAttendanceController::class, 'approve']
)
    ->name('labour-attendances.approve')
    ->middleware('permission:labour_attendances.approve');

Route::patch(
    '/labour-attendances/{labourAttendance}/reject',
    [LabourAttendanceController::class, 'reject']
)
    ->name('labour-attendances.reject')
    ->middleware('permission:labour_attendances.reject');

Route::patch(
    '/labour-attendances/{labourAttendance}/toggle-status',
    [LabourAttendanceController::class, 'toggleStatus']
)
    ->name('labour-attendances.toggle-status')
    ->middleware('permission:labour_attendances.toggle_status');

    Route::patch(
    '/labour-attendances/{labourAttendance}/reopen',
    [LabourAttendanceController::class, 'reopen']
)
->name('labour-attendances.reopen')
->middleware('permission:labour_attendances.reopen');

/*
|--------------------------------------------------------------------------
| Labour Attendance Corrections
|--------------------------------------------------------------------------
*/

Route::get(
    '/labour-attendance-corrections',
    [LabourAttendanceCorrectionController::class, 'index']
)
    ->name('labour-attendance-corrections.index')
    ->middleware('permission:labour_attendances.view');

    /*
|--------------------------------------------------------------------------
| Attendance Corrections
|--------------------------------------------------------------------------
*/

Route::middleware([
    'permission:attendance_corrections.view'
])->group(function () {

    Route::resource(
        'attendance-corrections',
        AttendanceCorrectionController::class
    );

    Route::post(
        'attendance-corrections/{attendanceCorrection}/submit',
        [AttendanceCorrectionController::class, 'submit']
    )->name('attendance-corrections.submit');

    Route::post(
        'attendance-corrections/{attendanceCorrection}/approve',
        [AttendanceCorrectionController::class, 'approve']
    )->name('attendance-corrections.approve');

    Route::post(
        'attendance-corrections/{attendanceCorrection}/reject',
        [AttendanceCorrectionController::class, 'reject']
    )->name('attendance-corrections.reject');

    Route::post(
        'attendance-corrections/{attendanceCorrection}/apply',
        [AttendanceCorrectionController::class, 'apply']
    )->name('attendance-corrections.apply');
});

/*
|--------------------------------------------------------------------------
| Labour Attendance AJAX
|--------------------------------------------------------------------------
*/

Route::get(
    '/ajax/projects/{project}/labours',
    [LabourAttendanceController::class, 'projectLabours']
)
    ->name('ajax.projects.labours')
    ->middleware('permission:labour_attendances.view');

Route::get(
    '/ajax/attendance-statuses',
    [LabourAttendanceController::class, 'attendanceStatuses']
)
    ->name('ajax.attendance-statuses')
    ->middleware('permission:labour_attendances.view');

    /*
    ---------------------
    WAGES CALCULATION
    ----------------------
     */

    Route::middleware([
    'permission:weekly_wage_sheets.view',
])->group(function (): void {

    Route::get(
        '/weekly-wage-sheets',
        [WeeklyWageSheetController::class, 'index']
    )->name('weekly-wage-sheets.index');

    Route::get(
        '/weekly-wage-sheets/create',
        [WeeklyWageSheetController::class, 'create']
    )
        ->middleware('permission:weekly_wage_sheets.create')
        ->name('weekly-wage-sheets.create');

    Route::post(
        '/weekly-wage-sheets',
        [WeeklyWageSheetController::class, 'store']
    )
        ->middleware('permission:weekly_wage_sheets.create')
        ->name('weekly-wage-sheets.store');

    Route::get(
        '/weekly-wage-sheets/{weeklyWageSheet}',
        [WeeklyWageSheetController::class, 'show']
    )->name('weekly-wage-sheets.show');

    Route::post(
        '/weekly-wage-sheets/{weeklyWageSheet}/generate',
        [WeeklyWageSheetController::class, 'generate']
    )
        ->middleware('permission:weekly_wage_sheets.calculate')
        ->name('weekly-wage-sheets.generate');

    Route::put(
        '/weekly-wage-sheets/{weeklyWageSheet}/adjustments',
        [WeeklyWageSheetController::class, 'updateAdjustments']
    )
        ->middleware('permission:weekly_wage_sheets.manage_adjustments')
        ->name('weekly-wage-sheets.adjustments.update');

    Route::post(
        '/weekly-wage-sheets/{weeklyWageSheet}/charges',
        [WeeklyWageSheetController::class, 'storeCharge']
    )
        ->middleware('permission:weekly_wage_sheets.manage_charges')
        ->name('weekly-wage-sheets.charges.store');

    Route::delete(
        '/weekly-wage-sheets/{weeklyWageSheet}/charges/{charge}',
        [WeeklyWageSheetController::class, 'destroyCharge']
    )
        ->middleware('permission:weekly_wage_sheets.manage_charges')
        ->name('weekly-wage-sheets.charges.destroy');

    Route::post(
        '/weekly-wage-sheets/{weeklyWageSheet}/submit',
        [WeeklyWageSheetController::class, 'submit']
    )
        ->middleware('permission:weekly_wage_sheets.submit')
        ->name('weekly-wage-sheets.submit');

    Route::post(
        '/weekly-wage-sheets/{weeklyWageSheet}/approve',
        [WeeklyWageSheetController::class, 'approve']
    )
        ->middleware('permission:weekly_wage_sheets.approve')
        ->name('weekly-wage-sheets.approve');

    Route::post(
        '/weekly-wage-sheets/{weeklyWageSheet}/reject',
        [WeeklyWageSheetController::class, 'reject']
    )
        ->middleware('permission:weekly_wage_sheets.reject')
        ->name('weekly-wage-sheets.reject');

    Route::post(
        '/weekly-wage-sheets/{weeklyWageSheet}/mark-paid',
        [WeeklyWageSheetController::class, 'markPaid']
    )
        ->middleware('permission:weekly_wage_sheets.mark_paid')
        ->name('weekly-wage-sheets.mark-paid');
});

Route::get(
    '/weekly-wage-sheets/{weeklyWageSheet}/export/excel',
    [WeeklyWageSheetController::class, 'exportExcel']
)
    ->middleware('permission:weekly_wage_sheets.export')
    ->name('weekly-wage-sheets.export-excel');

Route::get(
    '/weekly-wage-sheets/{weeklyWageSheet}/export/pdf',
    [WeeklyWageSheetController::class, 'exportPdf']
)
    ->middleware('permission:weekly_wage_sheets.export')
    ->name('weekly-wage-sheets.export-pdf');

/*
|--------------------------------------------------------------------------
| Weekly Labour Payment Register
|--------------------------------------------------------------------------
*/

Route::middleware([
    'permission:weekly_labour_payments.view',
])->group(function (): void {

    Route::get('/weekly-labour-payments', [WeeklyLabourPaymentController::class, 'index'])
        ->name('weekly-labour-payments.index');

    Route::get('/weekly-labour-payments/create', [WeeklyLabourPaymentController::class, 'create'])
        ->middleware('permission:weekly_labour_payments.create')
        ->name('weekly-labour-payments.create');

    Route::post('/weekly-labour-payments', [WeeklyLabourPaymentController::class, 'store'])
        ->middleware('permission:weekly_labour_payments.create')
        ->name('weekly-labour-payments.store');

    Route::get('/weekly-labour-payments/{weeklyLabourPayment}', [WeeklyLabourPaymentController::class, 'show'])
        ->name('weekly-labour-payments.show');

    Route::post('/weekly-labour-payments/{weeklyLabourPayment}/generate', [WeeklyLabourPaymentController::class, 'generate'])
        ->middleware('permission:weekly_labour_payments.calculate')
        ->name('weekly-labour-payments.generate');

    Route::put('/weekly-labour-payments/{weeklyLabourPayment}/adjustments', [WeeklyLabourPaymentController::class, 'updateAdjustments'])
        ->middleware('permission:weekly_labour_payments.manage_adjustments')
        ->name('weekly-labour-payments.adjustments.update');

    Route::post('/weekly-labour-payments/{weeklyLabourPayment}/submit', [WeeklyLabourPaymentController::class, 'submit'])
        ->middleware('permission:weekly_labour_payments.submit')
        ->name('weekly-labour-payments.submit');

    Route::post('/weekly-labour-payments/{weeklyLabourPayment}/approve', [WeeklyLabourPaymentController::class, 'approve'])
        ->middleware('permission:weekly_labour_payments.approve')
        ->name('weekly-labour-payments.approve');

    Route::post('/weekly-labour-payments/{weeklyLabourPayment}/reject', [WeeklyLabourPaymentController::class, 'reject'])
        ->middleware('permission:weekly_labour_payments.reject')
        ->name('weekly-labour-payments.reject');

    Route::post('/weekly-labour-payments/{weeklyLabourPayment}/mark-paid', [WeeklyLabourPaymentController::class, 'markPaid'])
        ->middleware('permission:weekly_labour_payments.mark_paid')
        ->name('weekly-labour-payments.mark-paid');

    Route::get('/weekly-labour-payments/{weeklyLabourPayment}/export/pdf', [WeeklyLabourPaymentController::class, 'exportPdf'])
        ->middleware('permission:weekly_labour_payments.export')
        ->name('weekly-labour-payments.export-pdf');
});

    /*
|--------------------------------------------------------------------------
| Gender Master
|--------------------------------------------------------------------------
*/

Route::resource('genders', GenderController::class)
    ->except(['destroy'])
    ->middleware('permission:labour_master_data.view');

Route::patch(
    '/genders/{gender}/toggle-status',
    [GenderController::class, 'toggleStatus']
)
    ->name('genders.toggle-status')
    ->middleware('permission:labour_master_data.manage');

    /*
|--------------------------------------------------------------------------
| Manpower Source Master
|--------------------------------------------------------------------------
*/

Route::resource('manpower-sources', ManpowerSourceController::class)
    ->except(['destroy'])
    ->middleware('permission:labour_master_data.view');

Route::patch(
    '/manpower-sources/{manpowerSource}/toggle-status',
    [ManpowerSourceController::class, 'toggleStatus']
)
    ->name('manpower-sources.toggle-status')
    ->middleware('permission:labour_master_data.manage');

    /*
|--------------------------------------------------------------------------
| Skill Category Master
|--------------------------------------------------------------------------
*/

Route::resource('skill-categories', SkillCategoryController::class)
    ->except(['destroy'])
    ->middleware('permission:labour_master_data.view');

Route::patch(
    '/skill-categories/{skillCategory}/toggle-status',
    [SkillCategoryController::class, 'toggleStatus']
)
    ->name('skill-categories.toggle-status')
    ->middleware('permission:labour_master_data.manage');

    /*
|--------------------------------------------------------------------------
| Designation Role Master
|--------------------------------------------------------------------------
*/

Route::resource('designation-roles', DesignationRoleController::class)
    ->except(['destroy'])
    ->middleware('permission:labour_master_data.view');

Route::patch(
    '/designation-roles/{designationRole}/toggle-status',
    [DesignationRoleController::class, 'toggleStatus']
)
    ->name('designation-roles.toggle-status')
    ->middleware('permission:labour_master_data.manage');

    /*
|--------------------------------------------------------------------------
| Working Status Master
|--------------------------------------------------------------------------
*/

Route::resource('working-statuses', WorkingStatusController::class)
    ->except(['destroy'])
    ->middleware('permission:labour_master_data.view');

Route::patch(
    '/working-statuses/{workingStatus}/toggle-status',
    [WorkingStatusController::class, 'toggleStatus']
)
    ->name('working-statuses.toggle-status')
    ->middleware('permission:labour_master_data.manage');

    /*
|--------------------------------------------------------------------------
| Shift Master
|--------------------------------------------------------------------------
*/

Route::resource('shifts', ShiftController::class)
    ->except(['destroy'])
    ->middleware('permission:labour_master_data.view');

Route::patch(
    '/shifts/{shift}/toggle-status',
    [ShiftController::class, 'toggleStatus']
)
    ->name('shifts.toggle-status')
    ->middleware('permission:labour_master_data.manage');

    /*
|--------------------------------------------------------------------------
| Labour Master
|--------------------------------------------------------------------------
*/

Route::get('/labours', [LabourController::class, 'index'])
    ->name('labours.index')
    ->middleware('permission:labour_masters.view');

Route::get('/labours/create', [LabourController::class, 'create'])
    ->name('labours.create')
    ->middleware('permission:labour_masters.create');

Route::post('/labours', [LabourController::class, 'store'])
    ->name('labours.store')
    ->middleware('permission:labour_masters.create');

Route::get('/labours/{labour}', [LabourController::class, 'show'])
    ->name('labours.show')
    ->middleware('permission:labour_masters.view');

Route::get('/labours/{labour}/edit', [LabourController::class, 'edit'])
    ->name('labours.edit')
    ->middleware('permission:labour_masters.edit');

Route::put('/labours/{labour}', [LabourController::class, 'update'])
    ->name('labours.update')
    ->middleware('permission:labour_masters.edit');

Route::patch('/labours/{labour}/toggle-status', [LabourController::class, 'toggleStatus'])
    ->name('labours.toggle-status')
    ->middleware('permission:labour_masters.toggle_status');

/*
|--------------------------------------------------------------------------
| Labour Master AJAX
|--------------------------------------------------------------------------
*/

Route::get(
    '/ajax/labour-categories/{labourCategory}/labour-types',
    [LabourController::class, 'labourTypes']
)
    ->name('ajax.labour-category.labour-types')
    ->middleware('permission:labour_masters.view');

Route::get(
    '/ajax/designation-roles',
    [LabourController::class, 'designationRoles']
)
    ->name('ajax.designation-roles')
    ->middleware('permission:labour_masters.view');


    /*
    |--------------------------------------------------------------------------
    | Unit & Brand Masters
    |--------------------------------------------------------------------------
    */

    Route::get('/unit-masters', [UnitMasterController::class, 'index'])
        ->name('unit-masters.index')
        ->middleware('permission:materials.view');

    Route::post('/unit-masters', [UnitMasterController::class, 'store'])
        ->name('unit-masters.store')
        ->middleware('permission:materials.view');

    Route::get('/unit-masters/{unitMaster}/edit', [UnitMasterController::class, 'edit'])
        ->name('unit-masters.edit')
        ->middleware('permission:materials.view');

    Route::put('/unit-masters/{unitMaster}', [UnitMasterController::class, 'update'])
        ->name('unit-masters.update')
        ->middleware('permission:materials.view');

    Route::patch('/unit-masters/{unitMaster}/toggle-status', [UnitMasterController::class, 'toggleStatus'])
        ->name('unit-masters.toggle-status')
        ->middleware('permission:materials.view');

    Route::get('/brand-masters', [BrandMasterController::class, 'index'])
        ->name('brand-masters.index')
        ->middleware('permission:materials.view');

    Route::post('/brand-masters', [BrandMasterController::class, 'store'])
        ->name('brand-masters.store')
        ->middleware('permission:materials.view');

    Route::get('/brand-masters/{brandMaster}/edit', [BrandMasterController::class, 'edit'])
        ->name('brand-masters.edit')
        ->middleware('permission:materials.view');

    Route::put('/brand-masters/{brandMaster}', [BrandMasterController::class, 'update'])
        ->name('brand-masters.update')
        ->middleware('permission:materials.view');

    Route::patch('/brand-masters/{brandMaster}/toggle-status', [BrandMasterController::class, 'toggleStatus'])
        ->name('brand-masters.toggle-status')
        ->middleware('permission:materials.view');


    /*
    |--------------------------------------------------------------------------
    | Activity Mapping
    |--------------------------------------------------------------------------
    */

    Route::get('/activity-mappings', [ActivityMappingController::class, 'index'])
        ->name('activity-mappings.index')
        ->middleware('permission:activity_mappings.view');

    Route::get('/activity-mappings/create', [ActivityMappingController::class, 'create'])
        ->name('activity-mappings.create')
        ->middleware('permission:activity_mappings.manage');

    Route::post('/activity-mappings', [ActivityMappingController::class, 'store'])
        ->name('activity-mappings.store')
        ->middleware('permission:activity_mappings.manage');

    Route::post('/activity-mappings/import', [ActivityMappingController::class, 'import'])
        ->name('activity-mappings.import')
        ->middleware('permission:activity_mappings.manage');

    Route::get('/activity-mappings/{activityMapping}/edit', [ActivityMappingController::class, 'edit'])
        ->name('activity-mappings.edit')
        ->middleware('permission:activity_mappings.manage');

    Route::put('/activity-mappings/{activityMapping}', [ActivityMappingController::class, 'update'])
        ->name('activity-mappings.update')
        ->middleware('permission:activity_mappings.manage');


    /*
    |--------------------------------------------------------------------------
    | DPR
    |--------------------------------------------------------------------------
    */

    Route::get(
    '/dprs/execution-data',
    [DprController::class, 'executionData']
)->name('dprs.execution-data');



    Route::resource('dprs', DprController::class)
        ->middleware('permission:dpr.view');

    Route::get('/dprs/{id}/pdf', [DprController::class, 'downloadPdf'])
        ->name('dprs.pdf')
        ->middleware('permission:dpr.view');

    Route::get('/pmo/dprs', [DprController::class, 'pmoQueue'])
        ->name('pmo.dprs')
        ->middleware('permission:dpr_reviews.view');

    Route::post('/dprs/{id}/approve', [DprController::class, 'approve'])
        ->name('dprs.approve')
        ->middleware('permission:dpr_reviews.approve');

    Route::post('/dprs/{id}/reject', [DprController::class, 'reject'])
        ->name('dprs.reject')
        ->middleware('permission:dpr_reviews.reject');

        

        /*
|--------------------------------------------------------------------------
| DPR Attendance AJAX
|--------------------------------------------------------------------------
*/

Route::get(
    '/ajax/dprs/labour-attendance',
    [DprController::class, 'labourAttendance']
)
    ->name('ajax.dprs.labour-attendance')
    ->middleware('permission:dpr.view');


    /*
    |--------------------------------------------------------------------------
    | Location AJAX
    |--------------------------------------------------------------------------
    */

    Route::get('/location/floors/{block}', [DprController::class, 'getFloors'])
        ->name('location.floors');

    Route::get('/location/units/{floor}', [DprController::class, 'getUnits'])
        ->name('location.units');

    Route::get('/location/rooms/{unit}', [DprController::class, 'getRooms'])
        ->name('location.rooms');

    Route::get('/location/subspaces/{room}', [DprController::class, 'getSubspaces'])
        ->name('location.subspaces');


    /*
    |--------------------------------------------------------------------------
    | Project Locations
    |--------------------------------------------------------------------------
    */

    Route::get('/project-locations', [ProjectLocationController::class, 'index'])
        ->name('project-locations.index')
        ->middleware('permission:location_masters.view');

    Route::post('/project-locations/blocks', [ProjectLocationController::class, 'storeBlock'])
        ->name('project-locations.blocks.store')
        ->middleware('permission:location_masters.manage');

    Route::post('/project-locations/floors', [ProjectLocationController::class, 'storeFloor'])
        ->name('project-locations.floors.store')
        ->middleware('permission:location_masters.manage');

    Route::post('/project-locations/units', [ProjectLocationController::class, 'storeUnit'])
        ->name('project-locations.units.store')
        ->middleware('permission:location_masters.manage');

    Route::post('/project-locations/rooms', [ProjectLocationController::class, 'storeRoom'])
        ->name('project-locations.rooms.store')
        ->middleware('permission:location_masters.manage');

    Route::post('/project-locations/subspaces', [ProjectLocationController::class, 'storeSubspace'])
        ->name('project-locations.subspaces.store')
        ->middleware('permission:location_masters.manage');

    Route::get('/project-locations/blocks/{projectBlock}/edit', [ProjectLocationController::class, 'editBlock'])
        ->name('project-locations.blocks.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/project-locations/blocks/{projectBlock}', [ProjectLocationController::class, 'updateBlock'])
        ->name('project-locations.blocks.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/project-locations/blocks/{projectBlock}/toggle-status', [ProjectLocationController::class, 'toggleBlockStatus'])
        ->name('project-locations.blocks.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/project-locations/floors/{projectFloor}/edit', [ProjectLocationController::class, 'editFloor'])
        ->name('project-locations.floors.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/project-locations/floors/{projectFloor}', [ProjectLocationController::class, 'updateFloor'])
        ->name('project-locations.floors.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/project-locations/floors/{projectFloor}/toggle-status', [ProjectLocationController::class, 'toggleFloorStatus'])
        ->name('project-locations.floors.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/project-locations/units/{projectUnit}/edit', [ProjectLocationController::class, 'editUnit'])
        ->name('project-locations.units.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/project-locations/units/{projectUnit}', [ProjectLocationController::class, 'updateUnit'])
        ->name('project-locations.units.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/project-locations/units/{projectUnit}/toggle-status', [ProjectLocationController::class, 'toggleUnitStatus'])
        ->name('project-locations.units.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/project-locations/rooms/{projectRoom}/edit', [ProjectLocationController::class, 'editRoom'])
        ->name('project-locations.rooms.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/project-locations/rooms/{projectRoom}', [ProjectLocationController::class, 'updateRoom'])
        ->name('project-locations.rooms.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/project-locations/rooms/{projectRoom}/toggle-status', [ProjectLocationController::class, 'toggleRoomStatus'])
        ->name('project-locations.rooms.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/project-locations/subspaces/{projectSubspace}/edit', [ProjectLocationController::class, 'editSubspace'])
        ->name('project-locations.subspaces.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/project-locations/subspaces/{projectSubspace}', [ProjectLocationController::class, 'updateSubspace'])
        ->name('project-locations.subspaces.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/project-locations/subspaces/{projectSubspace}/toggle-status', [ProjectLocationController::class, 'toggleSubspaceStatus'])
        ->name('project-locations.subspaces.toggle-status')
        ->middleware('permission:location_masters.manage');


    /*
    |--------------------------------------------------------------------------
    | Location Masters
    |--------------------------------------------------------------------------
    */

    Route::get('/location-masters', [LocationMasterController::class, 'index'])
        ->name('location-masters.index')
        ->middleware('permission:location_masters.view');

    Route::get('/location-block-masters', [LocationBlockMasterController::class, 'index'])
        ->name('location-block-masters.index')
        ->middleware('permission:location_masters.view');

    Route::post('/location-block-masters', [LocationBlockMasterController::class, 'store'])
        ->name('location-block-masters.store')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-block-masters/{locationBlockMaster}/edit', [LocationBlockMasterController::class, 'edit'])
        ->name('location-block-masters.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/location-block-masters/{locationBlockMaster}', [LocationBlockMasterController::class, 'update'])
        ->name('location-block-masters.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/location-block-masters/{locationBlockMaster}/toggle-status', [LocationBlockMasterController::class, 'toggleStatus'])
        ->name('location-block-masters.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-floor-masters', [LocationFloorMasterController::class, 'index'])
        ->name('location-floor-masters.index')
        ->middleware('permission:location_masters.view');

    Route::post('/location-floor-masters', [LocationFloorMasterController::class, 'store'])
        ->name('location-floor-masters.store')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-floor-masters/{locationFloorMaster}/edit', [LocationFloorMasterController::class, 'edit'])
        ->name('location-floor-masters.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/location-floor-masters/{locationFloorMaster}', [LocationFloorMasterController::class, 'update'])
        ->name('location-floor-masters.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/location-floor-masters/{locationFloorMaster}/toggle-status', [LocationFloorMasterController::class, 'toggleStatus'])
        ->name('location-floor-masters.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-unit-masters', [LocationUnitMasterController::class, 'index'])
        ->name('location-unit-masters.index')
        ->middleware('permission:location_masters.view');

    Route::post('/location-unit-masters', [LocationUnitMasterController::class, 'store'])
        ->name('location-unit-masters.store')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-unit-masters/{locationUnitMaster}/edit', [LocationUnitMasterController::class, 'edit'])
        ->name('location-unit-masters.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/location-unit-masters/{locationUnitMaster}', [LocationUnitMasterController::class, 'update'])
        ->name('location-unit-masters.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/location-unit-masters/{locationUnitMaster}/toggle-status', [LocationUnitMasterController::class, 'toggleStatus'])
        ->name('location-unit-masters.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-room-masters', [LocationRoomMasterController::class, 'index'])
        ->name('location-room-masters.index')
        ->middleware('permission:location_masters.view');

    Route::post('/location-room-masters', [LocationRoomMasterController::class, 'store'])
        ->name('location-room-masters.store')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-room-masters/{locationRoomMaster}/edit', [LocationRoomMasterController::class, 'edit'])
        ->name('location-room-masters.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/location-room-masters/{locationRoomMaster}', [LocationRoomMasterController::class, 'update'])
        ->name('location-room-masters.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/location-room-masters/{locationRoomMaster}/toggle-status', [LocationRoomMasterController::class, 'toggleStatus'])
        ->name('location-room-masters.toggle-status')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-subspace-masters', [LocationSubspaceMasterController::class, 'index'])
        ->name('location-subspace-masters.index')
        ->middleware('permission:location_masters.view');

    Route::post('/location-subspace-masters', [LocationSubspaceMasterController::class, 'store'])
        ->name('location-subspace-masters.store')
        ->middleware('permission:location_masters.manage');

    Route::get('/location-subspace-masters/{locationSubspaceMaster}/edit', [LocationSubspaceMasterController::class, 'edit'])
        ->name('location-subspace-masters.edit')
        ->middleware('permission:location_masters.manage');

    Route::put('/location-subspace-masters/{locationSubspaceMaster}', [LocationSubspaceMasterController::class, 'update'])
        ->name('location-subspace-masters.update')
        ->middleware('permission:location_masters.manage');

    Route::patch('/location-subspace-masters/{locationSubspaceMaster}/toggle-status', [LocationSubspaceMasterController::class, 'toggleStatus'])
        ->name('location-subspace-masters.toggle-status')
        ->middleware('permission:location_masters.manage');

        Route::get('/project-locations/{project}/wizard', [ProjectLocationController::class, 'wizard'])
    ->name('project-locations.wizard')
    ->middleware('permission:location_masters.manage');

Route::post('/project-locations/{project}/wizard/generate', [ProjectLocationController::class, 'generateWizard'])
    ->name('project-locations.wizard.generate')
    ->middleware('permission:location_masters.manage');

    Route::patch('/project-locations/floors/{projectFloor}/convert-usage', [ProjectLocationController::class, 'convertFloorUsage'])
    ->name('project-locations.floors.convert-usage')
    ->middleware('permission:location_masters.manage');


    /*
    |--------------------------------------------------------------------------
    | Labour Reports
    |--------------------------------------------------------------------------
    */

    Route::resource('labour-reports', LabourReportController::class)
        ->middleware('permission:labour_reports.view');

    Route::patch('/labour-reports/{labourReport}/submit', [LabourReportController::class, 'submit'])
        ->name('labour-reports.submit')
        ->middleware('permission:labour_reports.create');

    Route::patch('/labour-reports/{labourReport}/approve', [LabourReportController::class, 'approve'])
        ->name('labour-reports.approve')
        ->middleware('permission:labour_reports.approve');


    /*
    |--------------------------------------------------------------------------
    | Materials
    |--------------------------------------------------------------------------
    */

    Route::resource('material-received', MaterialReceivedController::class)
        ->middleware('permission:material_received.view');

    Route::patch('/material-received/{materialReceived}/submit', [MaterialReceivedController::class, 'submit'])
        ->name('material-received.submit')
        ->middleware('permission:material_received.create');

    Route::patch('/material-received/{materialReceived}/approve', [MaterialReceivedController::class, 'approve'])
        ->name('material-received.approve')
        ->middleware('permission:material_received.approve');

    Route::resource('material-consumed', MaterialConsumedController::class)
        ->middleware('permission:material_consumed.view');

    Route::patch('/material-consumed/{materialConsumed}/submit', [MaterialConsumedController::class, 'submit'])
        ->name('material-consumed.submit')
        ->middleware('permission:material_consumed.create');

    Route::patch('/material-consumed/{materialConsumed}/approve', [MaterialConsumedController::class, 'approve'])
        ->name('material-consumed.approve')
        ->middleware('permission:material_consumed.approve');

    Route::resource('material-requirements', MaterialRequirementController::class)
        ->middleware('permission:material_required.view');

    Route::patch('/material-requirements/{materialRequirement}/submit', [MaterialRequirementController::class, 'submit'])
        ->name('material-requirements.submit')
        ->middleware('permission:material_required.create');

    Route::patch('/material-requirements/{materialRequirement}/approve', [MaterialRequirementController::class, 'approve'])
        ->name('material-requirements.approve')
        ->middleware('permission:material_required.approve');

    Route::get('/stock-register', [StockRegisterController::class, 'index'])
        ->name('stock-register.index')
        ->middleware('permission:material_ledger.view');

    Route::get('/material-ledger', [MaterialLedgerController::class, 'index'])
        ->name('material-ledger.index')
        ->middleware('permission:material_ledger.view');

    Route::get('/material-shortage-report', [MaterialShortageReportController::class, 'index'])
        ->name('material-shortage-report.index')
        ->middleware('permission:material_shortage_report.view');


    /*
    |--------------------------------------------------------------------------
    | Planning
    |--------------------------------------------------------------------------
    */

    Route::get('/weekly-plans/progress-dashboard', [WeeklyPlanController::class, 'progressDashboard'])
        ->name('weekly-plans.progress-dashboard')
        ->middleware('permission:weekly_plans.view');

    Route::resource('weekly-plans', WeeklyPlanController::class)
        ->middleware('permission:weekly_plans.view');

    Route::get('/monthly-plans/progress-dashboard', [MonthlyPlanController::class, 'progressDashboard'])
        ->name('monthly-plans.progress-dashboard')
        ->middleware('permission:monthly_plans.view');

    Route::resource('monthly-plans', MonthlyPlanController::class)
        ->middleware('permission:monthly_plans.view');

    Route::resource('tomorrow-plans', TomorrowPlanController::class)
        ->middleware('permission:tomorrow_plans.view');

    Route::patch('/tomorrow-plans/{tomorrowPlan}/submit', [TomorrowPlanController::class, 'submit'])
        ->name('tomorrow-plans.submit')
        ->middleware('permission:tomorrow_plans.create');

    Route::patch('/tomorrow-plans/{tomorrowPlan}/approve', [TomorrowPlanController::class, 'approve'])
        ->name('tomorrow-plans.approve')
        ->middleware('permission:tomorrow_plans.approve');

    Route::get('/plan-vs-actual', [PlanVsActualController::class, 'index'])
        ->name('plan-vs-actual.index')
        ->middleware('permission:plan_vs_actual.view');

    Route::get('/projects/{project}/activity-progress', [ActivityProgressController::class, 'index'])
        ->name('activity-progress.index')
        ->middleware('permission:activity_progress.view');


    /*
    |--------------------------------------------------------------------------
    | Site Issues
    |--------------------------------------------------------------------------
    */

    Route::resource('site-issues', SiteIssueController::class)
        ->middleware('permission:site_issues.view');


    /*
    |--------------------------------------------------------------------------
    | PMO & Verification
    |--------------------------------------------------------------------------
    */

    Route::get('/material-verifications', [MaterialVerificationController::class, 'index'])
        ->name('material-verifications.index')
        ->middleware('permission:material_verification.view');

    Route::get('/material-verifications/{materialReceived}', [MaterialVerificationController::class, 'show'])
        ->name('material-verifications.show')
        ->middleware('permission:material_verification.view');

    Route::post('/material-verifications/{materialReceived}/verify', [MaterialVerificationController::class, 'verify'])
        ->name('material-verifications.verify')
        ->middleware('permission:material_verification.verify');

    Route::get('/mapping-pending-queue', [MappingPendingQueueController::class, 'index'])
        ->name('mapping-pending-queue.index')
        ->middleware('permission:mapping_queue.view');

    Route::get('/mapping-pending-queue/{dprWorkItem}/edit', [MappingPendingQueueController::class, 'edit'])
        ->name('mapping-pending-queue.edit')
        ->middleware('permission:mapping_queue.manage');

    Route::put('/mapping-pending-queue/{dprWorkItem}', [MappingPendingQueueController::class, 'update'])
        ->name('mapping-pending-queue.update')
        ->middleware('permission:mapping_queue.manage');


   

    /*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

Route::get('/users', [UserController::class, 'index'])
    ->name('users.index')
    ->middleware('permission:users.view');

Route::get('/users/create', [UserController::class, 'create'])
    ->name('users.create')
    ->middleware('permission:users.manage');

Route::post('/users/store', [UserController::class, 'store'])
    ->name('users.store')
    ->middleware('permission:users.manage');

Route::get('/users/{id}', [UserController::class, 'show'])
    ->whereNumber('id')
    ->name('users.show')
    ->middleware('permission:users.view');

Route::get('/users/{id}/edit', [UserController::class, 'edit'])
    ->whereNumber('id')
    ->name('users.edit')
    ->middleware('permission:users.manage');

Route::post('/users/{id}/update', [UserController::class, 'update'])
    ->whereNumber('id')
    ->name('users.update')
    ->middleware('permission:users.manage');

/*
|--------------------------------------------------------------------------
| User Password Management
|--------------------------------------------------------------------------
*/

Route::get('/users/{id}/password', [UserController::class, 'password'])
    ->whereNumber('id')
    ->name('users.password')
    ->middleware('permission:users.manage');

Route::post('/users/{id}/password', [UserController::class, 'updatePassword'])
    ->whereNumber('id')
    ->name('users.password.update')
    ->middleware('permission:users.manage');

/*
|--------------------------------------------------------------------------
| User Account Status Management
|--------------------------------------------------------------------------
*/

Route::post('/users/{id}/activate', [UserController::class, 'activate'])
    ->whereNumber('id')
    ->name('users.activate')
    ->middleware('permission:users.manage');

Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate'])
    ->whereNumber('id')
    ->name('users.deactivate')
    ->middleware('permission:users.manage');

Route::post('/users/{id}/suspend', [UserController::class, 'suspend'])
    ->whereNumber('id')
    ->name('users.suspend')
    ->middleware('permission:users.manage');

Route::post('/users/{id}/exit', [UserController::class, 'exitUser'])
    ->whereNumber('id')
    ->name('users.exit')
    ->middleware('permission:users.manage');

    /*
|--------------------------------------------------------------------------
| Department Master
|--------------------------------------------------------------------------
*/

Route::get('/departments', [DepartmentController::class, 'index'])
    ->name('departments.index')
    ->middleware('permission:departments.view');

Route::get('/departments/create', [DepartmentController::class, 'create'])
    ->name('departments.create')
    ->middleware('permission:departments.manage');

Route::post('/departments/store', [DepartmentController::class, 'store'])
    ->name('departments.store')
    ->middleware('permission:departments.manage');

Route::get('/departments/{id}', [DepartmentController::class, 'show'])
    ->whereNumber('id')
    ->name('departments.show')
    ->middleware('permission:departments.view');

Route::get('/departments/{id}/edit', [DepartmentController::class, 'edit'])
    ->whereNumber('id')
    ->name('departments.edit')
    ->middleware('permission:departments.manage');

Route::post('/departments/{id}/update', [DepartmentController::class, 'update'])
    ->whereNumber('id')
    ->name('departments.update')
    ->middleware('permission:departments.manage');

Route::post('/departments/{id}/activate', [DepartmentController::class, 'activate'])
    ->whereNumber('id')
    ->name('departments.activate')
    ->middleware('permission:departments.manage');

Route::post('/departments/{id}/deactivate', [DepartmentController::class, 'deactivate'])
    ->whereNumber('id')
    ->name('departments.deactivate')
    ->middleware('permission:departments.manage');


/*
|--------------------------------------------------------------------------
| Employee Designation Master
|--------------------------------------------------------------------------
*/

Route::get('/employee-designations', [EmployeeDesignationController::class, 'index'])
    ->name('employee-designations.index')
    ->middleware('permission:employee_designations.view');

Route::get('/employee-designations/create', [EmployeeDesignationController::class, 'create'])
    ->name('employee-designations.create')
    ->middleware('permission:employee_designations.manage');

Route::post('/employee-designations/store', [EmployeeDesignationController::class, 'store'])
    ->name('employee-designations.store')
    ->middleware('permission:employee_designations.manage');

Route::get('/employee-designations/{id}', [EmployeeDesignationController::class, 'show'])
    ->whereNumber('id')
    ->name('employee-designations.show')
    ->middleware('permission:employee_designations.view');

Route::get('/employee-designations/{id}/edit', [EmployeeDesignationController::class, 'edit'])
    ->whereNumber('id')
    ->name('employee-designations.edit')
    ->middleware('permission:employee_designations.manage');

Route::post('/employee-designations/{id}/update', [EmployeeDesignationController::class, 'update'])
    ->whereNumber('id')
    ->name('employee-designations.update')
    ->middleware('permission:employee_designations.manage');

Route::post('/employee-designations/{id}/activate', [EmployeeDesignationController::class, 'activate'])
    ->whereNumber('id')
    ->name('employee-designations.activate')
    ->middleware('permission:employee_designations.manage');

Route::post('/employee-designations/{id}/deactivate', [EmployeeDesignationController::class, 'deactivate'])
    ->whereNumber('id')
    ->name('employee-designations.deactivate')
    ->middleware('permission:employee_designations.manage');

     /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */
    Route::resource('roles', RoleController::class)
        ->middleware('permission:roles.view');

    Route::resource('permissions', PermissionController::class)
        ->middleware('permission:permissions.view');

    Route::get('/role-permissions', [RolePermissionController::class, 'index'])
        ->name('role-permissions.index')
        ->middleware('permission:role_permissions.view');

    Route::get('/role-permissions/{role}/edit', [RolePermissionController::class, 'edit'])
        ->name('role-permissions.edit')
        ->middleware('permission:role_permissions.manage');

    Route::put('/role-permissions/{role}', [RolePermissionController::class, 'update'])
        ->name('role-permissions.update')
        ->middleware('permission:role_permissions.manage');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index')
        ->middleware('permission:audit_trail.view');

    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])
        ->name('audit-logs.show')
        ->middleware('permission:audit_trail.view');
});

require __DIR__.'/auth.php';