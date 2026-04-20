<?php

declare(strict_types=1);

namespace Modules\Client\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Client\Application\Services\ClientService;
use Illuminate\Contracts\View\View;
use Modules\Client\Interfaces\Http\Requests\StoreClientRequest;

class ClientController extends Controller
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Display a listing of clients
     */
    public function index(): View
    {
        $clients = $this->clientService->getAllClients();

        return view('client::clients.index', ['clients' => $clients]);
    }

    /**
     * Display the specified clients
     */
    public function show(int $id): View
    {
        $client = $this->clientService->getClientById($id);

        return view('client::clients.show', ['client' => $client]);
    }

    public function create(): View
    {
        return view('client::clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {

        $this->clientService->createClient($request->validated());

        return redirect()->route('clients.index');
    }
}
