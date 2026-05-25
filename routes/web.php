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
    ->middleware(['auth', 'role:PMO']);

    Route::post('/dprs/{id}/approve', [DprController::class, 'approve'])
    ->middleware(['auth', 'role:PMO']);

Route::post('/dprs/{id}/reject', [DprController::class, 'reject'])
    ->middleware(['auth', 'role:PMO']);
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

Route::resource(
    'weekly-plans',
    WeeklyPlanController::class
)->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

Route::get(
    '/weekly-plan-progress',
    [WeeklyPlanController::class, 'progressDashboard']
)
->name('weekly-plans.progress-dashboard')
->middleware([
    'auth',
    'role:Admin,PMO,DGM'
]);

    });

require __DIR__.'/auth.php';
