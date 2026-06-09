<x-admin-layout>
    <x-slot name="header">Nouvelle vente</x-slot>

    <div x-data="saleForm()" class="mx-auto max-w-3xl space-y-6">

        <form method="POST" action="{{ route('admin.sales.store') }}">
            @csrf

            {{-- Erreurs globales --}}
            @if($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Informations --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="mb-4 text-sm font-semibold text-gray-700">Informations</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    {{-- Autocomplete Client --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Client <span class="text-gray-400">(optionnel)</span>
                        </label>

                        {{-- Client sélectionné --}}
                        <div x-show="clientLabel" class="mt-1 flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm">
                            <span class="flex-1 font-medium text-indigo-800" x-text="clientLabel"></span>
                            <button type="button" @click="clearClient()"
                                    class="text-indigo-400 hover:text-indigo-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Champ de recherche --}}
                        <div x-show="!clientLabel" class="relative mt-1">
                            <input type="text"
                                   x-model="clientQuery"
                                   @input.debounce.300ms="searchClients()"
                                   @focus="if(clientQuery) searchClients()"
                                   @click.away="clientOpen = false"
                                   placeholder="Code ou nom du client…"
                                   autocomplete="off"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

                            <div x-show="clientOpen" x-transition
                                 class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg">
                                <template x-for="c in clientResults" :key="c.id">
                                    <button type="button" @click="selectClient(c)"
                                            class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm hover:bg-indigo-50">
                                        <span class="font-mono text-xs text-gray-400" x-text="c.code ?? '—'"></span>
                                        <span class="font-medium text-gray-900" x-text="c.name"></span>
                                    </button>
                                </template>
                                <p x-show="clientResults.length === 0 && clientQuery.length > 0"
                                   class="px-3 py-2.5 text-sm text-gray-400">Aucun client trouvé.</p>
                            </div>
                        </div>

                        <input type="hidden" name="client_id" :value="clientId">
                    </div>

                    {{-- Mode de paiement --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Mode de paiement <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_method"
                                class="mt-1 block w-full rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $errors->has('payment_method') ? 'border-red-500' : 'border-gray-300' }}">
                            @foreach(\App\Models\Sale::PAYMENT_METHODS as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_method', 'especes') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Notes --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               placeholder="Remarques, numéro de commande…"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            {{-- Produits --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="mb-4 text-sm font-semibold text-gray-700">Produits</h2>

                {{-- Autocomplete Produit --}}
                <div class="relative mb-4" @click.away="productOpen = false">
                    <label class="block text-sm font-medium text-gray-700">Ajouter un produit</label>
                    <input type="text"
                           x-model="productQuery"
                           @input.debounce.300ms="searchProducts()"
                           @focus="if(productQuery) searchProducts()"
                           placeholder="Code ou nom du produit…"
                           autocomplete="off"
                           class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

                    <div x-show="productOpen" x-transition
                         class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg">
                        <template x-for="p in productResults" :key="p.id">
                            <button type="button" @click="selectProduct(p)"
                                    class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm hover:bg-indigo-50">
                                <span class="font-mono text-xs text-gray-400" x-text="p.code ?? '—'"></span>
                                <span class="flex-1 font-medium text-gray-900" x-text="p.name"></span>
                                <span class="text-xs text-gray-500">
                                    stock&nbsp;<span x-text="p.stock_quantity"></span>
                                </span>
                                <span class="text-xs font-semibold text-indigo-700" x-text="Number(p.price).toFixed(0) + ' Ar'"></span>
                            </button>
                        </template>
                        <p x-show="productResults.length === 0 && productQuery.length > 0"
                           class="px-3 py-2.5 text-sm text-gray-400">Aucun produit disponible.</p>
                    </div>
                </div>

                {{-- Table articles --}}
                <div x-show="items.length > 0">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Code</th>
                                <th class="px-3 py-2 text-left">Produit</th>
                                <th class="px-3 py-2 text-center">Qté</th>
                                <th class="px-3 py-2 text-right">P.U.</th>
                                <th class="px-3 py-2 text-right">Sous-total</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in items" :key="item.product_id">
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-400" x-text="item.code ?? '—'"></td>
                                    <td class="px-3 py-2 font-medium text-gray-900" x-text="item.name"></td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number"
                                               x-model.number="item.quantity"
                                               :max="item.stock"
                                               min="1"
                                               class="w-20 rounded-lg border-gray-300 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-3 py-2 text-right text-gray-600"
                                        x-text="item.unit_price.toFixed(0) + ' Ar'"></td>
                                    <td class="px-3 py-2 text-right font-semibold text-gray-900"
                                        x-text="(item.unit_price * item.quantity).toFixed(0) + ' Ar'"></td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeItem(index)"
                                                class="rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="mt-4 flex justify-end border-t border-gray-200 pt-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="text-2xl font-bold text-gray-900" x-text="total() + ' Ar'"></p>
                        </div>
                    </div>
                </div>

                <p x-show="items.length === 0" class="py-6 text-center text-sm text-gray-400">
                    Aucun produit ajouté. Recherchez un produit ci-dessus.
                </p>

                {{-- Hidden inputs --}}
                <template x-for="(item, index) in items" :key="item.product_id">
                    <div>
                        <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                    </div>
                </template>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.sales.index') }}"
                   class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Annuler
                </a>
                <button type="submit"
                        :disabled="items.length === 0"
                        class="rounded-lg bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Enregistrer la vente
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function saleForm() {
        return {
            // Client
            clientQuery:   '',
            clientResults: [],
            clientOpen:    false,
            clientId:      '{{ old('client_id', '') }}',
            clientLabel:   '',

            // Produit
            productQuery:   '',
            productResults: [],
            productOpen:    false,

            // Panier
            items: [],

            async searchClients() {
                if (!this.clientQuery) { this.clientResults = []; this.clientOpen = false; return; }
                const r = await fetch('{{ route('admin.clients.search') }}?q=' + encodeURIComponent(this.clientQuery));
                this.clientResults = await r.json();
                this.clientOpen = true;
            },

            selectClient(c) {
                this.clientId    = c.id;
                this.clientLabel = (c.code ? '[' + c.code + '] ' : '') + c.name;
                this.clientQuery = '';
                this.clientOpen  = false;
            },

            clearClient() {
                this.clientId    = '';
                this.clientLabel = '';
            },

            async searchProducts() {
                if (!this.productQuery) { this.productResults = []; this.productOpen = false; return; }
                const r = await fetch('{{ route('admin.products.search') }}?q=' + encodeURIComponent(this.productQuery));
                this.productResults = await r.json();
                this.productOpen = true;
            },

            selectProduct(p) {
                const existing = this.items.find(i => i.product_id == p.id);
                if (existing) {
                    if (existing.quantity < existing.stock) existing.quantity++;
                } else {
                    this.items.push({
                        product_id: p.id,
                        code:       p.code,
                        name:       p.name,
                        unit_price: parseFloat(p.price),
                        quantity:   1,
                        stock:      parseInt(p.stock_quantity),
                    });
                }
                this.productQuery  = '';
                this.productOpen   = false;
                this.productResults = [];
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            total() {
                return this.items
                    .reduce((sum, i) => sum + i.unit_price * i.quantity, 0)
                    .toFixed(0);
            },
        };
    }
    </script>
    @endpush
</x-admin-layout>
