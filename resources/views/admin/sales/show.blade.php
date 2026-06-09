<x-admin-layout>
    <x-slot name="header">Vente {{ $sale->reference }}</x-slot>

    <div class="mx-auto max-w-2xl space-y-6">

        {{-- En-tête de la vente --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-mono text-lg font-bold text-gray-900">{{ $sale->reference }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $sale->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                @if($sale->isCompleted())
                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">Complétée</span>
                @else
                    <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-600">Annulée</span>
                @endif
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 text-sm">
                <div>
                    <p class="text-gray-400">Client</p>
                    <p class="font-medium text-gray-900">{{ $sale->client?->name ?? 'Vente directe' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Mode de paiement</p>
                    <p class="font-medium text-gray-900">{{ \App\Models\Sale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Enregistrée par</p>
                    <p class="font-medium text-gray-900">{{ $sale->user?->name ?? '-' }}</p>
                </div>
                @if($sale->notes)
                <div class="col-span-2">
                    <p class="text-gray-400">Notes</p>
                    <p class="font-medium text-gray-900">{{ $sale->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Articles --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Produit</th>
                        <th class="px-4 py-3 text-left">Catégorie</th>
                        <th class="px-4 py-3 text-center">Qté</th>
                        <th class="px-4 py-3 text-right">Prix unit.</th>
                        <th class="px-4 py-3 text-right">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $item->product?->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item->unit_price, 2) }} Ar</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($item->subtotal, 2) }} Ar</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total</td>
                        <td class="px-4 py-3 text-right text-lg font-bold text-gray-900">
                            {{ number_format($sale->total_amount, 2) }} Ar
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.sales.index') }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                ← Retour aux ventes
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.sales.receipt', $sale) }}" target="_blank"
                   class="flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Ticket de caisse
                </a>

                @if($sale->isCompleted())
                <form method="POST" action="{{ route('admin.sales.destroy', $sale) }}"
                      x-data
                      @submit.prevent="if(confirm('Annuler cette vente et restaurer le stock ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                        Annuler la vente
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
