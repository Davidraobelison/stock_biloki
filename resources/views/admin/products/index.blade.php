<x-admin-layout>
    <x-slot name="header">Produits</x-slot>

    {{-- Toolbar --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher un produit…"
                   class="w-full max-w-xs rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            <select name="category_id"
                    class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>

            <button type="submit"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                Filtrer
            </button>
            @if(request()->hasAny(['search','category_id']))
                <a href="{{ route('admin.products.index') }}"
                   class="rounded-lg px-3 py-2 text-sm text-gray-500 hover:text-gray-700">✕</a>
            @endif
        </form>

        <div class="flex shrink-0 gap-2">
            <a href="{{ route('admin.products.export') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                ↓ Export CSV
            </a>
            <a href="{{ route('admin.products.create') }}"
               class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                + Nouveau produit
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">Produit</th>
                    <th class="px-4 py-3 text-left">Catégorie</th>
                    <th class="px-4 py-3 text-right">Prix</th>
                    <th class="px-4 py-3 text-center">Stock</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $product->code ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}"
                                     class="h-10 w-10 rounded-lg object-cover">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                @if($product->description)
                                    <p class="max-w-xs truncate text-xs text-gray-400">{{ $product->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->category->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($product->price, 2) }} Ar</td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $product->stock_quantity }}</td>
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
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                Modifier
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  x-data
                                  @submit.prevent="if(confirm('Supprimer ce produit ?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400">
                        Aucun produit trouvé.
                    </td>
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
