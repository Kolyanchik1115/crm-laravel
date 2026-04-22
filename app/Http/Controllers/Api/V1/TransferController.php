<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SameAccountTransferException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransferRequest;
use App\Http\Resources\TransferResource;
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

    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'GET /api/v1/transfers - TODO: implement index',
            'data' => []
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'message' => "GET /api/v1/transfers/{$id} - TODO: implement show",
            'data' => null
        ]);
    }

    public function store(StoreTransferRequest $request): JsonResponse
    {
        try {
            $dto = $request->toTransferDTO();
            $result = $this->transferService->executeTransfer($dto);

            return (new TransferResource($result))
                ->additional([
                    'success' => true,
                    'message' => 'Переказ успішно виконано',
                ])
                ->response()
                ->setStatusCode(200);

        } catch (SameAccountTransferException|InsufficientBalanceException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            Log::error('Transfer failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
