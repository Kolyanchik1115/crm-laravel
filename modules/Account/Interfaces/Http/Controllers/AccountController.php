<?php

declare(strict_types=1);

namespace Modules\Account\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Account\Application\Services\AccountService;

class AccountController extends Controller
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Display a listing of accounts
     */
    public function index(): View
    {
        $accounts = $this->accountService->getAllAccounts();

        return view('account::accounts.index', ['accounts' => $accounts]);
    }

    /**
     * Display the specified accounts
     */
    public function show(int $id): View
    {
        $account = $this->accountService->getAccountById($id);

        return view('account::accounts.show', ['account' => $account]);
    }
}
