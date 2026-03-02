<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['create', 'store']);
        $this->middleware('permission:products.edit')->only(['edit', 'update']);
        $this->middleware('permission:products.delete')->only('destroy');
    }

    /**
     * Liste des produits
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = Product::where('company_id', $companyId)
            ->with(['category', 'tax']);

        // Recherche
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filtre par catégorie
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        // Filtre par type
        if ($type = $request->input('type')) {
            if ($type === 'product') {
                $query->products();
            } elseif ($type === 'service') {
                $query->services();
            }
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Filtre stock bas
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Tri
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $products = $query->paginate(15)->withQueryString();

        $categories = Category::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $categories = Category::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        $taxes = Tax::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('products.create', compact('categories', 'taxes'));
    }

    /**
     * Enregistrer un nouveau produit
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id']          = $request->user()->company_id;
        $data['slug']                = $this->generateUniqueSlug($data['name'], $data['company_id']);
        $data['is_active']           = $request->boolean('is_active');
        $data['track_inventory']     = $request->boolean('track_inventory');
        $data['is_published_online'] = $request->boolean('is_published_online');

        // Générer le SKU si non fourni
        if (empty($data['sku'])) {
            $data['sku'] = Product::generateSku($data['company_id']);
        }

        // Upload image principale
        if ($request->hasFile('main_image')) {
            $data['main_image_path'] = $request->file('main_image')
                ->store('products/' . $data['company_id'], 'public');
        }

        $product = Product::create($data);

        // Upload images additionnelles
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products/' . $data['company_id'], 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produit créé avec succès.');
    }

    /**
     * Afficher un produit
     */
    public function show(Request $request, Product $product): View
    {
        $this->authorizeCompany($request, $product);

        $product->load(['category', 'tax', 'images', 'variants']);

        // Statistiques
        $stats = [
            'total_sold' => $product->sales_count,
            'views' => $product->views_count,
            'stock_movements' => 0,
        ];

        return view('products.show', compact('product', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, Product $product): View
    {
        $this->authorizeCompany($request, $product);

        $companyId = $request->user()->company_id;

        $categories = Category::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        $taxes = Tax::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        $product->load(['images', 'variants']);

        return view('products.edit', compact('product', 'categories', 'taxes'));
    }

    /**
     * Mettre à jour un produit
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorizeCompany($request, $product);

        $data = $request->validated();

        // Forcer les valeurs booléennes (les hidden inputs garantissent leur présence)
        $data['is_active']           = $request->boolean('is_active');
        $data['track_inventory']     = $request->boolean('track_inventory');
        $data['is_published_online'] = $request->boolean('is_published_online');

        // Régénérer le slug si le nom a changé
        if (isset($data['name']) && Str::slug($data['name']) !== $product->slug) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $product->company_id, $product->id);
        }

        // Upload nouvelle image principale
        if ($request->hasFile('main_image')) {
            // Supprimer l'ancienne
            if ($product->main_image_path) {
                Storage::disk('public')->delete($product->main_image_path);
            }
            $data['main_image_path'] = $request->file('main_image')
                ->store('products/' . $product->company_id, 'public');
        }

        // Supprimer l'image si demandé
        if ($request->boolean('remove_main_image') && $product->main_image_path) {
            Storage::disk('public')->delete($product->main_image_path);
            $data['main_image_path'] = null;
        }

        $product->update($data);

        // Upload images additionnelles
        if ($request->hasFile('images')) {
            $lastOrder = $product->images()->max('sort_order') ?? -1;
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products/' . $product->company_id, 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => $lastOrder + $index + 1,
                ]);
            }
        }

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Supprimer un produit
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeCompany($request, $product);

        // Supprimer les images
        if ($product->main_image_path) {
            Storage::disk('public')->delete($product->main_image_path);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produit supprimé avec succès.');
    }

    /**
     * Activer/Désactiver un produit
     */
    public function toggleStatus(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeCompany($request, $product);

        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Le produit a été {$status}.");
    }

    /**
     * Publier/Dépublier en ligne
     */
    public function toggleOnline(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeCompany($request, $product);

        if (!$request->user()->hasFeature('ecommerce')) {
            return back()->with('error', 'La publication en ligne nécessite une version supérieure.');
        }

        $product->update(['is_published_online' => !$product->is_published_online]);

        $status = $product->is_published_online ? 'publié' : 'retiré de la boutique';

        return back()->with('success', "Le produit a été {$status}.");
    }

    /**
     * Dupliquer un produit
     */
    public function duplicate(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeCompany($request, $product);

        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' (copie)';
        $newProduct->slug = $this->generateUniqueSlug($newProduct->name, $product->company_id);
        $newProduct->sku = Product::generateSku($product->company_id);
        $newProduct->is_published_online = false;
        $newProduct->views_count = 0;
        $newProduct->sales_count = 0;
        $newProduct->save();

        return redirect()
            ->route('products.edit', $newProduct)
            ->with('success', 'Produit dupliqué avec succès. Modifiez les informations nécessaires.');
    }

    /**
     * Supprimer une image additionnelle
     */
    public function deleteImage(Request $request, Product $product, int $imageId): RedirectResponse
    {
        $this->authorizeCompany($request, $product);

        $image = $product->images()->findOrFail($imageId);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image supprimée.');
    }

    /**
     * Définir une image comme image principale
     */
    public function setImagePrimary(Request $request, Product $product, int $imageId): RedirectResponse
    {
        $this->authorizeCompany($request, $product);

        $image = $product->images()->findOrFail($imageId);
        $image->makePrimary();

        return back()->with('success', 'Image principale mise à jour.');
    }

    /**
     * Générer un slug unique pour la company (avec suffixe numérique si collision)
     */
    protected function generateUniqueSlug(string $name, int $companyId, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            Product::where('company_id', $companyId)
                ->where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Vérifier que le produit appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, Product $product): void
    {
        if ($product->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à ce produit.');
        }
    }
}
