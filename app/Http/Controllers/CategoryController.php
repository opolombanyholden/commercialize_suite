<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:categories.view')->only(['index', 'show']);
        $this->middleware('permission:categories.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $categories = Category::where('company_id', auth()->user()->company_id)
            ->whereNull('parent_id')
            ->with('children')
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::where('company_id', auth()->user()->company_id)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('categories.create', compact('parentCategories'));
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store('categories/' . $data['company_id'], 'public');
        }

        $category = Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function show(Category $category)
    {
        $this->authorize('view', $category);

        $category->load(['parent', 'children', 'products' => function ($q) {
            $q->where('is_active', true)->limit(10);
        }]);

        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        $parentCategories = Category::where('company_id', auth()->user()->company_id)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')
                ->store('categories/' . $category->company_id, 'public');
        }

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        // Vérifier s'il y a des produits
        if ($category->products()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
        }

        // Vérifier s'il y a des sous-catégories
        if ($category->children()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette catégorie car elle contient des sous-catégories.');
        }

        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Réorganiser les catégories (drag & drop)
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:categories,id'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->categories as $item) {
            Category::where('id', $item['id'])
                ->where('company_id', auth()->user()->company_id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
