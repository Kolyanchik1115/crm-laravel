<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ClientService;
use App\Http\Requests\ClientRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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

        return view('clients.index', ['clients' => $clients]);
    }

    /**
     * Display the specified client
     */
    public function show(int $id): View
    {
        $client = $this->clientService->getClientById($id);

        return view('clients.show', ['client' => $client]);
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(ClientRequest $request): RedirectResponse
    {

        $this->clientService->createClient($request->validated());

        return redirect()->route('clients.index');
    }
}
