<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tax\StoreTaxRequest;
use App\Http\Requests\Tax\UpdateTaxRequest;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:taxes.view')->only(['index', 'show']);
        $this->middleware('permission:taxes.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Liste des taxes
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $taxes = Tax::where('company_id', $companyId)
            ->ordered()
            ->get();

        return view('settings.taxes.index', compact('taxes'));
    }

    /**
     * Formulaire de création
     */
    public function create(): View
    {
        return view('settings.taxes.create');
    }

    /**
     * Enregistrer une nouvelle taxe
     */
    public function store(StoreTaxRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        // Déterminer l'ordre
        $lastOrder = Tax::where('company_id', $data['company_id'])->max('sort_order');
        $data['sort_order'] = ($lastOrder ?? 0) + 1;

        // Si c'est la taxe par défaut, retirer le statut des autres
        if (!empty($data['is_default']) && $data['is_default']) {
            Tax::where('company_id', $data['company_id'])
                ->update(['is_default' => false]);
        }

        Tax::create($data);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Taxe créée avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, Tax $tax): View
    {
        $this->authorizeCompany($request, $tax);

        return view('settings.taxes.edit', compact('tax'));
    }

    /**
     * Mettre à jour une taxe
     */
    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        $this->authorizeCompany($request, $tax);

        $data = $request->validated();

        // Si c'est la taxe par défaut, retirer le statut des autres
        if (!empty($data['is_default']) && $data['is_default']) {
            Tax::where('company_id', $tax->company_id)
                ->where('id', '!=', $tax->id)
                ->update(['is_default' => false]);
        }

        $tax->update($data);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Taxe mise à jour avec succès.');
    }

    /**
     * Supprimer une taxe
     */
    public function destroy(Request $request, Tax $tax): RedirectResponse
    {
        $this->authorizeCompany($request, $tax);

        // Vérifier que la taxe n'est pas utilisée dans des documents
        // On pourrait vérifier dans quote_taxes et invoice_taxes
        
        $tax->delete();

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Taxe supprimée avec succès.');
    }

    /**
     * Activer/Désactiver une taxe
     */
    public function toggleStatus(Request $request, Tax $tax): RedirectResponse
    {
        $this->authorizeCompany($request, $tax);

        $tax->update(['is_active' => !$tax->is_active]);

        $status = $tax->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "La taxe a été {$status}.");
    }

    /**
     * Définir comme taxe par défaut
     */
    public function setDefault(Request $request, Tax $tax): RedirectResponse
    {
        $this->authorizeCompany($request, $tax);

        $tax->makeDefault();

        return back()->with('success', 'Taxe définie par défaut.');
    }

    /**
     * Vérifier que la taxe appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, Tax $tax): void
    {
        if ($tax->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à cette taxe.');
        }
    }
}
