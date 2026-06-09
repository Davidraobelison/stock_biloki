<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
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

        $stockByCategory = Category::withSum('products', 'stock_quantity')
            ->get()
            ->map(fn ($c) => [
                'name'  => $c->name,
                'total' => (int) ($c->products_sum_stock_quantity ?? 0),
            ]);

        $newClientsByMonth = Client::selectRaw(
                "DATE_TRUNC('month', created_at) AS month_start, COUNT(*) AS count"
            )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw("DATE_TRUNC('month', created_at)")
            ->orderByRaw("DATE_TRUNC('month', created_at)")
            ->get()
            ->map(fn ($row) => [
                'month' => \Carbon\Carbon::parse($row->month_start)->translatedFormat('M Y'),
                'count' => (int) $row->count,
            ]);

        return view('admin.dashboard', compact(
            'totalProducts', 'totalClients', 'outOfStock', 'lowStock',
            'stockByCategory', 'newClientsByMonth'
        ));
    }
}
