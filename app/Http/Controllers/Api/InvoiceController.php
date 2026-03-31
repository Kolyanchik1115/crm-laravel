<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use Illuminate\Http\JsonResponse;

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
