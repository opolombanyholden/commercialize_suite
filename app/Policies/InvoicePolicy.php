<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('invoices.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo('invoices.view') 
            && $user->company_id === $invoice->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('invoices.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo('invoices.edit') 
            && $user->company_id === $invoice->company_id
            && $invoice->status === 'draft';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo('invoices.delete') 
            && $user->company_id === $invoice->company_id
            && $invoice->status === 'draft'
            && !$invoice->payments()->exists();
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo('invoices.send') 
            && $user->company_id === $invoice->company_id;
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $user->hasPermissionTo('invoices.mark_paid') 
            && $user->company_id === $invoice->company_id;
    }
}
