<?php

declare(strict_types=1);

use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\CreateInvoiceController;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Api Routes
|--------------------------------------------------------------------------
*/

// Api versioning (v1 = first version, allows backward compatibility)
// v1 is added to support Api versioning. When changes are needed, we can create v2
// while keeping v1 for existing clients. This is a standard Api development practice.
Route::prefix('v1')->group(function () {

    // Services
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [CreateInvoiceController::class, 'store']);


    // Success invoice create
    Route::post('/test/create-invoice', function () {
        try {
            DB::transaction(function () {
                // Данные для счета
                $clientId = 1;
                $items = [
                    ['service_id' => 1, 'quantity' => 2, 'unit_price' => 1000.00],
                    ['service_id' => 2, 'quantity' => 1, 'unit_price' => 5000.00],
                ];

                $total = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

                // Создаем счет
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

    // Rollback check with error
    Route::post('/test/create-invoice-error', function () {
        try {
            DB::transaction(function () {
                $clientId = 1;
                $items = [
                    ['service_id' => 999, 'quantity' => 1, 'unit_price' => 1000.00], // несуществующий service_id
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
                        'service_id' => $item['service_id'], // exception here
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

    //Transfers
    Route::post('/transfer', [TransferController::class, 'transfer']);

    //Dashboard
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
});

