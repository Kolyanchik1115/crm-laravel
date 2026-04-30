<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API versioning (v1 = first version, allows backward compatibility)
Route::prefix('v1')->name('api.v1.')->group(function () {

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{id}/accounts', [ClientController::class, 'accounts'])->name('clients.accounts');

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{account}/transactions', [AccountController::class, 'transactions'])
        ->name('accounts.transactions');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

    //Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/{id}', [TransferController::class, 'show'])->name('transfers.show');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');

    // Test routes
    Route::get('/dashboard-stats', function () {
        $stats = Cache::get('crm:dashboard:stats');

        if (!$stats) {
            return response()->json([
                'status' => 'cache_miss',
                'message' => 'Cache is empty, job not executed yet',
            ]);
        }

        return response()->json([
            'status' => 'cache_hit',
            'data' => $stats,
        ]);
    });

    Route::post('/test/create-invoice', function () {
        try {
            DB::transaction(function () {
                $clientId = 1;
                $items = [
                    ['service_id' => 1, 'quantity' => 2, 'unit_price' => 1000.00],
                    ['service_id' => 2, 'quantity' => 1, 'unit_price' => 5000.00],
                ];

                $total = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

                $invoice = Invoice::create([
                    'client_id' => $clientId,
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                    'total_amount' => $total,
                    'status' => 'draft',
                    'issued_at' => now(),
                ]);

                foreach ($items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => $item['service_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Рахунок створено',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка: ' . $e->getMessage(),
            ], 400);
        }
    });

    Route::post('/test/create-invoice-error', function () {
        try {
            DB::transaction(function () {
                $clientId = 1;
                $items = [
                    ['service_id' => 999, 'quantity' => 1, 'unit_price' => 1000.00],
                ];

                $total = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

                $invoice = Invoice::create([
                    'client_id' => $clientId,
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                    'total_amount' => $total,
                    'status' => 'draft',
                    'issued_at' => now(),
                ]);

                foreach ($items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => $item['service_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Рахунок створено',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Транзакція відкотилася, дані не збережені',
                'error' => $e->getMessage(),
            ], 400);
        }
    });

    //Test sentry controller
    if (app()->environment('local', 'staging', 'development')) {
        Route::get('/test-sentry', function () {
            throw new \RuntimeException('Test Sentry integration - ' . now()->toDateTimeString());
        });
    }
});
