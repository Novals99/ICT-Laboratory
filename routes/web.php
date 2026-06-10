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
use App\Http\Controllers\AssetLogController;



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


    Route::resource('assetlog', AssetLogController::class)
        ->only(['index', 'show']);

    Route::prefix('asset/{asset}/log')->name('assetlog.')->group(function () {
        Route::post('/stock-in', [AssetLogController::class, 'storeStockIn'])->name('stock-in');
        Route::post('/stock-out', [AssetLogController::class, 'storeStockOut'])->name('stock-out');
        Route::post('/transfer', [AssetLogController::class, 'storeTransfer'])->name('transfer');
        Route::post('/damaged', [AssetLogController::class, 'storeDamaged'])->name('damaged');
        Route::post('/lost', [AssetLogController::class, 'storeLost'])->name('lost');
        Route::post('/repaired', [AssetLogController::class, 'storeRepaired'])->name('repaired');
        Route::post('/adjustment', [AssetLogController::class, 'storeAdjustment'])->name('adjustment');
    });
});



require __DIR__.'/auth.php';
