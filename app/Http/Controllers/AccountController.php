<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccountService;
use Illuminate\View\View;

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

        return view('accounts.index', ['accounts' => $accounts]);
    }

    /**
     * Display the specified account
     */
    public function show(int $id): View
    {
        $account = $this->accountService->getAccountById($id);

        return view('accounts.show', ['account' => $account]);
    }
}
