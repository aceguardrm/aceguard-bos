<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Services\BusinessHealthService;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {

    $client = Client::first();

    $health = null;

    if ($client) {
        $health = (new BusinessHealthService())->calculate($client);
    }

    return view('dashboard.index', compact('health'));

})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware(['auth'])->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Clients
    Route::resource('clients', ClientController::class);

});

require __DIR__.'/auth.php';
