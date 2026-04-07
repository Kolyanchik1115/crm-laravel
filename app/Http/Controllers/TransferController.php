<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->transferService->executeTransfer(
                fromAccountId: $validated['from_account_id'],
                toAccountId: $validated['to_account_id'],
                amount: $validated['amount'],
                description: $validated['description'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Переказ успішно виконано',
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer failed', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
