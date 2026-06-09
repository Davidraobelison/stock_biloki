<x-admin-layout>
    <x-slot name="header">Clients</x-slot>

    {{-- Toolbar --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher par nom, email, téléphone…"
                   class="w-full max-w-sm rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                Rechercher
            </button>
            @if(request('search'))
                <a href="{{ route('admin.clients.index') }}"
                   class="rounded-lg px-3 py-2 text-sm text-gray-500 hover:text-gray-700">✕</a>
            @endif
        </form>

        <div class="flex shrink-0 gap-2">
            <a href="{{ route('admin.clients.export') }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                ↓ Export CSV
            </a>
            <a href="{{ route('admin.clients.create') }}"
               class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                + Nouveau client
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Téléphone</th>
                    <th class="px-4 py-3 text-left">Adresse</th>
                    <th class="px-4 py-3 text-left">Ajouté le</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $client->code ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.clients.show', $client) }}"
                           class="font-medium text-indigo-600 hover:text-indigo-800">
                            {{ $client->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $client->email }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $client->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $client->address ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $client->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.clients.edit', $client) }}"
                               class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200">
                                Modifier
                            </a>
                            <form method="POST" action="{{ route('admin.clients.destroy', $client) }}"
                                  x-data
                                  @submit.prevent="if(confirm('Supprimer ce client ?')) $el.submit()">
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
                        Aucun client trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($clients->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
