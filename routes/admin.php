<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DirectoryController;
use App\Http\Middleware\AuthenticateAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
    Route::middleware(AuthenticateAdmin::class)->group(function () {
        Route::get('/', [DirectoryController::class, 'index'])->name('index');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/directories/{directory}', [DirectoryController::class, 'index'])->name('directories.index');
        Route::post('/directories/{directory}', [DirectoryController::class, 'store'])->name('directories.store');
        Route::put('/directories/{directory}/{id}', [DirectoryController::class, 'update'])->whereNumber('id')->name('directories.update');
        Route::delete('/directories/{directory}/{id}', [DirectoryController::class, 'destroy'])->whereNumber('id')->name('directories.destroy');
    });
});
