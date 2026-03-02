<?php

namespace App\Policies;

use App\Models\Tax;
use App\Models\User;

class TaxPolicy
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
        return $user->hasPermissionTo('taxes.view');
    }

    public function view(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo('taxes.view')
            && $user->company_id === $tax->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('taxes.manage');
    }

    public function update(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo('taxes.manage')
            && $user->company_id === $tax->company_id;
    }

    public function delete(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo('taxes.manage')
            && $user->company_id === $tax->company_id;
    }
}
