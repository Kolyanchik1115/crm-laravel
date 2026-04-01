<?php
declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Models\Client;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//TODO: Temporary route for CRM config (demo route)
Route::get('/crm-settings', function () {
    return [
        'default_currency' => config('crm.default_currency'),
        'min_transfer' => config('crm.min_transfer'),
        'max_transfer' => config('crm.max_transfer'),
    ];
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


// The right one is already exist /clients
// Route::get('/clients', [ClientController::class, 'index']);

// test get client route
Route::get('/test-client', function () {
    return 'Кiлькiсть клiєнтiв: ' . Client::count();
});

// test get account by id route
Route::get('/test-account/{id}', function ($id) {
    return Account::with('client')->find($id);
});

// Clients
Route::group(['prefix' => 'clients'], function () {
    Route::get('/', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/{id}', [ClientController::class, 'show'])->name('clients.show');
});

// Accounts
Route::group(['prefix' => 'accounts'], function () {
    Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/{id}', [AccountController::class, 'show'])->name('accounts.show');
});

//Transactions
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

//Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
