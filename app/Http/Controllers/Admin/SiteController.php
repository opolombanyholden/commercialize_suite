<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreSiteRequest;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sites.view')->only(['index', 'show']);
        $this->middleware('permission:sites.create')->only(['create', 'store']);
        $this->middleware('permission:sites.edit')->only(['edit', 'update']);
        $this->middleware('permission:sites.delete')->only('destroy');
    }

    /**
     * Liste des sites
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = Site::where('company_id', $companyId)
            ->with('manager')
            ->withCount('users');

        // Recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filtre par type
        if ($request->input('type') === 'headquarters') {
            $query->headquarters();
        } elseif ($request->input('type') === 'warehouse') {
            $query->warehouses();
        } elseif ($request->input('type') === 'store') {
            $query->stores();
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $sites = $query->ordered()->paginate(15);

        return view('admin.sites.index', compact('sites'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;
        
        $managers = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.sites.create', compact('managers'));
    }

    /**
     * Enregistrer un nouveau site
     */
    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        // Si c'est le premier site, le définir comme siège
        $sitesCount = Site::where('company_id', $data['company_id'])->count();
        if ($sitesCount === 0) {
            $data['is_headquarters'] = true;
        }

        $site = Site::create($data);

        return redirect()
            ->route('admin.sites.show', $site)
            ->with('success', 'Site créé avec succès.');
    }

    /**
     * Afficher un site
     */
    public function show(Request $request, Site $site): View
    {
        $this->authorizeCompany($request, $site);

        $site->load(['manager', 'users']);

        $stats = [
            'users_count' => $site->users()->count(),
            'quotes_count' => $site->quotes()->count(),
            'invoices_count' => $site->invoices()->count(),
            'total_revenue' => $site->invoices()
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
        ];

        return view('admin.sites.show', compact('site', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, Site $site): View
    {
        $this->authorizeCompany($request, $site);

        $companyId = $request->user()->company_id;
        
        $managers = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.sites.edit', compact('site', 'managers'));
    }

    /**
     * Mettre à jour un site
     */
    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $this->authorizeCompany($request, $site);

        $data = $request->validated();

        // Si on définit ce site comme siège, retirer le statut des autres
        if (!empty($data['is_headquarters']) && $data['is_headquarters']) {
            Site::where('company_id', $site->company_id)
                ->where('id', '!=', $site->id)
                ->update(['is_headquarters' => false]);
        }

        $site->update($data);

        return redirect()
            ->route('admin.sites.show', $site)
            ->with('success', 'Site mis à jour avec succès.');
    }

    /**
     * Supprimer un site
     */
    public function destroy(Request $request, Site $site): RedirectResponse
    {
        $this->authorizeCompany($request, $site);

        // Ne pas supprimer le siège principal s'il y a d'autres sites
        if ($site->is_headquarters) {
            $otherSites = Site::where('company_id', $site->company_id)
                ->where('id', '!=', $site->id)
                ->exists();

            if ($otherSites) {
                return back()->with('error', 'Impossible de supprimer le siège principal. Définissez d\'abord un autre site comme siège.');
            }
        }

        // Vérifier qu'il n'y a pas de documents liés
        if ($site->invoices()->exists() || $site->quotes()->exists()) {
            return back()->with('error', 'Impossible de supprimer un site avec des documents existants.');
        }

        $site->delete();

        return redirect()
            ->route('admin.sites.index')
            ->with('success', 'Site supprimé avec succès.');
    }

    /**
     * Activer/Désactiver un site
     */
    public function toggleStatus(Request $request, Site $site): RedirectResponse
    {
        $this->authorizeCompany($request, $site);

        $site->update(['is_active' => !$site->is_active]);

        $status = $site->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Le site a été {$status}.");
    }

    /**
     * Gérer les utilisateurs d'un site
     */
    public function users(Request $request, Site $site): View
    {
        $this->authorizeCompany($request, $site);

        $site->load('users.roles');

        $availableUsers = User::where('company_id', $site->company_id)
            ->whereDoesntHave('sites', function ($q) use ($site) {
                $q->where('site_id', $site->id);
            })
            ->where('is_active', true)
            ->get();

        return view('admin.sites.users', compact('site', 'availableUsers'));
    }

    /**
     * Ajouter un utilisateur au site
     */
    public function addUser(Request $request, Site $site): RedirectResponse
    {
        $this->authorizeCompany($request, $site);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Vérifier que l'utilisateur appartient à la même entreprise
        if ($user->company_id !== $site->company_id) {
            return back()->with('error', 'Cet utilisateur n\'appartient pas à votre entreprise.');
        }

        // Vérifier qu'il n'est pas déjà assigné
        if ($site->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Cet utilisateur est déjà assigné à ce site.');
        }

        $site->users()->attach($user->id);

        return back()->with('success', 'Utilisateur ajouté au site.');
    }

    /**
     * Retirer un utilisateur du site
     */
    public function removeUser(Request $request, Site $site, User $user): RedirectResponse
    {
        $this->authorizeCompany($request, $site);

        $site->users()->detach($user->id);

        return back()->with('success', 'Utilisateur retiré du site.');
    }

    /**
     * Vérifier que le site appartient à l'entreprise de l'utilisateur
     */
    protected function authorizeCompany(Request $request, Site $site): void
    {
        if ($site->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à ce site.');
        }
    }
}
