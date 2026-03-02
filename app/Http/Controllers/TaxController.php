<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaxRequest;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:taxes.view')->only(['index', 'show']);
        $this->middleware('permission:taxes.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $taxes = Tax::where('company_id', auth()->user()->company_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('taxes.index', compact('taxes'));
    }

    public function create()
    {
        return view('taxes.create');
    }

    public function store(TaxRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        // Si c'est la taxe par défaut, retirer le statut des autres
        if ($data['is_default'] ?? false) {
            Tax::where('company_id', $data['company_id'])
                ->update(['is_default' => false]);
        }

        $tax = Tax::create($data);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Taxe créée avec succès.');
    }

    public function show(Tax $tax)
    {
        $this->authorize('view', $tax);

        return view('taxes.show', compact('tax'));
    }

    public function edit(Tax $tax)
    {
        $this->authorize('update', $tax);

        return view('taxes.edit', compact('tax'));
    }

    public function update(TaxRequest $request, Tax $tax)
    {
        $this->authorize('update', $tax);

        $data = $request->validated();

        // Si c'est la taxe par défaut, retirer le statut des autres
        if ($data['is_default'] ?? false) {
            Tax::where('company_id', $tax->company_id)
                ->where('id', '!=', $tax->id)
                ->update(['is_default' => false]);
        }

        $tax->update($data);

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Taxe mise à jour avec succès.');
    }

    public function destroy(Tax $tax)
    {
        $this->authorize('delete', $tax);

        // Vérifier si la taxe est utilisée
        if ($tax->products()->exists()) {
            return back()->with('error', 'Cette taxe est utilisée par des produits et ne peut pas être supprimée.');
        }

        $tax->delete();

        return redirect()
            ->route('taxes.index')
            ->with('success', 'Taxe supprimée avec succès.');
    }

    /**
     * Toggle statut actif
     */
    public function toggleActive(Tax $tax)
    {
        $this->authorize('update', $tax);

        $tax->update(['is_active' => !$tax->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * Définir comme taxe par défaut
     */
    public function setDefault(Tax $tax)
    {
        $this->authorize('update', $tax);

        $tax->makeDefault();

        return back()->with('success', 'Taxe définie par défaut.');
    }
}
