<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Transaction\src\Interfaces\Http\Resources\V1\TransactionResource;

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
