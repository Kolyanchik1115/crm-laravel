<?php

declare(strict_types=1);

namespace Modules\Account\Interfaces\Http\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Account\Domain\Entities\Account;
use Modules\Account\Interfaces\Http\Resources\AccountResource;

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
