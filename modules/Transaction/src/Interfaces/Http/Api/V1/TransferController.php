<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Transaction\src\Application\Services\TransferService;
use Modules\Transaction\src\Domain\Entities\Transaction;
use Modules\Transaction\src\Interfaces\Http\Requests\V1\StoreTransferRequest;
use Modules\Transaction\src\Interfaces\Http\Resources\V1\TransferResource;

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
