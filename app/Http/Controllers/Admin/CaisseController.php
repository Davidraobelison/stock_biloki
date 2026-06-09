<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaisseMouvement;
use App\Models\CaisseSession;
use Illuminate\Http\Request;

class CaisseController extends Controller
{
    public function index()
    {
        $session = CaisseSession::where('status', 'open')
            ->with(['mouvements' => fn ($q) => $q->with('sale')->latest(), 'user'])
            ->latest()
            ->first();

        $history = CaisseSession::where('status', 'closed')
            ->with('user')
            ->latest()
            ->paginate(10, ['*']);

        return view('admin.caisse.index', compact('session', 'history'));
    }

    public function store(Request $request)
    {
        if (CaisseSession::where('status', 'open')->exists()) {
            return back()->with('error', 'Une session de caisse est déjà ouverte.');
        }

        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ]);

        CaisseSession::create([
            'user_id'         => auth()->id(),
            'opening_balance' => $validated['opening_balance'],
            'notes'           => $validated['notes'] ?? null,
            'status'          => 'open',
            'opened_at'       => now(),
        ]);

        return redirect()->route('admin.caisse.index')
                         ->with('success', 'Caisse ouverte avec succès.');
    }

    public function close(Request $request, CaisseSession $session)
    {
        if (! $session->isOpen()) {
            return back()->with('error', 'Cette session est déjà fermée.');
        }

        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ]);

        $session->update([
            'closing_balance' => $validated['closing_balance'],
            'status'          => 'closed',
            'closed_at'       => now(),
            'notes'           => $validated['notes'] ?? $session->notes,
        ]);

        return redirect()->route('admin.caisse.index')
                         ->with('success', 'Caisse fermée. Bilan enregistré.');
    }

    public function storeMouvement(Request $request, CaisseSession $session)
    {
        if (! $session->isOpen()) {
            return back()->with('error', 'Cette session est fermée.');
        }

        $validated = $request->validate([
            'type'        => 'required|in:entree,sortie',
            'amount'      => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        CaisseMouvement::create([
            'session_id'  => $session->id,
            'user_id'     => auth()->id(),
            'type'        => $validated['type'],
            'amount'      => $validated['amount'],
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Mouvement enregistré.');
    }
}
