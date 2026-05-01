<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Transaction\src\Application\Services\TransferService;
use Modules\Transaction\src\Interfaces\Http\Requests\V1\StoreTransferRequest;
use Modules\Transaction\src\Interfaces\Http\Resources\V1\TransferResource;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService
    ) {
    }

    public function index(): JsonResponse
    {
        $transfers = $this->transferService
            ->getTransfersPaginated(5);

        return TransferResource::collection($transfers)
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $transfer = $this->transferService->getTransferById($id);

        return (new TransferResource($transfer))
            ->additional(['success' => true])
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

        $location = url("/api/v1/transfers/{$result['transaction_out_id']}");
        $response->header('Location', $location);

        return $response;
    }
}
