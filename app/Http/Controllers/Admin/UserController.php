<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only('destroy');
    }

    /**
     * Liste des utilisateurs
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $query = User::where('company_id', $companyId)
            ->with(['roles', 'sites']);

        // Recherche
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($role = $request->input('role')) {
            $query->role($role);
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::whereNotIn('name', ['super_admin'])->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $roles = Role::whereNotIn('name', ['super_admin'])->get();
        $sites = Site::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        return view('admin.users.create', compact('roles', 'sites'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;
        $data['password'] = Hash::make($data['password']);
        $data['version'] = $request->user()->version; // Hérite de la version de l'entreprise

        // Upload avatar
        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);

        // Assigner le rôle
        if ($request->filled('role')) {
            $user->assignRole($request->role);
        }

        // Assigner les sites
        if ($request->filled('sites')) {
            $sites = collect($request->sites)->mapWithKeys(function ($siteId, $index) use ($request) {
                return [$siteId => ['is_primary' => $index === 0 || $siteId == $request->primary_site]];
            });
            $user->sites()->sync($sites);
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher un utilisateur
     */
    public function show(Request $request, User $user): View
    {
        $this->authorizeCompany($request, $user);

        $user->load(['roles', 'sites']);

        $stats = [
            'quotes_count' => $user->quotes()->count(),
            'invoices_count' => $user->invoices()->count(),
            'total_sales' => $user->invoices()
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Request $request, User $user): View
    {
        $this->authorizeCompany($request, $user);

        $companyId = $request->user()->company_id;

        $roles = Role::whereNotIn('name', ['super_admin'])->get();
        $sites = Site::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $userSites = $user->sites()->pluck('site_id')->toArray();
        $primarySite = $user->sites()->wherePivot('is_primary', true)->first()?->id;

        return view('admin.users.edit', compact('user', 'roles', 'sites', 'userSites', 'primarySite'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorizeCompany($request, $user);

        $data = $request->validated();

        // Ne pas modifier le mot de passe si non fourni
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Upload avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Supprimer avatar si demandé
        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $data['avatar_path'] = null;
        }

        $user->update($data);

        // Mettre à jour le rôle
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        // Mettre à jour les sites
        if ($request->has('sites')) {
            $sites = collect($request->sites)->mapWithKeys(function ($siteId) use ($request) {
                return [$siteId => ['is_primary' => $siteId == $request->primary_site]];
            });
            $user->sites()->sync($sites);
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeCompany($request, $user);

        // Ne pas se supprimer soi-même
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Supprimer l'avatar
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorizeCompany($request, $user);

        // Ne pas se désactiver soi-même
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "L'utilisateur a été {$status}.");
    }

    /**
     * Profil de l'utilisateur connecté
     */
    public function profile(Request $request): View
    {
        $user = $request->user();
        $user->load(['roles', 'sites']);

        return view('admin.users.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only(['name', 'email', 'phone', 'job_title']);

        // Upload avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Vérifier que l'utilisateur appartient à l'entreprise
     */
    protected function authorizeCompany(Request $request, User $user): void
    {
        if ($user->company_id !== $request->user()->company_id) {
            abort(403, 'Accès non autorisé à cet utilisateur.');
        }
    }
}
