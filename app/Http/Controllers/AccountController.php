<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(): View
    {
        $accounts = Account::with('client')
            ->orderBy('account_number')
            ->get();

        return view('accounts.index', ['accounts' => $accounts]);
    }

    /**
     * Display the specified account
     */
    public function show(int $id): View
    {
        $account = Account::with('client')->findOrFail($id);

        return view('accounts.show', ['account' => $account]);
    }
}
