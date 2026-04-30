<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Service\src\Interfaces\Http\Api\V1\ServiceController;

Route::prefix('v1')->middleware(['auth:api', 'role:ADMIN,MANAGER,USER'])->group(function () {
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
});
