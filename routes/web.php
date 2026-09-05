<?php

use App\Http\Controllers\BusinessPulseController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\SecurityWorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/dashboard')->name('home');


/*
|--------------------------------------------------------------------------
| Authenticated BOS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Executive Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

});


/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Organisation Workspaces
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'clients',
        ClientController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'projects',
        ProjectController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Project Tasks / Milestones
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/projects/{project}/tasks',
        [ProjectTaskController::class, 'store']
    )->name('project-tasks.store');

    Route::patch(
        '/projects/{project}/tasks/{projectTask}',
        [ProjectTaskController::class, 'update']
    )->name('project-tasks.update');

    Route::patch(
        '/projects/{project}/tasks/{projectTask}/toggle',
        [ProjectTaskController::class, 'toggle']
    )->name('project-tasks.toggle');

    Route::delete(
        '/projects/{project}/tasks/{projectTask}',
        [ProjectTaskController::class, 'destroy']
    )->name('project-tasks.destroy');


    /*
    |--------------------------------------------------------------------------
    | Security Workspace
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/clients/{client}/security',
        [SecurityWorkspaceController::class, 'show']
    )->name('security.workspace');

    Route::patch(
        '/clients/{client}/security-controls/{securityControl}',
        [SecurityWorkspaceController::class, 'update']
    )->name('security.controls.update');


    /*
    |--------------------------------------------------------------------------
    | Business Pulse™ Workspace
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/clients/{client}/business-pulse',
        [BusinessPulseController::class, 'show']
    )->name('business-pulse.workspace');

    Route::patch(
        '/clients/{client}/business-pulse',
        [BusinessPulseController::class, 'update']
    )->name('business-pulse.update');

});

require __DIR__.'/auth.php';
