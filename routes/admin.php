<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DirectoryController;
use App\Http\Middleware\AuthenticateAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
    Route::middleware(AuthenticateAdmin::class)->group(function () {
        Route::get('/sms', [\App\Http\Controllers\Admin\SmsController::class, 'index'])->name('sms.index');
        Route::get('/sms/create', [\App\Http\Controllers\Admin\SmsController::class, 'create'])->name('sms.create');
        Route::get('/sms/{id}/edit', [\App\Http\Controllers\Admin\SmsController::class, 'edit'])->whereNumber('id')->name('sms.edit');
        Route::post('/sms', [\App\Http\Controllers\Admin\SmsController::class, 'store'])->name('sms.store');
        Route::put('/sms/{id}', [\App\Http\Controllers\Admin\SmsController::class, 'update'])->name('sms.update');
        Route::delete('/sms/{id}', [\App\Http\Controllers\Admin\SmsController::class, 'destroy'])->name('sms.destroy');
        Route::get('/glonass', [\App\Http\Controllers\Admin\GlonassController::class, 'index'])->name('glonass.index');
        Route::get('/glonass/create', [\App\Http\Controllers\Admin\GlonassController::class, 'create'])->name('glonass.create');
        Route::get('/glonass/{id}/edit', [\App\Http\Controllers\Admin\GlonassController::class, 'edit'])->whereNumber('id')->name('glonass.edit');
        Route::post('/glonass', [\App\Http\Controllers\Admin\GlonassController::class, 'store'])->name('glonass.store');
        Route::put('/glonass/{id}', [\App\Http\Controllers\Admin\GlonassController::class, 'update'])->name('glonass.update');
        Route::delete('/glonass/{id}', [\App\Http\Controllers\Admin\GlonassController::class, 'destroy'])->name('glonass.destroy');
        Route::get('/', [DirectoryController::class, 'index'])->name('index');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/directories/{directory}', [DirectoryController::class, 'index'])->name('directories.index');
        Route::post('/directories/{directory}', [DirectoryController::class, 'store'])->name('directories.store');
        Route::put('/directories/{directory}/{id}', [DirectoryController::class, 'update'])->whereNumber('id')->name('directories.update');
        Route::delete('/directories/{directory}/{id}', [DirectoryController::class, 'destroy'])->whereNumber('id')->name('directories.destroy');
    });
});
