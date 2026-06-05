<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::get('/activity-log', [ActivityLogController::class, 'index'])
    ->name('activity-log.index');

Route::get('/activity-log/export', function () {
    return 'Export belum dibuat';
})->name('activity-log.export');