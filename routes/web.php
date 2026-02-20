<?php

use App\Http\Controllers\DistrictController;
use App\Http\Controllers\IncidentsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', fn () => redirect(route('incidents.dashboard')))->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/incidents/dashboard', [IncidentsController::class, 'dashboard'])->name('incidents.dashboard');
    Route::get('/incidents/create', [IncidentsController::class, 'create'])->name('incidents.create');
    Route::get('/incidents/edit/{id}', [IncidentsController::class, 'edit'])->name('incidents.edit');
    Route::put('/incidents/update/{id}', [IncidentsController::class, 'update'])->name('incidents.update');
});

require __DIR__.'/auth.php';
