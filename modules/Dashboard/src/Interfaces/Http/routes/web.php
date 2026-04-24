<?php

declare(strict_types=1);

use Modules\Dashboard\src\Interfaces\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
