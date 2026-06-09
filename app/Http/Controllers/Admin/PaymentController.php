<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'today');
        $method = $request->input('method', '');

        $base = Sale::where('status', 'completed');

        match ($period) {
            'week'  => $base->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $base->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'year'  => $base->whereYear('created_at', now()->year),
            default => $base->whereDate('created_at', today()),
        };

        if ($method) {
            $base->where('payment_method', $method);
        }

        $totalRevenu = (float) (clone $base)->sum('total_amount');
        $totalVentes = (clone $base)->count();

        $byMethod = (clone $base)
            ->selectRaw('payment_method, sum(total_amount) as total, count(*) as nb')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->keyBy('payment_method');

        $sales = Sale::with('client')
            ->where('status', 'completed')
            ->when($method, fn ($q) => $q->where('payment_method', $method))
            ->tap(function ($q) use ($period) {
                match ($period) {
                    'week'  => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    'year'  => $q->whereYear('created_at', now()->year),
                    default => $q->whereDate('created_at', today()),
                };
            })
            ->latest()
            ->paginate(20, ['*'])
            ->withQueryString();

        $methods = \App\Models\Sale::PAYMENT_METHODS;

        return view('admin.payments.index', compact(
            'sales', 'byMethod', 'totalRevenu', 'totalVentes', 'period', 'method', 'methods'
        ));
    }
}
