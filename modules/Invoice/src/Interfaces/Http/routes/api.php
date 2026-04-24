<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoice\src\Domain\Entities\Invoice;
use Modules\Invoice\src\Domain\Entities\InvoiceItem;
use Modules\Invoice\src\Interfaces\Http\Api\V1\InvoiceController;

Route::prefix('v1')->group(function () {

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

});

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
