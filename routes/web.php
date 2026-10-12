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
    \App\Http\Middleware\AuthorizeWorkspace::class,
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

Route::middleware(['auth', \App\Http\Middleware\AuthorizeWorkspace::class])->group(function () {

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


Route::middleware(['auth', \App\Http\Middleware\AuthorizeWorkspace::class])
    ->group(function () {
        Route::get('/clients/{client}/members',
            [\App\Http\Controllers\WorkspaceMemberController::class, 'index'])
            ->name('clients.members.index');

        Route::patch('/clients/{client}/members/{member}',
            [\App\Http\Controllers\WorkspaceMemberController::class, 'update'])
            ->name('clients.members.update');

        Route::delete('/clients/{client}/members/{member}',
            [\App\Http\Controllers\WorkspaceMemberController::class, 'destroy'])
            ->name('clients.members.destroy');
    });


Route::middleware(['auth', \App\Http\Middleware\AuthorizeWorkspace::class])
    ->group(function () {
        Route::post('/clients/{client}/invitations',
            [\App\Http\Controllers\WorkspaceInvitationController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('clients.invitations.store');

        Route::delete('/clients/{client}/invitations/{invitation}',
            [\App\Http\Controllers\WorkspaceInvitationController::class, 'destroy'])
            ->name('clients.invitations.destroy');
    });

Route::get('/workspace-invitations/{token}',
    [\App\Http\Controllers\WorkspaceInvitationAcceptanceController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('workspace-invitations.show');

Route::post('/workspace-invitations/{token}',
    [\App\Http\Controllers\WorkspaceInvitationAcceptanceController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('workspace-invitations.accept');

require __DIR__.'/auth.php';
