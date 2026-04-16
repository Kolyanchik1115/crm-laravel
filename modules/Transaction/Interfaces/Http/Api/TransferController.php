<?php

declare(strict_types=1);

namespace Modules\Transaction\Interfaces\Http\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Transaction\Application\Services\TransferService;
use Modules\Transaction\Domain\Exceptions\InsufficientBalanceException;
use Modules\Transaction\Domain\Exceptions\SameAccountTransferException;
use Modules\Transaction\Interfaces\Http\Requests\StoreTransferRequest;
use Modules\Transaction\Interfaces\Http\Resources\TransferResource;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function transfer(StoreTransferRequest $request): JsonResponse
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
