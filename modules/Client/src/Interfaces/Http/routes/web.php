<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Modules\Client\src\Domain\Entities\Client;
use Modules\Client\src\Interfaces\Http\Controllers\ClientController;

Route::middleware(['auth', 'web.role:ADMIN,MANAGER'])->prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/{id}', [ClientController::class, 'show'])->name('clients.show');
});

Route::middleware(['auth', 'web.role:ADMIN'])->group(function () {
    Route::get('/test-clients', function () {
        return 'Кiлькiсть клiєнтiв: ' . Client::count();
    });

    // N+1 demo (только для разработки)
    Route::get('/clients-slow', function () {
        DB::enableQueryLog();
        $clients = Client::all();
        foreach ($clients as $client) {
            $client->accounts->count();
        }
        $queriesCount = count(DB::getQueryLog());
        return "Кількість запитів: " . $queriesCount . " (N+1 проблема)";
    });
});
