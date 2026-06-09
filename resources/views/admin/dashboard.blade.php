<x-admin-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm font-medium text-gray-500">Total produits</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalProducts }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm font-medium text-gray-500">Total clients</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalClients }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-red-200">
            <p class="text-sm font-medium text-red-500">Rupture de stock</p>
            <p class="mt-2 text-3xl font-bold text-red-600">{{ $outOfStock }}</p>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-yellow-200">
            <p class="text-sm font-medium text-yellow-600">Stock faible</p>
            <p class="mt-2 text-3xl font-bold text-yellow-600">{{ $lowStock }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Stock par catégorie --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Stock par catégorie</h2>
            <canvas id="chartCategories" height="220"></canvas>
        </div>

        {{-- Nouveaux clients par mois --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <h2 class="mb-4 text-sm font-semibold text-gray-700">Nouveaux clients (6 derniers mois)</h2>
            <canvas id="chartClients" height="220"></canvas>
        </div>
    </div>

    {{-- Produits en alerte --}}
    @php
        $alertProducts = \App\Models\Product::with('category')
            ->whereColumn('stock_quantity', '<=', 'stock_alert')
            ->orderBy('stock_quantity')
            ->take(10)
            ->get();
    @endphp

    @if($alertProducts->isNotEmpty())
    <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Produits en alerte de stock</h2>
        </div>
        <ul class="divide-y divide-gray-100">
            @foreach($alertProducts as $p)
            <li class="flex items-center justify-between px-6 py-3">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $p->name }}</p>
                    <p class="text-xs text-gray-400">{{ $p->category->name ?? '—' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">{{ $p->stock_quantity }} unités</span>
                    @if($p->stock_quantity <= 0)
                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Rupture</span>
                    @else
                        <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700">Stock faible</span>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const categoryData = @json($stockByCategory);
        const clientData   = @json($newClientsByMonth);

        new Chart(document.getElementById('chartCategories'), {
            type: 'bar',
            data: {
                labels: categoryData.map(d => d.name),
                datasets: [{
                    label: 'Unités en stock',
                    data: categoryData.map(d => d.total),
                    backgroundColor: 'rgba(99,102,241,0.7)',
                    borderRadius: 4,
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartClients'), {
            type: 'line',
            data: {
                labels: clientData.map(d => d.month),
                datasets: [{
                    label: 'Nouveaux clients',
                    data: clientData.map(d => d.count),
                    borderColor: 'rgba(16,185,129,1)',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    </script>
    @endpush
</x-admin-layout>
