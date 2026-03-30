<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(): View
    {
        $clients = Client::with('accounts')
            ->orderBy('full_name')
            ->get();

        return view('clients.index', ['clients' => $clients]);
    }

    /**
     * Display the specified client
     */
    public function show(int $id): View
    {
        $client = Client::with('accounts')->findOrFail($id);

        return view('clients.show', ['client' => $client]);
    }
}
