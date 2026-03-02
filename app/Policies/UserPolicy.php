<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users.view')
            && $user->company_id === $model->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $model): bool
    {
        // Un utilisateur ne peut pas modifier quelqu'un avec un rôle plus élevé
        if ($this->hasHigherRole($model, $user)) {
            return false;
        }

        return $user->hasPermissionTo('users.edit')
            && $user->company_id === $model->company_id;
    }

    public function delete(User $user, User $model): bool
    {
        // Ne peut pas se supprimer soi-même
        if ($user->id === $model->id) {
            return false;
        }

        // Un utilisateur ne peut pas supprimer quelqu'un avec un rôle plus élevé
        if ($this->hasHigherRole($model, $user)) {
            return false;
        }

        return $user->hasPermissionTo('users.delete')
            && $user->company_id === $model->company_id;
    }

    /**
     * Vérifier si le modèle a un rôle plus élevé que l'utilisateur
     */
    protected function hasHigherRole(User $model, User $user): bool
    {
        $roleLevels = [
            'super_admin' => 100,
            'company_admin' => 90,
            'site_manager' => 70,
            'accountant' => 60,
            'sales_manager' => 50,
            'salesperson' => 40,
            'warehouse_manager' => 50,
            'viewer' => 10,
        ];

        $userLevel = 0;
        $modelLevel = 0;

        foreach ($user->roles as $role) {
            $level = $roleLevels[$role->name] ?? 0;
            $userLevel = max($userLevel, $level);
        }

        foreach ($model->roles as $role) {
            $level = $roleLevels[$role->name] ?? 0;
            $modelLevel = max($modelLevel, $level);
        }

        return $modelLevel > $userLevel;
    }
}
