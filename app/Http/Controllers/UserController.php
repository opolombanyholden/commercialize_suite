<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.edit')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = User::where('company_id', auth()->user()->company_id)
            ->with('roles');

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::whereNotIn('name', ['super_admin'])->orderBy('name')->get();
        
        $sites = Site::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('users.create', compact('roles', 'sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'exists:roles,name'],
            'sites' => ['required', 'array', 'min:1'],
            'sites.*' => ['exists:sites,id'],
            'primary_site' => ['required', 'exists:sites,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => auth()->user()->company_id,
            'version' => auth()->user()->version,
            'phone' => $request->phone,
            'job_title' => $request->job_title,
            'language' => 'fr',
            'timezone' => 'Africa/Libreville',
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Assigner le rôle
        $user->assignRole($request->role);

        // Assigner les sites
        foreach ($request->sites as $siteId) {
            $user->sites()->attach($siteId, [
                'is_primary' => $siteId == $request->primary_site,
            ]);
        }

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'sites', 'company']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::whereNotIn('name', ['super_admin'])->orderBy('name')->get();
        
        $sites = Site::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $user->load(['roles', 'sites']);

        return view('users.edit', compact('user', 'roles', 'sites'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'exists:roles,name'],
            'sites' => ['required', 'array', 'min:1'],
            'sites.*' => ['exists:sites,id'],
            'primary_site' => ['required', 'exists:sites,id'],
            'is_active' => ['boolean'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'job_title' => $request->job_title,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Mettre à jour le rôle
        $user->syncRoles([$request->role]);

        // Mettre à jour les sites
        $sitesData = [];
        foreach ($request->sites as $siteId) {
            $sitesData[$siteId] = ['is_primary' => $siteId == $request->primary_site];
        }
        $user->sites()->sync($sitesData);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Empêcher de se supprimer soi-même
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Vérifier s'il y a des documents liés
        if ($user->quotes()->exists() || $user->invoices()->exists()) {
            return back()->with('error', 'Cet utilisateur a des documents associés et ne peut pas être supprimé.');
        }

        // Supprimer l'avatar
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Toggle statut actif
     */
    public function toggleActive(User $user)
    {
        $this->authorize('update', $user);

        // Empêcher de se désactiver soi-même
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(User $user)
    {
        $this->authorize('update', $user);

        $newPassword = \Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);

        // TODO: Envoyer par email

        return back()->with('success', "Mot de passe réinitialisé : {$newPassword}");
    }
}
