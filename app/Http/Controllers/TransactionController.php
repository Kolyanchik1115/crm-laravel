<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Contracts\View\View;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transactions
     */
    public function index(): View
    {
        $transactions = $this->transactionService->getAllTransactions();

        return view('transactions.index', ['transactions' => $transactions]);
    }
}
