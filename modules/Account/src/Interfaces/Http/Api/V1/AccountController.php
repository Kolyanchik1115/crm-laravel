<?php

declare(strict_types=1);

namespace Modules\Account\src\Interfaces\Http\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Account\src\Application\Services\AccountService;
use Modules\Account\src\Interfaces\Http\Resources\V1\AccountResource;
use Modules\Transaction\src\Application\Services\TransactionService;
use Modules\Transaction\src\Interfaces\Http\Resources\V1\TransactionResource;

class AccountController extends Controller
{
    public function __construct(
        private AccountService     $accountService,
        private TransactionService $transactionService
    ) {
    }

    /**
     * Display a listing of accounts
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $accounts = $this->accountService
            ->getAllAccountsPaginated(5);

        return AccountResource::collection($accounts)
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Display the specified account
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $account = $this->accountService->getAccountById($id);

        return (new AccountResource($account))
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Get account transactions
     *
     * @param int $accountId
     * @return JsonResponse
     */
    public function transactions(int $accountId): JsonResponse
    {
        $transactions = $this->transactionService->
        getAccountTransactionsPaginated($accountId, 5);

        return TransactionResource::collection($transactions)
            ->additional(['success' => true])
            ->response()
            ->setStatusCode(200);
    }
}
