<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
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


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/admin-dashboard', [DashboardController::class, 'admin'])
    ->middleware(['auth', 'role:Admin']);

Route::get('/engineer-dashboard', [DashboardController::class, 'engineer'])
    ->middleware(['auth', 'role:Engineer']);

Route::get('/pmo-dashboard', [DashboardController::class, 'pmo'])
    ->middleware(['auth', 'role:PMO']);

Route::get('/ceo-dashboard', [DashboardController::class, 'ceo'])
    ->middleware(['auth', 'role:CEO']);

Route::get('/accountant-dashboard', [DashboardController::class, 'accountant'])
    ->middleware(['auth', 'role:Accountant']);

Route::resource('projects', ProjectController::class)
    ->middleware(['auth', 'role:Admin']);

Route::resource('activities', ActivityController::class)
    ->middleware(['auth', 'role:Admin']);

Route::resource('contractors', ContractorController::class)
    ->middleware(['auth', 'role:Admin']);

Route::resource('dprs', DprController::class)
    ->middleware(['auth']);

Route::get('/pmo/dprs', [DprController::class, 'pmoQueue'])
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/dprs/{id}/approve', [DprController::class, 'approve'])
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/dprs/{id}/reject', [DprController::class, 'reject'])
    ->middleware(['auth', 'role:Admin,PMO,DGM']);


    Route::get('/dprs/{id}/pdf', [DprController::class, 'downloadPdf']);
    Route::middleware(['auth', 'role:Admin'])->group(function () {

    Route::get('/users', [UserController::class, 'index']);

    Route::get('/users/create', [UserController::class, 'create']);

    Route::post('/users/store', [UserController::class, 'store']);

    Route::get('/users/{id}/edit', [UserController::class, 'edit']);

    Route::post('/users/{id}/update', [UserController::class, 'update']);
    Route::get('/project-progress', [ProjectController::class, 'progress'])
    ->middleware(['auth']);
    Route::get('/engineer-productivity', [DashboardController::class, 'engineerProductivity'])
    ->middleware(['auth']);
    Route::resource('labour-types', LabourTypeController::class)
    ->middleware(['auth', 'role:Admin']);
    Route::resource('materials', MaterialController::class )->middleware(['auth', 'role:Admin']);
    Route::resource('vendors', VendorController::class )->middleware(['auth', 'role:Admin']);
    Route::resource( 'machinery-tools', MachineryToolController::class
)->middleware(['auth', 'role:Admin']);

Route::get( '/weekly-plans/progress-dashboard',  [WeeklyPlanController::class, 'progressDashboard']
) ->name('weekly-plans.progress-dashboard') ->middleware([ 'auth', 'role:Admin,PMO,DGM' ]);

Route::resource('weekly-plans', WeeklyPlanController::class )->middleware([ 'auth', 'role:Admin,PMO,DGM'
]);



Route::get('/activity-mappings', [ActivityMappingController::class, 'index'])
    ->name('activity-mappings.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/activity-mappings/import', [ActivityMappingController::class, 'import'])
    ->name('activity-mappings.import')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::get('/activity-mappings/{activityMapping}/edit', [ActivityMappingController::class, 'edit'])
    ->name('activity-mappings.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/activity-mappings/{activityMapping}', [ActivityMappingController::class, 'update'])
    ->name('activity-mappings.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/activity-mappings/create', [ActivityMappingController::class, 'create'])
    ->name('activity-mappings.create')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/activity-mappings', [ActivityMappingController::class, 'store'])
    ->name('activity-mappings.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/location/floors/{block}', [DprController::class, 'getFloors'])
    ->name('location.floors')
    ->middleware(['auth']);

Route::get('/location/units/{floor}', [DprController::class, 'getUnits'])
    ->name('location.units')
    ->middleware(['auth']);

Route::get('/location/rooms/{unit}', [DprController::class, 'getRooms'])
    ->name('location.rooms')
    ->middleware(['auth']);

Route::get('/location/subspaces/{room}', [DprController::class, 'getSubspaces'])
    ->name('location.subspaces')
    ->middleware(['auth']);

    Route::get('/project-locations', [ProjectLocationController::class, 'index'])
    ->name('project-locations.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::post('/project-locations/blocks', [ProjectLocationController::class, 'storeBlock'])
    ->name('project-locations.blocks.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::post('/project-locations/floors', [ProjectLocationController::class, 'storeFloor'])
    ->name('project-locations.floors.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::post('/project-locations/units', [ProjectLocationController::class, 'storeUnit'])
    ->name('project-locations.units.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::post('/project-locations/rooms', [ProjectLocationController::class, 'storeRoom'])
    ->name('project-locations.rooms.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::post('/project-locations/subspaces', [ProjectLocationController::class, 'storeSubspace'])
    ->name('project-locations.subspaces.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/location-block-masters', [LocationBlockMasterController::class, 'index'])
    ->name('location-block-masters.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/location-block-masters', [LocationBlockMasterController::class, 'store'])
    ->name('location-block-masters.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/location-block-masters/{locationBlockMaster}/edit', [LocationBlockMasterController::class, 'edit'])
    ->name('location-block-masters.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/location-block-masters/{locationBlockMaster}', [LocationBlockMasterController::class, 'update'])
    ->name('location-block-masters.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/location-block-masters/{locationBlockMaster}/toggle-status', [LocationBlockMasterController::class, 'toggleStatus'])
    ->name('location-block-masters.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/location-floor-masters', [LocationFloorMasterController::class, 'index'])
    ->name('location-floor-masters.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/location-floor-masters', [LocationFloorMasterController::class, 'store'])
    ->name('location-floor-masters.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::get('/location-floor-masters/{locationFloorMaster}/edit', [LocationFloorMasterController::class, 'edit'])
    ->name('location-floor-masters.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/location-floor-masters/{locationFloorMaster}', [LocationFloorMasterController::class, 'update'])
    ->name('location-floor-masters.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/location-floor-masters/{locationFloorMaster}/toggle-status', [LocationFloorMasterController::class, 'toggleStatus'])
    ->name('location-floor-masters.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/location-unit-masters', [LocationUnitMasterController::class, 'index'])
    ->name('location-unit-masters.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/location-unit-masters', [LocationUnitMasterController::class, 'store'])
    ->name('location-unit-masters.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::get('/location-unit-masters/{locationUnitMaster}/edit', [LocationUnitMasterController::class, 'edit'])
    ->name('location-unit-masters.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/location-unit-masters/{locationUnitMaster}', [LocationUnitMasterController::class, 'update'])
    ->name('location-unit-masters.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/location-unit-masters/{locationUnitMaster}/toggle-status', [LocationUnitMasterController::class, 'toggleStatus'])
    ->name('location-unit-masters.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);
    Route::get('/location-room-masters', [LocationRoomMasterController::class, 'index'])
    ->name('location-room-masters.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/location-room-masters', [LocationRoomMasterController::class, 'store'])
    ->name('location-room-masters.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::get('/location-room-masters/{locationRoomMaster}/edit', [LocationRoomMasterController::class, 'edit'])
    ->name('location-room-masters.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/location-room-masters/{locationRoomMaster}', [LocationRoomMasterController::class, 'update'])
    ->name('location-room-masters.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/location-room-masters/{locationRoomMaster}/toggle-status', [LocationRoomMasterController::class, 'toggleStatus'])
    ->name('location-room-masters.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/location-subspace-masters', [LocationSubspaceMasterController::class, 'index'])
    ->name('location-subspace-masters.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::post('/location-subspace-masters', [LocationSubspaceMasterController::class, 'store'])
    ->name('location-subspace-masters.store')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::get('/location-subspace-masters/{locationSubspaceMaster}/edit', [LocationSubspaceMasterController::class, 'edit'])
    ->name('location-subspace-masters.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/location-subspace-masters/{locationSubspaceMaster}', [LocationSubspaceMasterController::class, 'update'])
    ->name('location-subspace-masters.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/location-subspace-masters/{locationSubspaceMaster}/toggle-status', [LocationSubspaceMasterController::class, 'toggleStatus'])
    ->name('location-subspace-masters.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/project-locations/blocks/{projectBlock}/edit', [ProjectLocationController::class, 'editBlock'])
    ->name('project-locations.blocks.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/project-locations/blocks/{projectBlock}', [ProjectLocationController::class, 'updateBlock'])
    ->name('project-locations.blocks.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/project-locations/blocks/{projectBlock}/toggle-status', [ProjectLocationController::class, 'toggleBlockStatus'])
    ->name('project-locations.blocks.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/project-locations/floors/{projectFloor}/edit', [ProjectLocationController::class, 'editFloor'])
    ->name('project-locations.floors.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/project-locations/floors/{projectFloor}', [ProjectLocationController::class, 'updateFloor'])
    ->name('project-locations.floors.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/project-locations/floors/{projectFloor}/toggle-status', [ProjectLocationController::class, 'toggleFloorStatus'])
    ->name('project-locations.floors.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/project-locations/units/{projectUnit}/edit', [ProjectLocationController::class, 'editUnit'])
    ->name('project-locations.units.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/project-locations/units/{projectUnit}', [ProjectLocationController::class, 'updateUnit'])
    ->name('project-locations.units.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/project-locations/units/{projectUnit}/toggle-status', [ProjectLocationController::class, 'toggleUnitStatus'])
    ->name('project-locations.units.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/project-locations/rooms/{projectRoom}/edit', [ProjectLocationController::class, 'editRoom'])
    ->name('project-locations.rooms.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/project-locations/rooms/{projectRoom}', [ProjectLocationController::class, 'updateRoom'])
    ->name('project-locations.rooms.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/project-locations/rooms/{projectRoom}/toggle-status', [ProjectLocationController::class, 'toggleRoomStatus'])
    ->name('project-locations.rooms.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/project-locations/subspaces/{projectSubspace}/edit', [ProjectLocationController::class, 'editSubspace'])
    ->name('project-locations.subspaces.edit')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::put('/project-locations/subspaces/{projectSubspace}', [ProjectLocationController::class, 'updateSubspace'])
    ->name('project-locations.subspaces.update')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::patch('/project-locations/subspaces/{projectSubspace}/toggle-status', [ProjectLocationController::class, 'toggleSubspaceStatus'])
    ->name('project-locations.subspaces.toggle-status')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::get('/location-masters', [LocationMasterController::class, 'index'])
    ->name('location-masters.index')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::middleware(['auth'])->group(function () {
    Route::resource('labour-reports', LabourReportController::class);

    Route::patch('/labour-reports/{labourReport}/submit', [LabourReportController::class, 'submit'])
    ->name('labour-reports.submit')
    ->middleware(['auth']);

    Route::patch('/labour-reports/{labourReport}/approve', [LabourReportController::class, 'approve'])
    ->name('labour-reports.approve')
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::middleware(['auth'])->group(function () {

    Route::resource('material-received', MaterialReceivedController::class);

    Route::patch('/material-received/{materialReceived}/submit', [MaterialReceivedController::class, 'submit'])
        ->name('material-received.submit');

    Route::patch('/material-received/{materialReceived}/approve', [MaterialReceivedController::class, 'approve'])
        ->name('material-received.approve')
        ->middleware(['role:Admin,PMO,DGM']);

        Route::resource('material-categories', MaterialCategoryController::class)
    ->middleware(['auth', 'role:Admin,PMO,DGM']);

    Route::resource('material-consumed', MaterialConsumedController::class);

Route::patch('/material-consumed/{materialConsumed}/submit', [MaterialConsumedController::class, 'submit'])
    ->name('material-consumed.submit');

Route::patch('/material-consumed/{materialConsumed}/approve', [MaterialConsumedController::class, 'approve'])
    ->name('material-consumed.approve')
    ->middleware(['role:Admin,PMO,DGM']);
    Route::get('/stock-register', [StockRegisterController::class, 'index'])
    ->name('stock-register.index');

    Route::get('/material-ledger', [MaterialLedgerController::class, 'index'])
    ->name('material-ledger.index');

    Route::resource('material-requirements', MaterialRequirementController::class);

Route::patch('/material-requirements/{materialRequirement}/submit',
    [MaterialRequirementController::class, 'submit'])
    ->name('material-requirements.submit');

Route::patch('/material-requirements/{materialRequirement}/approve',
    [MaterialRequirementController::class, 'approve'])
    ->name('material-requirements.approve');

    Route::get('/material-shortage-report', [MaterialShortageReportController::class, 'index'])
    ->name('material-shortage-report.index');

    Route::resource('tomorrow-plans', TomorrowPlanController::class);

Route::patch('/tomorrow-plans/{tomorrowPlan}/submit',
    [TomorrowPlanController::class, 'submit'])
    ->name('tomorrow-plans.submit');

Route::patch('/tomorrow-plans/{tomorrowPlan}/approve',
    [TomorrowPlanController::class, 'approve'])
    ->name('tomorrow-plans.approve');
});

Route::resource('site-issues', SiteIssueController::class);
Route::get('/plan-vs-actual', [PlanVsActualController::class, 'index'])
    ->name('plan-vs-actual.index');

    Route::get('/monthly-plans/progress-dashboard', [MonthlyPlanController::class, 'progressDashboard']
)->name('monthly-plans.progress-dashboard') ->middleware(['auth', 'role:Admin,PMO,DGM']);

Route::resource('monthly-plans', MonthlyPlanController::class) ->middleware([ 'auth', 'role:Admin,PMO,DGM'
    ]);

    Route::get(
    '/material-verifications',
    [MaterialVerificationController::class, 'index']
)->name('material-verifications.index')
 ->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

Route::get(
    '/material-verifications/{materialReceived}',
    [MaterialVerificationController::class, 'show']
)->name('material-verifications.show')
 ->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

Route::post(
    '/material-verifications/{materialReceived}/verify',
    [MaterialVerificationController::class, 'verify']
)->name('material-verifications.verify')
 ->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

Route::get(
    '/mapping-pending-queue',
    [MappingPendingQueueController::class, 'index']
)->name('mapping-pending-queue.index')
 ->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

Route::get(
    '/mapping-pending-queue/{dprWorkItem}/edit',
    [MappingPendingQueueController::class, 'edit']
)->name('mapping-pending-queue.edit')
 ->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

Route::put(
    '/mapping-pending-queue/{dprWorkItem}',
    [MappingPendingQueueController::class, 'update']
)->name('mapping-pending-queue.update')
 ->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

Route::get('/project-progress-dashboard', [ProjectProgressDashboardController::class, 'index'])
    ->name('project-progress-dashboard.index');

    Route::get('/project-dashboard/{project}', [ProjectDashboardController::class, 'show'])
    ->name('project-dashboard.show');

    Route::get(
    '/projects/{project}/activity-progress',
    [ActivityProgressController::class, 'index']
)->name('activity-progress.index');

});


    });



require __DIR__.'/auth.php';
