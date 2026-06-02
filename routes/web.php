<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\PcController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Laboratory
    Route::resource('laboratory', LaboratoryController::class);

    // PC 
    Route::post('/laboratory/{laboratory}/pc', [PcController::class, 'store'])->name('pc.store');
    Route::put('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'update'])->name('pc.update');
    Route::delete('/laboratory/{laboratory}/pc/{pc}', [PcController::class, 'destroy'])->name('pc.destroy');

    // Placeholder
    Route::get('/users', fn() => 'coming soon')->name('users.index');
    Route::get('/request-lab', fn() => 'coming soon')->name('requestlab.index');
    Route::get('/asset', fn() => 'coming soon')->name('asset.index');

});

require __DIR__.'/auth.php';
