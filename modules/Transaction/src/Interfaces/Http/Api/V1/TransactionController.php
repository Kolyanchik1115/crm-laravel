<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Transaction\src\Application\Services\TransactionService;
use Modules\Transaction\src\Interfaces\Http\Resources\V1\TransactionResource;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function index(): JsonResponse
    {
        $transactions = $this->transactionService
            ->getAllTransactionsPaginated(5);

        return TransactionResource::collection($transactions)
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransactionById($id);

        return (new TransactionResource($transaction))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }
}
