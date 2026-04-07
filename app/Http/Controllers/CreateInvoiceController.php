<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CreateInvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    )
    {
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            $result = $this->invoiceService->createInvoice(
                clientId: $request->validated()['client_id'],
                items: $request->validated()['items']
            );

            return response()->json([
                'success' => true,
                'message' => 'Рахунок-фактуру створено',
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Помилка: ' . $e->getMessage(),
            ], 400);
        }
    }
}
