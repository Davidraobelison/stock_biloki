<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts  = Product::count();
        $totalClients   = Client::count();
        $outOfStock     = Product::where('stock_quantity', '<=', 0)->count();
        $lowStock       = Product::whereColumn('stock_quantity', '<=', 'stock_alert')
                                  ->where('stock_quantity', '>', 0)
                                  ->count();

        $newClientsLast6Months = Client::where('created_at', '>=', now()->subMonths(6)->startOfMonth())->count();

        $stockByCategory = Category::withSum('products', 'stock_quantity')
            ->get()
            ->map(fn ($c) => [
                'name'  => $c->name,
                'total' => (int) ($c->products_sum_stock_quantity ?? 0),
            ]);

        // CA par jour sur les 30 derniers jours
        $caParJourRaw = Sale::selectRaw("DATE(created_at) AS day, SUM(total_amount) AS total")
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->pluck('total', 'day');

        // Remplir les jours sans vente avec 0
        $caParJour = collect();
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $caParJour->push([
                'day'   => now()->subDays($i)->translatedFormat('d/m'),
                'total' => (float) ($caParJourRaw[$day] ?? 0),
            ]);
        }

        return view('admin.dashboard', compact(
            'totalProducts', 'totalClients', 'outOfStock', 'lowStock',
            'newClientsLast6Months', 'stockByCategory', 'caParJour'
        ));
    }
}
