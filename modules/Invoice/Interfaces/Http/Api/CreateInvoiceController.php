<?php

declare(strict_types=1);

namespace Modules\Invoice\Interfaces\Http\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Invoice\Application\Services\InvoiceService;
use Modules\Invoice\Interfaces\Http\Requests\StoreInvoiceRequest;
use Modules\Invoice\Interfaces\Http\Resources\InvoiceResource;

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
