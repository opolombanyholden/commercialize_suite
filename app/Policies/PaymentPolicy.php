<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
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
        return $user->hasPermissionTo('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.view')
            && $user->company_id === $payment->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('payments.create');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.edit')
            && $user->company_id === $payment->company_id;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payments.delete')
            && $user->company_id === $payment->company_id;
    }
}
