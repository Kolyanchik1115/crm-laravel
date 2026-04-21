<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $transactions = Transaction::with('account.client')
            ->orderBy('created_at', 'desc')
            ->get();

        return TransactionResource::collection($transactions)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = Transaction::with('account.client')->findOrFail($id);

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(200);
    }
}
