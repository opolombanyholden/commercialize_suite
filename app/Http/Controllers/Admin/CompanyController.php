<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:companies.view')->only(['index', 'show']);
        $this->middleware('permission:companies.create')->only(['create', 'store']);
        $this->middleware('permission:companies.edit')->only(['edit', 'update']);
        $this->middleware('permission:companies.delete')->only('destroy');
    }

    /**
     * Liste des entreprises (super admin seulement)
     */
    public function index(Request $request): View
    {
        $query = Company::withCount(['users', 'sites']);

        // Recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $companies = $query->latest()->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Formulaire de création
     */
    public function create(): View
    {
        return view('admin.companies.create');
    }

    /**
     * Enregistrer une nouvelle entreprise
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        // Upload logo
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $company = Company::create($data);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('success', 'Entreprise créée avec succès.');
    }

    /**
     * Afficher une entreprise
     */
    public function show(Company $company): View
    {
        $company->load(['sites', 'users.roles']);

        $stats = [
            'users_count' => $company->users()->count(),
            'sites_count' => $company->sites()->count(),
            'products_count' => $company->products()->count(),
            'clients_count' => $company->clients()->count(),
            'invoices_count' => $company->invoices()->count(),
            'total_revenue' => $company->invoices()
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
        ];

        return view('admin.companies.show', compact('company', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Company $company): View
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Mettre à jour une entreprise
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $data = $request->validated();

        // Upload nouveau logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        // Supprimer le logo si demandé
        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }

        $company->update($data);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('success', 'Entreprise mise à jour avec succès.');
    }

    /**
     * Supprimer une entreprise
     */
    public function destroy(Company $company): RedirectResponse
    {
        // Vérifier qu'il n'y a pas d'utilisateurs actifs
        if ($company->users()->where('is_active', true)->exists()) {
            return back()->with('error', 'Impossible de supprimer une entreprise avec des utilisateurs actifs.');
        }

        // Supprimer le logo
        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Entreprise supprimée avec succès.');
    }

    /**
     * Activer/Désactiver une entreprise
     */
    public function toggleStatus(Company $company): RedirectResponse
    {
        $company->update(['is_active' => !$company->is_active]);

        $status = $company->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "L'entreprise a été {$status}.");
    }

    /**
     * Paramètres de l'entreprise (pour l'utilisateur connecté)
     */
    public function settings(Request $request): View
    {
        $company = $request->user()->company;

        return view('admin.companies.settings', compact('company'));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function updateSettings(UpdateCompanyRequest $request): RedirectResponse
    {
        $company = $request->user()->company;
        $data = $request->validated();

        // Supprimer le logo existant si demandé
        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }

        // Upload logo
        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        // Supprimer les champs non-colonnes avant mise à jour
        unset($data['logo'], $data['remove_logo']);

        $company->update($data);

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }
}
