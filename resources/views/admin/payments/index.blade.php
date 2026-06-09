<x-admin-layout>
    <x-slot name="header">Paiements</x-slot>

    {{-- Filtres --}}
    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <div class="flex rounded-lg border border-gray-300 bg-white overflow-hidden text-sm shadow-sm">
            @foreach(['today' => "Aujourd'hui", 'week' => 'Cette semaine', 'month' => 'Ce mois', 'year' => 'Cette année'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['period' => $val, 'method' => request('method')]) }}"
                   class="px-4 py-2 {{ request('period', 'today') === $val ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <select name="method" onchange="this.form.submit()"
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Tous les modes</option>
            @foreach($methods as $val => $label)
                <option value="{{ $val }}" @selected(request('method') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    {{-- Cartes récap --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 col-span-2 lg:col-span-1">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Total encaissé</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($totalRevenu, 0, ',', ' ') }} Ar</p>
            <p class="text-xs text-gray-400 mt-1">{{ $totalVentes }} vente(s)</p>
        </div>

        @foreach($methods as $val => $label)
            @php $row = $byMethod[$val] ?? null @endphp
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-lg font-bold text-gray-900">
                    {{ $row ? number_format($row->total, 0, ',', ' ') . ' Ar' : '—' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $row ? $row->nb . ' vente(s)' : '0 vente' }}</p>
            </div>
        @endforeach
    </div>

    {{-- Table des ventes --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Référence</th>
                    <th class="px-4 py-3 text-left">Client</th>
                    <th class="px-4 py-3 text-left">Mode</th>
                    <th class="px-4 py-3 text-right">Montant</th>
                    <th class="px-4 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-medium text-indigo-600">
                        <a href="{{ route('admin.sales.show', $sale) }}">{{ $sale->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $sale->client?->name ?? 'Vente directe' }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                            {{ \App\Models\Sale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        {{ number_format($sale->total_amount, 0, ',', ' ') }} Ar
                    </td>
                    <td class="px-4 py-3 text-gray-400">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">
                        Aucune vente pour cette période.
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
