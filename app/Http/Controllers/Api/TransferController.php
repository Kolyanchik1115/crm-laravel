<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        try {

            $dto = $request->toTransferDTO();

            $result = $this->transferService->executeTransfer($dto);

            return response()->json([
                'success' => true,
                'message' => 'Переказ успішно виконано',
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
