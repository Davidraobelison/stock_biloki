<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaisseMouvement;
use App\Models\CaisseSession;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['client', 'items'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'ilike', "%{$search}%")
                  ->orWhereHas('client', fn ($q) => $q->where('name', 'ilike', "%{$search}%"));
            });
        }

        $sales = $query->paginate(20)->withQueryString();

        return view('admin.sales.index', compact('sales'));
    }

    public function create()
    {
        $clients  = Client::orderBy('name')->get(['id', 'name']);
        $products = Product::with('category')
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock_quantity', 'category_id']);

        $productsJson = $products->map(fn ($p) => [
            'id'         => $p->id,
            'name'       => $p->name,
            'unit_price' => (float) $p->price,
            'stock'      => (int) $p->stock_quantity,
        ]);

        return view('admin.sales.create', compact('clients', 'products', 'productsJson'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'          => 'nullable|exists:clients,id',
            'payment_method'     => 'required|in:especes,mvola,orange_money,airtel_money,carte,virement',
            'notes'              => 'nullable|string|max:500',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        // Vérification du stock avant toute écriture
        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->stock_quantity < $item['quantity']) {
                return back()->withInput()->withErrors([
                    'items' => "Stock insuffisant pour « {$product->name} » (disponible : {$product->stock_quantity}).",
                ]);
            }
        }

        $sale = Sale::create([
            'reference'      => 'TEMP',
            'client_id'      => $validated['client_id'] ?? null,
            'user_id'        => auth()->id(),
            'status'         => 'completed',
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'] ?? null,
            'total_amount'   => 0,
        ]);

        $sale->update([
            'reference' => 'VNT-' . now()->format('Y') . '-' . str_pad($sale->id, 4, '0', STR_PAD_LEFT),
        ]);

        $total = 0;

        foreach ($validated['items'] as $item) {
            $product  = Product::findOrFail($item['product_id']);
            $qty      = $item['quantity'];
            $price    = $product->price;
            $subtotal = $price * $qty;
            $total   += $subtotal;
            $before   = $product->stock_quantity;
            $after    = $before - $qty;

            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $product->id,
                'quantity'   => $qty,
                'unit_price' => $price,
                'subtotal'   => $subtotal,
            ]);

            $product->update(['stock_quantity' => $after]);

            StockMovement::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'type'            => 'out',
                'quantity'        => $qty,
                'before_quantity' => $before,
                'after_quantity'  => $after,
                'note'            => "Vente {$sale->reference}",
            ]);
        }

        $sale->update(['total_amount' => $total]);

        // Mouvement de caisse automatique si session ouverte
        $activeSession = CaisseSession::current();
        if ($activeSession) {
            $paymentLabel = Sale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method;
            CaisseMouvement::create([
                'session_id'  => $activeSession->id,
                'user_id'     => auth()->id(),
                'sale_id'     => $sale->id,
                'type'        => 'entree',
                'amount'      => $total,
                'description' => "Vente {$sale->reference} ({$paymentLabel})",
            ]);
        }

        return redirect()->route('admin.sales.show', $sale)
                         ->with('success', "Vente {$sale->reference} enregistrée avec succès.");
    }

    public function show(Sale $sale)
    {
        $sale->load(['client', 'user', 'items.product.category']);

        return view('admin.sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['client', 'user', 'items.product']);

        return view('admin.sales.receipt', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        if ($sale->isCancelled()) {
            return back()->with('error', 'Cette vente est déjà annulée.');
        }

        $sale->load('items.product');

        foreach ($sale->items as $item) {
            $product = $item->product;
            $before  = $product->stock_quantity;
            $after   = $before + $item->quantity;

            $product->update(['stock_quantity' => $after]);

            StockMovement::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'type'            => 'in',
                'quantity'        => $item->quantity,
                'before_quantity' => $before,
                'after_quantity'  => $after,
                'note'            => "Annulation vente {$sale->reference}",
            ]);
        }

        $sale->update(['status' => 'cancelled']);

        return redirect()->route('admin.sales.index')
                         ->with('success', "Vente {$sale->reference} annulée. Stock restauré.");
    }
}
