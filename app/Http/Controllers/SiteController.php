<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteRequest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sites.view')->only(['index', 'show']);
        $this->middleware('permission:sites.create')->only(['create', 'store']);
        $this->middleware('permission:sites.edit')->only(['edit', 'update']);
        $this->middleware('permission:sites.delete')->only(['destroy']);
    }

    public function index()
    {
        $sites = Site::where('company_id', auth()->user()->company_id)
            ->with('manager')
            ->withCount('users')
            ->orderBy('is_headquarters', 'desc')
            ->orderBy('name')
            ->get();

        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        $managers = User::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sites.create', compact('managers'));
    }

    public function store(SiteRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;

        // Si c'est le siège, retirer le statut des autres
        if ($data['is_headquarters'] ?? false) {
            Site::where('company_id', $data['company_id'])
                ->update(['is_headquarters' => false]);
        }

        $site = Site::create($data);

        // Ajouter le créateur à ce site
        auth()->user()->sites()->attach($site->id, ['is_primary' => false]);

        return redirect()
            ->route('sites.show', $site)
            ->with('success', 'Site créé avec succès.');
    }

    public function show(Site $site)
    {
        $this->authorize('view', $site);

        $site->load(['manager', 'users']);

        return view('sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        $this->authorize('update', $site);

        $managers = User::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sites.edit', compact('site', 'managers'));
    }

    public function update(SiteRequest $request, Site $site)
    {
        $this->authorize('update', $site);

        $data = $request->validated();

        // Si c'est le siège, retirer le statut des autres
        if ($data['is_headquarters'] ?? false) {
            Site::where('company_id', $site->company_id)
                ->where('id', '!=', $site->id)
                ->update(['is_headquarters' => false]);
        }

        $site->update($data);

        return redirect()
            ->route('sites.show', $site)
            ->with('success', 'Site mis à jour avec succès.');
    }

    public function destroy(Site $site)
    {
        $this->authorize('delete', $site);

        // Vérifier si c'est le siège
        if ($site->is_headquarters) {
            return back()->with('error', 'Impossible de supprimer le siège social.');
        }

        // Vérifier s'il y a des documents liés
        if ($site->quotes()->exists() || $site->invoices()->exists()) {
            return back()->with('error', 'Ce site a des documents associés et ne peut pas être supprimé.');
        }

        $site->delete();

        return redirect()
            ->route('sites.index')
            ->with('success', 'Site supprimé avec succès.');
    }

    /**
     * Gérer les utilisateurs d'un site
     */
    public function users(Site $site)
    {
        $this->authorize('update', $site);

        $site->load('users');

        $availableUsers = User::where('company_id', $site->company_id)
            ->where('is_active', true)
            ->whereDoesntHave('sites', function ($q) use ($site) {
                $q->where('site_id', $site->id);
            })
            ->orderBy('name')
            ->get();

        return view('sites.users', compact('site', 'availableUsers'));
    }

    /**
     * Ajouter un utilisateur au site
     */
    public function addUser(Request $request, Site $site)
    {
        $this->authorize('update', $site);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'is_primary' => ['boolean'],
        ]);

        $site->users()->attach($request->user_id, [
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return back()->with('success', 'Utilisateur ajouté au site.');
    }

    /**
     * Retirer un utilisateur du site
     */
    public function removeUser(Site $site, User $user)
    {
        $this->authorize('update', $site);

        $site->users()->detach($user->id);

        return back()->with('success', 'Utilisateur retiré du site.');
    }

    /**
     * Toggle statut actif
     */
    public function toggleActive(Site $site)
    {
        $this->authorize('update', $site);

        if ($site->is_headquarters && $site->is_active) {
            return back()->with('error', 'Impossible de désactiver le siège social.');
        }

        $site->update(['is_active' => !$site->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }
}
