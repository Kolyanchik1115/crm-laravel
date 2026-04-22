<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CreateInvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            $dto = $request->toCreateInvoiceDTO();
            $result = $this->invoiceService->createInvoice($dto);

            return (new InvoiceResource($result))
                ->additional([
                    'success' => true,
                    'message' => 'Рахунок-фактуру створено',
                ])
                ->response()
                ->setStatusCode(201);

        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
