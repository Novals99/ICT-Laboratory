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
use App\Http\Controllers\AssetLogController;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});


Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);

    // Laboratory
    Route::resource('laboratory', LaboratoryController::class);

    // PC — nested di bawah laboratory
    Route::post('/laboratory/{laboratory}/pc', [PcController::class, 'store'])->name('pc.store');
    Route::put('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'update'])->name('pc.update');
    Route::delete('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'destroy'])->name('pc.destroy');

    // Asset / Inventory
    Route::resource('asset', AssetController::class);
    //////////
    // Lab Request
    Route::get('/requestlab', [RequestLabController::class, 'index'])
        ->name('requestlab.index');

    // Store — simpan request baru
    Route::post('/requestlab', [RequestLabController::class, 'store'])
        ->name('requestlab.store');

    // Detail — return JSON untuk modal (AJAX)
    Route::get('/requestlab/{id}/detail', [RequestLabController::class, 'detail'])
        ->name('requestlab.detail');

    // Update Status — Approved / Rejected dari modal
    Route::patch('/requestlab/{id}/status', [RequestLabController::class, 'updateStatus'])
        ->name('requestlab.status');

    // Edit — form edit halaman terpisah
    Route::get('/requestlab/{id}/edit', [RequestLabController::class, 'edit'])
        ->name('requestlab.edit');

    // Update — simpan perubahan
    Route::put('/requestlab/{id}', [RequestLabController::class, 'update'])
        ->name('requestlab.update');

    // Destroy — hapus data
    Route::delete('/requestlab/{id}', [RequestLabController::class, 'destroy'])
        ->name('requestlab.destroy');

    Route::get('/requestlab/export/pdf', [RequestLabController::class, 'exportPdf'])
        ->name('requestlab.export.pdf');

    ////////////

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

require __DIR__ . '/auth.php';
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
    Route::delete('/laboratory/bulk-destroy', [App\Http\Controllers\LaboratoryController::class, 'bulkDestroy'])->name('laboratory.bulkDestroy');





require __DIR__.'/auth.php';
