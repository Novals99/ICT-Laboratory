<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LabRequestPdfController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/labrequest/export', [LabRequestPdfController::class, 'export'])
    ->name('labrequest.export');