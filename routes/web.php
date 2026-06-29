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
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\LabourCategoryController;
use App\Http\Controllers\UnitMasterController;
use App\Http\Controllers\BrandMasterController;
use App\Http\Controllers\ActivityDivisionController;
use App\Http\Controllers\WorkStageController;

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
        ->middleware('permission:dashboard.view');

    Route::get('/engineer-dashboard', [DashboardController::class, 'engineer'])
        ->name('engineer-dashboard')
        ->middleware('permission:engineer_dashboard.view');

    Route::get('/pmo-dashboard', [DashboardController::class, 'pmo'])
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
    | Administration
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

    Route::get('/users/{id}/edit', [UserController::class, 'edit'])
        ->name('users.edit')
        ->middleware('permission:users.manage');

    Route::post('/users/{id}/update', [UserController::class, 'update'])
        ->name('users.update')
        ->middleware('permission:users.manage');

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