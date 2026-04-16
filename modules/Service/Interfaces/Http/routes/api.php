<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Service\Interfaces\Http\Api\ServiceController;

Route::prefix('v1')->group(function () {
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
});
