<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\PcController;
use App\Http\Controllers\RequestItemController;
use App\Http\Controllers\RequestLabController;
use App\Http\Controllers\StaffLabController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetLabController;
use App\Http\Controllers\AssetLogController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ReturnRequestController;
use App\Http\Controllers\TransferRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::get('/users/export/{format}', [UserController::class, 'export'])->name('users.export');
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(\App\Http\Middleware\EnsureSpv::class)->group(function () {
    Route::get('/laboratory/recycle-bin', [LaboratoryController::class, 'recycleBin'])->name('laboratory.recycle-bin');
    Route::post('/laboratory/{id}/restore', [LaboratoryController::class, 'restore'])->name('laboratory.restore');
    Route::delete('/laboratory/{id}/force-delete', [LaboratoryController::class, 'forceDestroy'])->name('laboratory.forceDestroy');
});
    Route::delete('/laboratory/bulk-destroy', [LaboratoryController::class, 'bulkDestroy'])->name('laboratory.bulkDestroy');

    Route::resource('laboratory', LaboratoryController::class);
    Route::get('/laboratory/export/{format}', [LaboratoryController::class, 'export'])->name('laboratory.export');

    Route::post('/laboratory/{laboratory}/pc', [PcController::class, 'store'])->name('pc.store');
    Route::put('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'update'])->name('pc.update');
    Route::delete('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'destroy'])->name('pc.destroy');
    Route::resource('asset', AssetController::class);
    Route::get('/asset/export/{format}', [AssetController::class, 'export'])->name('asset.export');

    //////////
    // Lab Request
    Route::get('/requestlab', [RequestLabController::class, 'index'])
        ->name('requestlab.index');
    Route::post('/requestlab', [RequestLabController::class, 'store'])
        ->name('requestlab.store');
    Route::get('/requestlab/{id}/detail', [RequestLabController::class, 'detail'])
        ->name('requestlab.detail');
    Route::patch('/requestlab/{id}/status', [RequestLabController::class, 'updateStatus'])
        ->name('requestlab.status');
    Route::patch('/requestlab/item/{itemId}/status', [RequestLabController::class, 'updateItemStatus'])
        ->name('requestlab.item.status');
    Route::get('/requestlab/{id}/edit', [RequestLabController::class, 'edit'])
        ->name('requestlab.edit');
    Route::put('/requestlab/{id}', [RequestLabController::class, 'update'])
        ->name('requestlab.update');
    Route::delete('/requestlab/{id}', [RequestLabController::class, 'destroy'])
        ->name('requestlab.destroy');
    Route::get('/requestlab/export/{format}', [RequestLabController::class, 'export'])
        ->name('requestlab.export');

    Route::resource('stafflab', StaffLabController::class);
    Route::resource('assetlab', AssetLabController::class);

    Route::post('/laboratory/{laboratory}/assetlab/{assetId}/adjust', [AssetLabController::class, 'adjust'])
        ->name('lab.assetlab.adjust');
    Route::delete('/laboratory/{laboratory}/assetlab/{assetId}', [AssetLabController::class, 'removeFromLab'])
        ->name('lab.assetlab.remove');

    Route::resource('requestitem', RequestItemController::class);

    Route::resource('assetlog', AssetLogController::class)
        ->only(['index', 'show']);
    Route::get('/assetlog/export/{format}', [AssetLogController::class, 'export'])->name('assetlog.export');

    Route::prefix('asset/{asset}/log')->name('assetlog.')->group(function () {
        Route::post('/stock-in', [AssetLogController::class, 'storeStockIn'])->name('stock-in');
        Route::post('/stock-out', [AssetLogController::class, 'storeStockOut'])->name('stock-out');
        Route::post('/transfer', [AssetLogController::class, 'storeTransfer'])->name('transfer');
        Route::post('/damaged', [AssetLogController::class, 'storeDamaged'])->name('damaged');

        Route::post('/lost', [AssetLogController::class, 'storeLost'])->name('lost');
        Route::post('/repaired', [AssetLogController::class, 'storeRepaired'])->name('repaired');
        Route::post('/adjustment', [AssetLogController::class, 'storeAdjustment'])->name('adjustment');
    });
    Route::get('/activity-log', [ActivityLogController::class, 'index'])
        ->name('activity-log.index');

    Route::get('/activity-log/export', function () {
        return 'Export belum dibuat';
    })->name('activity-log.export');
    // ── RETURN REQUESTS (Retur Lab → Gudang) ─────────────────────────────────────

    Route::prefix('return-requests')->name('return-requests.')->group(function () {

        Route::get('/',       [ReturnRequestController::class, 'index'])->name('index');
        Route::get('/create', [ReturnRequestController::class, 'create'])->name('create');
        Route::post('/',      [ReturnRequestController::class, 'store'])->name('store');
        Route::post('/quick', [ReturnRequestController::class, 'storeQuick'])->name('store-quick');

        Route::get('/{id}/detail', [ReturnRequestController::class, 'getDetail'])->name('detail');
        Route::post('/{id}/approve', [ReturnRequestController::class, 'approveViaModal'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('approve');
        Route::post('/{id}/reject',  [ReturnRequestController::class, 'rejectViaModal'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('reject');

        Route::get('/{returnRequest}',         [ReturnRequestController::class, 'show'])->name('show');
        Route::post('/{returnRequest}/approve', [ReturnRequestController::class, 'approve'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('approve');
        Route::post('/{returnRequest}/reject',  [ReturnRequestController::class, 'reject'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('reject');
    });

    // ── ENDPOINT AJAX — Ambil aset di lab (dipakai kedua form) ───────────────────
    // Diletakkan di luar prefix agar bisa diakses dari form transfer maupun return.
    // Jika ingin lebih rapi, pisah ke routes/api.php.

    Route::get('/api/labs/{labId}/assets', [ReturnRequestController::class, 'getLabAssets'])
        ->name('api.labs.assets');

    // ── TRANSFER REQUESTS (Mutasi Antar Lab) ─────────────────────────────────────

    Route::prefix('transfer-requests')->name('transfer-requests.')->group(function () {

        Route::get('/',       [TransferRequestController::class, 'index'])->name('index');
        Route::get('/create', [TransferRequestController::class, 'create'])->name('create');
        Route::post('/',      [TransferRequestController::class, 'store'])->name('store');

        Route::get('/{id}/detail', [TransferRequestController::class, 'getDetail'])->name('detail');
        Route::post('/{id}/approve', [TransferRequestController::class, 'approveViaModal'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('approve');
        Route::post('/{id}/reject',   [TransferRequestController::class, 'rejectViaModal'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('reject');

        Route::get('/{transferRequest}',          [TransferRequestController::class, 'show'])->name('show');
    });
require __DIR__ . '/auth.php';
