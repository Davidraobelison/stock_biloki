<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->orderByRaw('CASE WHEN stock_quantity <= 0 THEN 0 WHEN stock_quantity <= stock_alert THEN 1 ELSE 2 END')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.stock.index', compact('products'));
    }

    public function show(Product $product)
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('admin.stock.show', compact('product', 'movements'));
    }

    public function adjust(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type'     => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note'     => 'nullable|string|max:255',
        ]);

        $before   = $product->stock_quantity;
        $quantity = $validated['quantity'];

        if ($validated['type'] === 'out' && $before < $quantity) {
            return back()->withErrors(['quantity' => 'Stock insuffisant (disponible : ' . $before . ').']);
        }

        $after = $validated['type'] === 'in' ? $before + $quantity : $before - $quantity;

        $product->update(['stock_quantity' => $after]);

        StockMovement::create([
            'product_id'      => $product->id,
            'user_id'         => auth()->id(),
            'type'            => $validated['type'],
            'quantity'        => $quantity,
            'before_quantity' => $before,
            'after_quantity'  => $after,
            'note'            => $validated['note'],
        ]);

        return back()->with('success', 'Stock mis à jour avec succès.');
    }
}
