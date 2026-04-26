<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AccountResource;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = Account::with(['client', 'transactions'])
            ->orderBy('account_number')
            ->paginate(15);

        return AccountResource::collection($accounts)
            ->response()
            ->setStatusCode(200);
    }

    public function show(int $id): JsonResponse
    {
        $account = Account::with(['client', 'transactions'])->findOrFail($id);

        return (new AccountResource($account))
            ->response()
            ->setStatusCode(200);
    }

    public function transactions(int $accountId): JsonResponse
    {
        $transactions = Transaction::with(['account'])
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return TransactionResource::collection($transactions)
            ->response()
            ->setStatusCode(200);
    }
}
