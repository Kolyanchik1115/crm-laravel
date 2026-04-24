<?php

declare(strict_types=1);

use Modules\Client\src\Domain\Entities\Client;
use Modules\Client\src\Interfaces\Http\Controllers\ClientController;

Route::group(['prefix' => 'clients'], function () {
    Route::get('/', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/{id}', [ClientController::class, 'show'])->name('clients.show');
});

Route::get('/test-clients', function () {
    return 'Кiлькiсть клiєнтiв: ' . Client::count();
});

// Just for show bad variant (N+1)
Route::get('/clients-slow', function () {
    DB::enableQueryLog();

    $clients = Client::all(); // without with()

    foreach ($clients as $client) {
        $client->accounts->count(); // N+1 problem
    }

    $queries = DB::getQueryLog();
    $queriesCount = count($queries);

    return "Кількість запитів: " . $queriesCount . " (N+1 проблема)";
});
