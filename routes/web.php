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

// ── PUBLIC ROUTES ────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
});
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // User Management
    Route::get('/users/export/{format}', [UserController::class, 'export'])->name('users.export');
    Route::resource('users', UserController::class);

    // Laboratory Management
    Route::delete('/laboratory/bulk-destroy', [LaboratoryController::class, 'bulkDestroy'])->name('laboratory.bulkDestroy');
    Route::get('/laboratory/export/{format}', [LaboratoryController::class, 'export'])->name('laboratory.export');
    Route::resource('laboratory', LaboratoryController::class);

    // PC Management (Nested under Laboratory)
    Route::prefix('laboratory/{laboratory}/pc')->name('pc.')->group(function () {
        Route::post('/', [PcController::class, 'store'])->name('store');
        Route::put('/{pc}', [PcController::class, 'update'])->name('update');
        Route::delete('/{pc}', [PcController::class, 'destroy'])->name('destroy');
    });

    // Asset Management
    Route::get('/asset/export/{format}', [AssetController::class, 'export'])->name('asset.export');
    Route::resource('asset', AssetController::class);

    // Asset Logs & Transactions
    Route::get('/assetlog/export/{format}', [AssetLogController::class, 'export'])->name('assetlog.export');
    Route::resource('assetlog', AssetLogController::class)->only(['index', 'show']);

    Route::prefix('asset/{asset}/log')->name('assetlog.')->group(function () {
        Route::post('/stock-in', [AssetLogController::class, 'storeStockIn'])->name('stock-in');
        Route::post('/stock-out', [AssetLogController::class, 'storeStockOut'])->name('stock-out');
        Route::post('/transfer', [AssetLogController::class, 'storeTransfer'])->name('transfer');
        Route::post('/damaged', [AssetLogController::class, 'storeDamaged'])->name('damaged');
        Route::post('/lost', [AssetLogController::class, 'storeLost'])->name('lost');
        Route::post('/repaired', [AssetLogController::class, 'storeRepaired'])->name('repaired');
        Route::post('/adjustment', [AssetLogController::class, 'storeAdjustment'])->name('adjustment');
    });

    // Lab Requests (Permintaan Barang Baru ke Gudang)
    Route::prefix('requestlab')->name('requestlab.')->group(function () {
        Route::get('/', [RequestLabController::class, 'index'])->name('index');
        Route::post('/', [RequestLabController::class, 'store'])->name('store');
        Route::get('/export/{format}', [RequestLabController::class, 'export'])->name('export');
        Route::get('/{id}/detail', [RequestLabController::class, 'detail'])->name('detail');
        Route::get('/{id}/edit', [RequestLabController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RequestLabController::class, 'update'])->name('update');
        Route::delete('/{id}', [RequestLabController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [RequestLabController::class, 'updateStatus'])->name('status');
        Route::patch('/item/{itemId}/status', [RequestLabController::class, 'updateItemStatus'])->name('item.status');
    });

    // Basic Resources
    Route::resource('stafflab', StaffLabController::class);
    Route::resource('assetlab', AssetLabController::class);
    Route::resource('requestitem', RequestItemController::class);

    // Custom Asset Lab Actions
    Route::post('/laboratory/{laboratory}/assetlab/{assetId}/adjust', [AssetLabController::class, 'adjust'])->name('lab.assetlab.adjust');
    Route::delete('/laboratory/{laboratory}/assetlab/{assetId}', [AssetLabController::class, 'removeFromLab'])->name('lab.assetlab.remove');

    // System Activity Logs
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/activity-log/export', function () {
        return 'Export belum dibuat';
    })->name('activity-log.export');

    // Return Requests (Retur Lab ke Gudang)
    Route::prefix('return-requests')->name('return-requests.')->group(function () {
        Route::get('/', [ReturnRequestController::class, 'index'])->name('index');
        Route::get('/create', [ReturnRequestController::class, 'create'])->name('create');
        Route::post('/', [ReturnRequestController::class, 'store'])->name('store');
        Route::post('/quick', [ReturnRequestController::class, 'storeQuick'])->name('store-quick');

        Route::get('/{id}/detail', [ReturnRequestController::class, 'getDetail'])->name('detail');
        Route::post('/{id}/approve', [ReturnRequestController::class, 'approveViaModal'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('approve');
        Route::post('/{id}/reject',  [ReturnRequestController::class, 'rejectViaModal'])
            ->middleware(\App\Http\Middleware\EnsureSpv::class)
            ->name('reject');
        Route::get('/{returnRequest}', [ReturnRequestController::class, 'show'])->name('show');
        
        // Supervisor Only Actions
        Route::middleware(\App\Http\Middleware\EnsureSpv::class)->group(function () {
            Route::post('/{returnRequest}/approve', [ReturnRequestController::class, 'approve'])->name('approve');
            Route::post('/{returnRequest}/reject', [ReturnRequestController::class, 'reject'])->name('reject');
        });
    });

    // Transfer Requests (Mutasi Antar Lab)
    Route::prefix('transfer-requests')->name('transfer-requests.')->group(function () {
        Route::get('/', [TransferRequestController::class, 'index'])->name('index');
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
        Route::post('/', [TransferRequestController::class, 'store'])->name('store');
        Route::get('/{transferRequest}', [TransferRequestController::class, 'show'])->name('show');

        // Supervisor Only Actions
        Route::middleware(\App\Http\Middleware\EnsureSpv::class)->group(function () {
            Route::post('/{transferRequest}/approve', [TransferRequestController::class, 'approve'])->name('approve');
            Route::post('/{transferRequest}/reject', [TransferRequestController::class, 'reject'])->name('reject');
        });
    });

    // AJAX API Endpoints
    Route::get('/api/labs/{labId}/assets', [ReturnRequestController::class, 'getLabAssets'])->name('api.labs.assets');

    // ── SUPERVISOR ONLY (Laboratory Recycle Bin) ────────────────────────────────
    Route::middleware(\App\Http\Middleware\EnsureSpv::class)->group(function () {
        Route::get('/laboratory/recycle-bin', [LaboratoryController::class, 'recycleBin'])->name('laboratory.recycle-bin');
        Route::post('/laboratory/{id}/restore', [LaboratoryController::class, 'restore'])->name('laboratory.restore');
        Route::delete('/laboratory/{id}/force-delete', [LaboratoryController::class, 'forceDestroy'])->name('laboratory.forceDestroy');
    });

});

require __DIR__ . '/auth.php';