<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(20)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:clients,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        Client::create($validated);

        return redirect()->route('admin.clients.index')
                         ->with('success', 'Client créé avec succès.');
    }

    public function show(Client $client)
    {
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:clients,email,' . $client->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.index')
                         ->with('success', 'Client modifié avec succès.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('admin.clients.index')
                         ->with('success', 'Client supprimé.');
    }

    public function export(): StreamedResponse
    {
        $clients = Client::all();

        return response()->streamDownload(function () use ($clients) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM pour Excel
            fputcsv($handle, ['ID', 'Nom', 'Email', 'Téléphone', 'Adresse', 'Créé le'], ';');

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->id,
                    $client->name,
                    $client->email,
                    $client->phone ?? '',
                    $client->address ?? '',
                    $client->created_at->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        }, 'clients_' . now()->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
