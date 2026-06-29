<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()->paginate(10);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|max:255',
            'contact_name' => 'required|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|max:30',
            'website' => 'nullable|max:255',
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:100',
            'postcode' => 'nullable|max:20',
            'country' => 'nullable|max:100',
            'status' => 'required',
            'notes' => 'nullable',
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'company_name' => 'required|max:255',
            'contact_name' => 'required|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|max:30',
            'website' => 'nullable|max:255',
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:100',
            'postcode' => 'nullable|max:20',
            'country' => 'nullable|max:100',
            'status' => 'required',
            'notes' => 'nullable',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
