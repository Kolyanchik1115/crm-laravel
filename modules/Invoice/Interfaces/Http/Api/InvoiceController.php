<?php

declare(strict_types=1);

namespace Modules\Invoice\Interfaces\Http\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Invoice\Domain\Entities\Invoice;
use Modules\Invoice\Interfaces\Http\Resources\InvoiceResource;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        $invoices = Invoice::with(['client', 'items.service'])
            ->orderBy('created_at', 'desc')
            ->get();

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
}
