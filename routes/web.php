<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecurityWorkspaceController;
use App\Models\Client;
use App\Services\BusinessHealthService;
use App\Services\SecurityScoreService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $client = Client::first();

    $health = null;
    $security = null;

    if ($client) {
        $health = (new BusinessHealthService())->calculate($client);
        $security = (new SecurityScoreService())->calculate($client);
    }

    return view('dashboard.index', compact(
        'health',
        'security'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
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

    /*
    |--------------------------------------------------------------------------
    | Client Workspaces
    |--------------------------------------------------------------------------
    */

    Route::resource('clients', ClientController::class);

    /*
    |--------------------------------------------------------------------------
    | Client Security Workspace
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
});

require __DIR__.'/auth.php';