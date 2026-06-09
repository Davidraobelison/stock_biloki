<x-admin-layout>
    <x-slot name="header">Stock – {{ $product->name }}</x-slot>

    <div class="space-y-6">

        {{-- Produit info + alerte --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div>
                <p class="text-sm text-gray-500">{{ $product->category->name ?? '—' }}</p>
                <h2 class="mt-1 text-xl font-bold text-gray-900">{{ $product->name }}</h2>
                <div class="mt-2 flex items-center gap-3">
                    <span class="text-3xl font-bold {{ $product->stock_quantity <= 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-yellow-600' : 'text-gray-900') }}">
                        {{ $product->stock_quantity }}
                    </span>
                    <span class="text-sm text-gray-400">unités · seuil : {{ $product->stock_alert }}</span>
                    @if($product->status === 'out_of_stock')
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">Rupture</span>
                    @elseif($product->status === 'low_stock')
                        <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700">Stock faible</span>
                    @else
                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">En stock</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.stock.index') }}"
               class="shrink-0 text-sm text-gray-500 hover:text-gray-700">← Retour au stock</a>
        </div>

        {{-- Formulaire d'ajustement --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Ajuster le stock</h3>

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.stock.adjust', $product) }}"
                  x-data="{ type: 'in' }">
                @csrf

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    {{-- Type --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type de mouvement</label>
                        <div class="flex gap-2">
                            <label class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2 text-sm
                                          transition-colors"
                                   :class="type === 'in' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="type" value="in" x-model="type" class="sr-only">
                                ↑ Entrée
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2 text-sm
                                          transition-colors"
                                   :class="type === 'out' ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="type" value="out" x-model="type" class="sr-only">
                                ↓ Sortie
                            </label>
                        </div>
                    </div>

                    {{-- Quantité --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Quantité <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}"
                               class="w-32 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    {{-- Note --}}
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Note (optionnel)</label>
                        <input type="text" name="note" value="{{ old('note') }}" placeholder="ex: réapprovisionnement…"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <button type="submit"
                            class="shrink-0 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Valider
                    </button>
                </div>
            </form>
        </div>

        {{-- Historique des mouvements --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-700">Historique des mouvements</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-center">Type</th>
                        <th class="px-4 py-3 text-center">Quantité</th>
                        <th class="px-4 py-3 text-center">Avant</th>
                        <th class="px-4 py-3 text-center">Après</th>
                        <th class="px-4 py-3 text-left">Note</th>
                        <th class="px-4 py-3 text-left">Opérateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($m->type === 'in')
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">Entrée</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">Sortie</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-semibold
                                   {{ $m->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $m->type === 'in' ? '+' : '-' }}{{ $m->quantity }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-400">{{ $m->before_quantity }}</td>
                        <td class="px-4 py-3 text-center font-medium text-gray-700">{{ $m->after_quantity }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->note ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->user->name ?? 'Système' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Aucun mouvement enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($movements->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
