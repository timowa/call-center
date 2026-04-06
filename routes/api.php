<?php

use App\Http\Controllers\FireReportController;
use Illuminate\Support\Facades\Route;

Route::get('/dictionaries/get-fire-directories', [FireReportController::class, 'getFireDirectories'])->name('fire.get-directories');

