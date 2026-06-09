<x-admin-layout>
    <x-slot name="header">Caisse</x-slot>

    <div class="space-y-6">

    {{-- ===== SESSION OUVERTE ===== --}}
    @if($session)

        {{-- Statut + fermeture --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span>
                        <p class="font-semibold text-gray-900">Session #{{ $session->id }} ouverte</p>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Ouverte par <strong>{{ $session->user?->name }}</strong>
                        le {{ $session->opened_at->format('d/m/Y à H:i') }}
                    </p>
                    @if($session->notes)
                        <p class="mt-1 text-sm text-gray-400">{{ $session->notes }}</p>
                    @endif
                </div>

                <div class="text-right">
                    <p class="text-xs text-gray-400">Solde d'ouverture</p>
                    <p class="text-lg font-semibold text-gray-700">{{ number_format($session->opening_balance, 0, ',', ' ') }} Ar</p>
                </div>
            </div>

            {{-- Solde attendu --}}
            @php
                $entrees = $session->mouvements->where('type', 'entree')->sum('amount');
                $sorties = $session->mouvements->where('type', 'sortie')->sum('amount');
                $solde   = (float) $session->opening_balance + $entrees - $sorties;
            @endphp
            <div class="mt-4 grid grid-cols-3 gap-4 rounded-lg bg-gray-50 p-4 text-center">
                <div>
                    <p class="text-xs text-gray-400">Entrées</p>
                    <p class="text-base font-semibold text-green-600">+ {{ number_format($entrees, 0, ',', ' ') }} Ar</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Sorties</p>
                    <p class="text-base font-semibold text-red-600">- {{ number_format($sorties, 0, ',', ' ') }} Ar</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Solde attendu</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($solde, 0, ',', ' ') }} Ar</p>
                </div>
            </div>

            {{-- Fermer la caisse --}}
            <div x-data="{ open: false }" class="mt-4 border-t border-gray-100 pt-4">
                <button @click="open = !open" type="button"
                        class="rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                    Fermer la caisse
                </button>

                <div x-show="open" x-transition class="mt-4">
                    <form method="POST" action="{{ route('admin.caisse.close', $session) }}">
                        @csrf
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Solde physique compté <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="closing_balance" step="1" min="0"
                                       placeholder="{{ number_format($solde, 0) }}"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes de clôture</label>
                                <input type="text" name="notes"
                                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button type="submit"
                                    class="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Confirmer la fermeture
                            </button>
                            <button @click="open = false" type="button"
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Ajouter un mouvement --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Nouveau mouvement</h2>
            <form method="POST" action="{{ route('admin.caisse.mouvement', $session) }}"
                  class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600">Type</label>
                    <select name="type"
                            class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="entree">Entrée</option>
                        <option value="sortie">Sortie / Dépense</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Montant (Ar)</label>
                    <input type="number" name="amount" min="1" step="1" placeholder="0"
                           class="mt-1 w-36 rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600">Description</label>
                    <input type="text" name="description" placeholder="Ex : achat emballages, retrait…"
                           class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Ajouter
                </button>
            </form>
            @if($errors->any())
                <p class="mt-2 text-xs text-red-600">{{ $errors->first() }}</p>
            @endif
        </div>

        {{-- Mouvements de la session --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-700">Mouvements de la session</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Heure</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-left">Vente</th>
                        <th class="px-4 py-3 text-center">Type</th>
                        <th class="px-4 py-3 text-right">Montant</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($session->mouvements as $mouvement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400">{{ $mouvement->created_at->format('H:i') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $mouvement->description }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-indigo-600">
                            @if($mouvement->sale)
                                <a href="{{ route('admin.sales.show', $mouvement->sale) }}">
                                    {{ $mouvement->sale->reference }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($mouvement->type === 'entree')
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">Entrée</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-600">Sortie</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $mouvement->type === 'entree' ? 'text-green-700' : 'text-red-600' }}">
                            {{ $mouvement->type === 'entree' ? '+' : '-' }}{{ number_format($mouvement->amount, 0, ',', ' ') }} Ar
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">
                            Aucun mouvement pour l'instant.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    {{-- ===== AUCUNE SESSION ===== --}}
    @else

        <div class="rounded-xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-200" x-data="{ open: false }">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Aucune session ouverte</p>
            <p class="mt-1 text-xs text-gray-400">Ouvrez la caisse pour commencer à enregistrer les mouvements.</p>

            <button @click="open = !open" type="button"
                    class="mt-4 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Ouvrir la caisse
            </button>

            <div x-show="open" x-transition class="mt-6 text-left">
                <form method="POST" action="{{ route('admin.caisse.store') }}"
                      class="mx-auto max-w-md rounded-xl border border-gray-200 p-5">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Solde d'ouverture (Ar) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="opening_balance" min="0" step="1"
                                   value="{{ old('opening_balance', 0) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <input type="text" name="notes" value="{{ old('notes') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Ouvrir la caisse
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @endif

    {{-- ===== HISTORIQUE ===== --}}
    @if($history->count())
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700">Historique des sessions</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Session</th>
                    <th class="px-4 py-3 text-left">Caissier</th>
                    <th class="px-4 py-3 text-right">Ouverture</th>
                    <th class="px-4 py-3 text-right">Fermeture</th>
                    <th class="px-4 py-3 text-right">Écart</th>
                    <th class="px-4 py-3 text-left">Fermée le</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($history as $s)
                @php
                    $ecart = $s->closing_balance !== null
                        ? (float) $s->closing_balance - $s->expectedBalance()
                        : null;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">#{{ $s->id }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format($s->opening_balance, 0, ',', ' ') }} Ar</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        {{ $s->closing_balance !== null ? number_format($s->closing_balance, 0, ',', ' ') . ' Ar' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right text-xs font-medium {{ $ecart === null ? '' : ($ecart == 0 ? 'text-green-600' : ($ecart > 0 ? 'text-blue-600' : 'text-red-600')) }}">
                        @if($ecart !== null)
                            {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }} Ar
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400">
                        {{ $s->closed_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($history->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $history->links() }}</div>
        @endif
    </div>
    @endif

    </div>
</x-admin-layout>
