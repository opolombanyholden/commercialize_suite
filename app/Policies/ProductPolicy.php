<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('products.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('products.view') 
            && $user->company_id === $product->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('products.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('products.edit') 
            && $user->company_id === $product->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('products.delete') 
            && $user->company_id === $product->company_id;
    }

    /**
     * Determine whether the user can publish the product online.
     */
    public function publishOnline(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('products.publish_online') 
            && $user->company_id === $product->company_id
            && $user->hasFeature('ecommerce');
    }
}
