<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:categories.view')->only(['index', 'show']);
        $this->middleware('permission:categories.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Liste des catégories
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $categories = Category::where('company_id', $companyId)
            ->with('parent')
            ->withCount('products')
            ->rootCategories()
            ->ordered()
            ->get();

        // Charger les sous-catégories récursivement
        $categories->each(function ($category) {
            $category->load(['children' => function ($query) {
                $query->withCount('products')->ordered();
            }]);
        });

        return view('products.categories.index', compact('categories'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $parentCategories = Category::where('company_id', $companyId)
            ->rootCategories()
            ->active()
            ->ordered()
            ->get();

        return view('products.categories.create', compact('parentCategories'));
    }

    /**
     * Enregistrer une nouvelle catégorie
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;
        $data['slug'] = Str::slug($data['name']);

        // Déterminer l'ordre de tri
        $lastOrder = Category::where('company_id', $data['company_id'])
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('sort_order');
        $data['sort_order'] = ($lastOrder ?? 0) + 1;

        // Upload image
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store('categories/' . $data['company_id'], 'public');
        }

        $category = Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Afficher une catégorie
     */
    public function show(Request $request, Category $category): View
    {
        $this->authorizeCompany($request, $category);

        $category->load(['parent', 'children.products', 'products']);

        return view('products.categories.show', compact('category'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, Category $category): View
    {
        $this->authorizeCompany($request, $category);

        $companyId = $request->user()->company_id;

        // Exclure la catégorie actuelle et ses enfants des parents possibles
        $excludeIds = collect([$category->id]);
        $category->children->each(function ($child) use (&$excludeIds) {
            $excludeIds->push($child->id);
        });

        $parentCategories = Category::where('company_id', $companyId)
            ->whereNotIn('id', $excludeIds)
            ->rootCategories()
            ->active()
            ->ordered()
            ->get();

        return view('products.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Mettre à jour une catégorie
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeCompany($request, $category);

        $data = $request->validated();

        // Upload image
        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')
                ->store('categories/' . $category->company_id, 'public');
        }

        // Supprimer image si demandé
        if ($request->boolean('remove_image') && $category->image_path) {
            Storage::disk('public')->delete($category->image_path);
            $data['image_path'] = null;
        }

        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Supprimer une catégorie
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCompany($request, $category);

        // Vérifier qu'il n'y a pas de produits
        if ($category->products()->exists()) {
            return back()->with('error', 'Impossible de supprimer une catégorie contenant des produits.');
        }

        // Vérifier qu'il n'y a pas de sous-catégories
        if ($category->children()->exists()) {
            return back()->with('error', 'Impossible de supprimer une catégorie avec des sous-catégories.');
        }

        // Supprimer l'image
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Activer/Désactiver une catégorie
     */
    public function toggleStatus(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCompany($request, $category);

        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "La catégorie a été {$status}.");
    }

    /**
     * Réordonner les catégories (AJAX)
     */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:categories,id'],
            'categories.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->categories as $item) {
            $category = Category::find($item['id']);
            
            // Vérifier l'accès
            if ($category->company_id !== $request->user()->company_id) {
                continue;
            }

            $category->update(['sort_order' => $item['order']]);
        }

        return back()->with('success', 'Ordre mis à jour.');
    }

    /**
     * Vérifier que la catégorie appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, Category $category): void
    {
        if ($category->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }
    }
}
