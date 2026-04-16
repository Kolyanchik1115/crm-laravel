<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Client\Interfaces\Http\Controllers\ClientController;

Route::group(['prefix' => 'clients'], function () {
    Route::get('/', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/{id}', [ClientController::class, 'show'])->name('clients.show');
});
