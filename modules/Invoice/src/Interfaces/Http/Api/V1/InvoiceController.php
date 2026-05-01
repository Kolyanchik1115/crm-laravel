<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Invoice\src\Application\Services\InvoiceService;
use Modules\Invoice\src\Interfaces\Http\Requests\V1\StoreInvoiceRequest;
use Modules\Invoice\src\Interfaces\Http\Resources\V1\InvoiceResource;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {
    }

    public function index(): JsonResponse
    {
        $invoices = $this->invoiceService
            ->getAllInvoicesPaginated(5);

        return InvoiceResource::collection($invoices)
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->invoiceService->getInvoiceById($id);

        return (new InvoiceResource($invoice))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $dto = $request->toCreateInvoiceDTO();
        $invoice = $this->invoiceService->createInvoice($dto);

        $response = (new InvoiceResource($invoice))
            ->additional([
                'success' => true,
                'message' => 'Рахунок-фактуру успішно створено',
            ])
            ->response()
            ->setStatusCode(201);

        $location = url("/api/v1/invoices/{$invoice->id}");
        $response->header('Location', $location);

        return $response;
    }
}
