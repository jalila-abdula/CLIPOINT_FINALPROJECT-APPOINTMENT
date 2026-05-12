<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    private function parseAddress(?string $address): array
    {
        if (empty($address)) {
            return [
                'house_street' => '',
                'barangay' => '',
                'city' => '',
                'postal_province' => '',
            ];
        }

        $parts = explode(',', $address);
        return [
            'house_street' => trim($parts[0] ?? ''),
            'barangay' => trim($parts[1] ?? ''),
            'city' => trim($parts[2] ?? ''),
            'postal_province' => trim($parts[3] ?? ''),
        ];
    }

    public function index(): View
    {
        $search = request('search');
        $filter = request('filter', 'latest');

        $clients = Client::query()
            ->with(['appointments' => fn ($query) => $query
                ->with('staff')
                ->latest('appointment_date')
                ->latest('appointment_time')])
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filter === 'oldest', fn ($query) => $query->oldest(), fn ($query) => $query->latest())
            ->paginate(10)
            ->withQueryString();

        $addressParts = [
            'house_street' => '',
            'barangay' => '',
            'city' => '',
            'postal_province' => '',
        ];

        return view('clients.index', compact('clients', 'search', 'filter', 'addressParts'));
    }

    public function create(): View
    {
        return view('clients.create', [
            'client' => new Client(),
            'addressParts' => [
                'house_street' => '',
                'barangay' => '',
                'city' => '',
                'postal_province' => '',
            ],
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create($request->validated());

        return redirect()
            ->route('clients.index')
            ->with('status', 'Client profile created successfully.');
    }

    public function show(Client $client): View
    {
        $client->load(['appointments.staff', 'serviceRecords.staff']);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        // Parse the address into components
        $addressParts = $this->parseAddress($client->address);

        return view('clients.edit', compact('client', 'addressParts'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.index')
            ->with('status', 'Client profile updated successfully.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('status', 'Client deleted successfully.');
    }
}
