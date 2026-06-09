<x-admin-layout>
    <x-slot name="header">Fiche client</x-slot>

    <div class="mx-auto max-w-2xl space-y-6">

        {{-- Info card --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $client->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">Client depuis le {{ $client->created_at->format('d/m/Y') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.clients.edit', $client) }}"
                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Modifier
                    </a>
                    <form method="POST" action="{{ route('admin.clients.destroy', $client) }}"
                          x-data
                          @submit.prevent="if(confirm('Supprimer ce client ?')) $el.submit()">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-100">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>

            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <dt class="text-xs font-medium text-gray-400 uppercase">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $client->email }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 px-4 py-3">
                    <dt class="text-xs font-medium text-gray-400 uppercase">Téléphone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $client->phone ?? '—' }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 px-4 py-3 sm:col-span-2">
                    <dt class="text-xs font-medium text-gray-400 uppercase">Adresse</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $client->address ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Historique commandes (module non implémenté) --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">Historique d'achats</h3>
            <div class="rounded-lg bg-gray-50 px-4 py-8 text-center text-sm text-gray-400">
                Le module commandes n'est pas encore disponible.
            </div>
        </div>

        <div class="text-right">
            <a href="{{ route('admin.clients.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700">← Retour à la liste</a>
        </div>
    </div>
</x-admin-layout>
