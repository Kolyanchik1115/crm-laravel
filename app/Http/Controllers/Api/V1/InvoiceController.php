<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'POST /api/v1/invoices - TODO: implement store',
            'data' => null
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'message' => "PATCH /api/v1/invoices/{$id} - TODO: implement update",
            'data' => null
        ]);
    }
}
