<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
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
        return $user->hasPermissionTo('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('categories.view')
            && $user->company_id === $category->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('categories.manage');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('categories.manage')
            && $user->company_id === $category->company_id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('categories.manage')
            && $user->company_id === $category->company_id;
    }
}
