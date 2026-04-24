<?php

declare(strict_types=1);

namespace Modules\Invoice\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Invoice\src\Application\Services\InvoiceService;
use Modules\Invoice\src\Domain\Entities\Invoice;
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
        $invoices = Invoice::with(['client', 'items.service'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return InvoiceResource::collection($invoices)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = Invoice::with(['client', 'items.service'])->findOrFail($id);

        return (new InvoiceResource($invoice))
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

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'message' => "PATCH /api/v1/invoices/{$id} - TODO: implement update",
            'data' => null
        ]);
    }
}
