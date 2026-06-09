<x-admin-layout>
    <x-slot name="header">Modifier : {{ $product->name }}</x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data"
                  x-data="{ imagePreview: '{{ $product->image ? Storage::url($product->image) : '' }}' }">
                @csrf @method('PUT')

                <div class="space-y-5">
                    {{-- Nom --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                      @error('name') border-red-500 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $product->description) }}</textarea>
                    </div>

                    {{-- Prix + Catégorie --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prix (€) <span class="text-red-500">*</span></label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                          @error('price') border-red-500 @enderror">
                            @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catégorie <span class="text-red-500">*</span></label>
                            <select name="category_id"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                           @error('category_id') border-red-500 @enderror">
                                <option value="">— Choisir —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Stock quantité + seuil --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantité en stock <span class="text-red-500">*</span></label>
                            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Seuil d'alerte <span class="text-red-500">*</span></label>
                            <input type="number" name="stock_alert" value="{{ old('stock_alert', $product->stock_alert) }}" min="0"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    {{-- Image --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Image</label>
                        <div class="mt-1 flex items-start gap-4">
                            <div x-show="imagePreview" class="h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                <img :src="imagePreview" class="h-full w-full object-cover">
                            </div>
                            <input type="file" name="image" accept="image/*"
                                   @change="imagePreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : imagePreview"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('admin.products.index') }}"
                       class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        Annuler
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
