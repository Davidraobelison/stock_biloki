<x-admin-layout>
    <x-slot name="header">Nouveau client</x-slot>

    <div class="mx-auto max-w-lg">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <form method="POST" action="{{ route('admin.clients.store') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                      @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                      @error('email') border-red-500 @enderror">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Adresse</label>
                        <textarea name="address" rows="3"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('admin.clients.index') }}"
                       class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Annuler
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Créer le client
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
