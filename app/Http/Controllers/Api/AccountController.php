<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Account;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = Account::with(['client', 'transactions'])
            ->orderBy('account_number')
            ->get();

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
}
