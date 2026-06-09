<x-admin-layout>
    <x-slot name="header">Gestion du stock</x-slot>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Produit</th>
                    <th class="px-4 py-3 text-left">Catégorie</th>
                    <th class="px-4 py-3 text-center">Stock actuel</th>
                    <th class="px-4 py-3 text-center">Seuil d'alerte</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $product->category->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center font-semibold
                               {{ $product->stock_quantity <= 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-yellow-600' : 'text-gray-700') }}">
                        {{ $product->stock_quantity }}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $product->stock_alert }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($product->status === 'out_of_stock')
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">Rupture</span>
                        @elseif($product->status === 'low_stock')
                            <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700">Stock faible</span>
                        @else
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">En stock</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.stock.show', $product) }}"
                           class="rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                            Gérer
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400">Aucun produit.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($products->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
