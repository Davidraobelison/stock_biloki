<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['name'], $category->id);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Catégorie modifiée avec succès.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Impossible de supprimer une catégorie contenant des produits.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Catégorie supprimée.');
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (Category::where('slug', $slug)
                        ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                        ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
