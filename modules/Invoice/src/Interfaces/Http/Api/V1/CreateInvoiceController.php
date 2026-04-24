<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Invoice\src\Application\Services\InvoiceService;
use Modules\Invoice\src\Interfaces\Http\Requests\V1\StoreInvoiceRequest;
use Modules\Invoice\src\Interfaces\Http\Resources\V1\InvoiceResource;

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
