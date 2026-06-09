<x-admin-layout>
    <x-slot name="header">Catégories</x-slot>

    {{-- Toolbar --}}
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $categories->count() }} catégorie(s)</p>
        <a href="{{ route('admin.categories.create') }}"
           class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + Nouvelle catégorie
        </a>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Slug</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-center">Produits</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $category->slug }}</td>
                    <td class="px-4 py-3 max-w-xs truncate text-gray-500">{{ $category->description ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                            {{ $category->products_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                Modifier
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  x-data
                                  @submit.prevent="if(confirm('Supprimer cette catégorie ?')) $el.submit()">
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
                    <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">
                        Aucune catégorie. <a href="{{ route('admin.categories.create') }}" class="text-indigo-600 hover:underline">Créer la première</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
