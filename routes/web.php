<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\PcController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\RequestLabController;
use App\Http\Controllers\StaffLabController;
use App\Http\Controllers\AssetLabController;
use App\Http\Controllers\RequestItemController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Users
    Route::resource('users', UserController::class);

    // Laboratory
    Route::resource('laboratory', LaboratoryController::class);

    // PC — nested di bawah laboratory
    Route::post('/laboratory/{laboratory}/pc', [PcController::class, 'store'])->name('pc.store');
    Route::put('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'update'])->name('pc.update');
    Route::delete('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'destroy'])->name('pc.destroy');

    // Asset / Inventory
    Route::resource('asset', AssetController::class);

    // Lab Request
    Route::resource('requestlab', RequestLabController::class);
    Route::resource('requestitem', RequestItemController::class);

    // Pivot tables
    Route::resource('stafflab', StaffLabController::class);
    Route::resource('assetlab', AssetLabController::class);
    
    // PC nested
    Route::post('/laboratory/{laboratory}/pc', [PcController::class, 'store'])->name('pc.store');
    Route::put('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'update'])->name('pc.update');
    Route::delete('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'destroy'])->name('pc.destroy');

    // Asset Lab adjustment
    Route::post('/laboratory/{laboratory}/assetlab/{assetId}/adjust', [AssetLabController::class, 'adjust'])->name('lab.assetlab.adjust');
    Route::delete('/laboratory/{laboratory}/assetlab/{assetId}', [AssetLabController::class, 'removeFromLab'])->name('lab.assetlab.remove');
    });

require __DIR__.'/auth.php';
