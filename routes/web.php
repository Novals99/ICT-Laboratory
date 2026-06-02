<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\PcController;
use App\Http\Controllers\RequestLabController;
use App\Http\Controllers\StaffLabController;
use App\Http\Controllers\AssetLabController;
use App\Http\Controllers\RequestItemController;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('laboratory', LaboratoryController::class);
    Route::resource('asset', AssetController::class);
    Route::resource('pc', PcController::class);
    Route::resource('requestlab', RequestLabController::class);
    Route::resource('stafflab', StaffLabController::class);
    Route::resource('assetlab', AssetLabController::class);
    Route::resource('requestitem', RequestItemController::class);
});



require __DIR__.'/auth.php';
