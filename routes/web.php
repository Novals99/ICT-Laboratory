<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::get('/activity-log', [ActivityLogController::class, 'index']);
