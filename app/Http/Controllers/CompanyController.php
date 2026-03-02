<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.company');
    }

    /**
     * Afficher les paramètres de l'entreprise
     */
    public function edit()
    {
        $company = auth()->user()->company;

        return view('company.edit', compact('company'));
    }

    /**
     * Mettre à jour les paramètres de l'entreprise
     */
    public function update(CompanyRequest $request)
    {
        $company = auth()->user()->company;
        $data = $request->validated();

        // Upload logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')
                ->store('logos/' . $company->id, 'public');
        }

        $company->update($data);

        return redirect()
            ->route('company.edit')
            ->with('success', 'Paramètres de l\'entreprise mis à jour avec succès.');
    }

    /**
     * Supprimer le logo
     */
    public function deleteLogo()
    {
        $company = auth()->user()->company;

        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $company->update(['logo_path' => null]);
        }

        return back()->with('success', 'Logo supprimé.');
    }
}
