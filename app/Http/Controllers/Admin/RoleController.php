<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.edit')->only(['edit', 'update']);
        $this->middleware('permission:roles.delete')->only('destroy');
    }

    /**
     * Liste des rôles
     */
    public function index(): View
    {
        $roles = Role::with('permissions:id,name')
            ->withCount('permissions', 'users')
            ->whereNotIn('name', ['super_admin'])
            ->get();

        $allPermissions = Permission::orderBy('name')->get(['id', 'name'])
            ->groupBy(function ($permission) {
                return explode('.', $permission->name)[0];
            });

        return view('admin.roles.index', compact('roles', 'allPermissions'));
    }

    /**
     * Afficher un rôle
     */
    public function show(Role $role): View
    {
        $role->load('permissions');

        $users = $role->users()
            ->where('company_id', auth()->user()->company_id)
            ->get();

        // Grouper les permissions par module
        $permissionsByModule = $role->permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.roles.show', compact('role', 'users', 'permissionsByModule'));
    }

    /**
     * Formulaire de création
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Enregistrer un nouveau rôle
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Role $role): View
    {
        // Ne pas permettre l'édition des rôles système
        if (in_array($role->name, ['super_admin', 'company_admin'])) {
            abort(403, 'Ce rôle ne peut pas être modifié.');
        }

        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        // Ne pas permettre l'édition des rôles système
        if (in_array($role->name, ['super_admin', 'company_admin'])) {
            return back()->with('error', 'Ce rôle ne peut pas être modifié.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Supprimer un rôle
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Ne pas permettre la suppression des rôles système
        $systemRoles = ['super_admin', 'company_admin', 'site_manager', 'accountant', 
                       'sales_manager', 'salesperson', 'warehouse_manager', 'viewer'];

        if (in_array($role->name, $systemRoles)) {
            return back()->with('error', 'Les rôles système ne peuvent pas être supprimés.');
        }

        // Vérifier qu'aucun utilisateur n'a ce rôle
        if ($role->users()->exists()) {
            return back()->with('error', 'Impossible de supprimer un rôle assigné à des utilisateurs.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }
}
