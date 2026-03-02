<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
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
        return $user->hasPermissionTo('sites.view');
    }

    public function view(User $user, Site $site): bool
    {
        return $user->hasPermissionTo('sites.view')
            && $user->company_id === $site->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sites.create');
    }

    public function update(User $user, Site $site): bool
    {
        return $user->hasPermissionTo('sites.edit')
            && $user->company_id === $site->company_id;
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->hasPermissionTo('sites.delete')
            && $user->company_id === $site->company_id;
    }
}
