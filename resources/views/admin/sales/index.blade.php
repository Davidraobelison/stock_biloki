<x-admin-layout>
    <x-slot name="header">Ventes</x-slot>

    {{-- Toolbar --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Référence ou client…"
                   class="w-full max-w-xs rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            <select name="status"
                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Tous les statuts</option>
                <option value="completed" @selected(request('status') === 'completed')>Complétées</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Annulées</option>
            </select>

            <button type="submit"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.sales.index') }}"
                   class="rounded-lg px-3 py-2 text-sm text-gray-500 hover:text-gray-700">✕</a>
            @endif
        </form>

        <a href="{{ route('admin.sales.create') }}"
           class="shrink-0 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + Nouvelle vente
        </a>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Référence</th>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-center">Articles</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-medium text-gray-900">{{ $sale->reference }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $sale->client?->name ?? 'Vente directe' }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $sale->items->count() }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        {{ number_format($sale->total_amount, 2) }} Ar
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($sale->isCompleted())
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">Complétée</span>
                        @else
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-600">Annulée</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.sales.show', $sale) }}"
                               class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                Voir
                            </a>
                            @if($sale->isCompleted())
                            <form method="POST" action="{{ route('admin.sales.destroy', $sale) }}"
                                  x-data
                                  @submit.prevent="if(confirm('Annuler cette vente et restaurer le stock ?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100">
                                    Annuler
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">
                        Aucune vente.
                        <a href="{{ route('admin.sales.create') }}" class="text-indigo-600 hover:underline">Créer la première</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($sales->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
