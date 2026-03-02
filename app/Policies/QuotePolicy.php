<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('quotes.view');
    }

    public function view(User $user, Quote $quote): bool
    {
        return $user->hasPermissionTo('quotes.view') 
            && $user->company_id === $quote->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('quotes.create');
    }

    public function update(User $user, Quote $quote): bool
    {
        return $user->hasPermissionTo('quotes.edit') 
            && $user->company_id === $quote->company_id
            && $quote->canBeEdited();
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->hasPermissionTo('quotes.delete') 
            && $user->company_id === $quote->company_id
            && $quote->canBeDeleted();
    }

    public function send(User $user, Quote $quote): bool
    {
        return $user->hasPermissionTo('quotes.send') 
            && $user->company_id === $quote->company_id;
    }

    public function convert(User $user, Quote $quote): bool
    {
        return $user->hasPermissionTo('quotes.convert') 
            && $user->company_id === $quote->company_id
            && $quote->canBeConverted();
    }
}
