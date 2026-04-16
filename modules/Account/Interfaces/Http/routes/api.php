<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Account\Interfaces\Http\Api\AccountController;

Route::prefix('v1')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);
});
