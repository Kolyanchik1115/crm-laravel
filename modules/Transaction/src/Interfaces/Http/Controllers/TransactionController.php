<?php

declare(strict_types=1);

namespace Modules\Transaction\src\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Transaction\src\Application\Services\TransactionService;

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

        return view('transaction::transactions.index', ['transactions' => $transactions]);
    }
}
