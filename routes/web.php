<?php

use App\Http\Controllers\IncidentsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuggestionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(route('incidents.dashboard')));

Route::get('/dashboard', fn () => redirect(route('incidents.dashboard')))->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::post('/training/accept', [\App\Http\Controllers\TrainingController::class, 'accept'])->name('training.accept');
    Route::get('/training/{id}', [\App\Http\Controllers\TrainingController::class, 'show'])->name('training.show');
    Route::post('/training/{id}/reply', [\App\Http\Controllers\TrainingController::class, 'reply'])->name('training.reply');
    Route::post('/training/{id}/hangup', [\App\Http\Controllers\TrainingController::class, 'hangup'])->name('training.hangup');

    Route::get('/incidents/dashboard', [IncidentsController::class, 'dashboard'])->name('incidents.dashboard');

    Route::get('/incidents/create-instant', [IncidentsController::class, 'createInstant'])->name('incidents.create-instant');
    Route::get('/incidents/create-from-call', [IncidentsController::class, 'createFromCall'])->name('incidents.create-from-call');
    Route::get('/incidents/edit/{id}', [IncidentsController::class, 'edit'])->name('incidents.edit');
    Route::get('/incidents/view/{id}', [IncidentsController::class, 'view'])->name('incidents.view');

    Route::post('/incidents/store', [IncidentsController::class, 'store'])->name('incidents.store');

    Route::post('/incidents/{id}/department-transition', \App\Http\Controllers\DepartmentTransitionController::class)->name('incidents.department-transition');

    Route::put('/incidents/update/{id}', [IncidentsController::class, 'update'])->name('incidents.update');

    Route::post('/suggestions/get-address', [SuggestionsController::class, 'addresses'])->name('suggestions.addresses');
    Route::post('/suggestions/get-coordinates', [SuggestionsController::class, 'coordinates'])->name('suggestions.coordinates');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
