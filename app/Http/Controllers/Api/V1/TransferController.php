<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransferRequest;
use App\Http\Resources\Api\V1\TransferResource;
use App\Models\Transaction;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(): JsonResponse
    {
        $transfers = Transaction::with(['account'])
            ->where('type', 'transfer_out')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return TransferResource::collection($transfers)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $transfer = Transaction::with(['account'])
            ->where('type', 'transfer_out')
            ->findOrFail($id);

        return (new TransferResource($transfer))
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreTransferRequest $request): JsonResponse
    {
        $dto = $request->toTransferDTO();
        $result = $this->transferService->executeTransfer($dto);

        $response = (new TransferResource((object)$result))
            ->additional([
                'success' => true,
                'message' => 'Переказ успішно виконано',
            ])
            ->response()
            ->setStatusCode(201);

        //location
        $location = url("/api/v1/transfers/{$result['transaction_out_id']}");
        $response->header('Location', $location);

        return $response;
    }
}
