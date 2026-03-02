<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['create', 'store']);
        $this->middleware('permission:products.edit')->only(['edit', 'update']);
        $this->middleware('permission:products.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Product::where('company_id', auth()->user()->company_id)
            ->with(['category', 'tax']);

        // Recherche
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtre par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par statut
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'low_stock') {
                $query->lowStock();
            }
        }

        // Tri
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $products = $query->paginate(20)->withQueryString();

        $categories = Category::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxes = Tax::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('products.create', compact('categories', 'taxes'));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Upload image principale
        if ($request->hasFile('main_image')) {
            $data['main_image_path'] = $request->file('main_image')
                ->store('products/' . $data['company_id'], 'public');
        }

        $product = Product::create($data);

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produit créé avec succès.');
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['category', 'tax', 'images', 'variants']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxes = Tax::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('products.edit', compact('product', 'categories', 'taxes'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Upload nouvelle image principale
        if ($request->hasFile('main_image')) {
            // Supprimer l'ancienne image
            if ($product->main_image_path) {
                Storage::disk('public')->delete($product->main_image_path);
            }
            $data['main_image_path'] = $request->file('main_image')
                ->store('products/' . $product->company_id, 'public');
        }

        $product->update($data);

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Supprimer l'image
        if ($product->main_image_path) {
            Storage::disk('public')->delete($product->main_image_path);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produit supprimé avec succès.');
    }

    /**
     * Recherche AJAX pour autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $products = Product::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'type', 'price', 'stock_quantity']);

        return response()->json($products);
    }

    /**
     * Toggle statut actif
     */
    public function toggleActive(Product $product)
    {
        $this->authorize('update', $product);

        $product->update(['is_active' => !$product->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * Dupliquer un produit
     */
    public function duplicate(Product $product)
    {
        $this->authorize('create', Product::class);

        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' (copie)';
        $newProduct->slug = Str::slug($newProduct->name);
        $newProduct->sku = Product::generateSku($product->company_id);
        $newProduct->is_active = false;
        $newProduct->views_count = 0;
        $newProduct->sales_count = 0;
        $newProduct->save();

        return redirect()
            ->route('products.edit', $newProduct)
            ->with('success', 'Produit dupliqué. Vous pouvez maintenant le modifier.');
    }
}
