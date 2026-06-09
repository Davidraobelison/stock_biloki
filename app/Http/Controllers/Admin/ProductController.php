<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($search = $request->input('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products   = $query->latest()->paginate(20, ['*'])->withQueryString();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_alert'    => 'required|integer|min:0',
            'category_id'    => 'required|exists:categories,id',
            'image'          => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);
        $product->update(['code' => 'PRD-' . str_pad($product->id, 4, '0', STR_PAD_LEFT)]);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produit créé avec succès.');
    }

    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'stock_alert'    => 'required|integer|min:0',
            'category_id'    => 'required|exists:categories,id',
            'image'          => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name'], $product->id);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produit modifié avec succès.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produit supprimé.');
    }

    public function export()
    {
        $products = Product::with('category')->orderBy('name', 'asc')->get();

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Code', 'Nom', 'Catégorie', 'Prix (Ar)', 'Stock', 'Seuil alerte', 'Statut', 'Créé le'], ';');

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->code ?? '',
                    $product->name,
                    $product->category?->name ?? '',
                    $product->price,
                    $product->stock_quantity,
                    $product->stock_alert,
                    match($product->status) {
                        'out_of_stock' => 'Rupture',
                        'low_stock'    => 'Stock faible',
                        default        => 'En stock',
                    },
                    $product->created_at->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        }, 'produits_' . now()->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ], 'attachment');
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');

        $products = Product::where('stock_quantity', '>', 0)
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                      ->orWhere('code', 'ilike', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'code', 'name', 'price', 'stock_quantity']);

        return response()->json($products);
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (Product::where('slug', $slug)
                       ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                       ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
