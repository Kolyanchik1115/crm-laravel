<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(): View
    {
        $transactions = Transaction::with('account.client')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transactions.index', ['transactions' => $transactions]);
    }
}
