<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_products');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        if (! $user->checkPermissionTo('view_products')) {
            return false;
        }

        // Super admins can view all products
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only view products from their stores
        return $user->stores()->where('stores.id', $product->store_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_products');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        if (! $user->checkPermissionTo('update_products')) {
            return false;
        }

        // Super admins can update all products
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only update products from their stores
        return $user->stores()->where('stores.id', $product->store_id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        if (! $user->checkPermissionTo('delete_products')) {
            return false;
        }

        // Super admins can delete all products
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only delete products from their stores
        return $user->stores()->where('stores.id', $product->store_id)->exists();
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete_products');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        if (! $user->checkPermissionTo('restore_products')) {
            return false;
        }

        // Super admins can restore all products
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only restore products from their stores
        return $user->stores()->where('stores.id', $product->store_id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        if (! $user->checkPermissionTo('force_delete_products')) {
            return false;
        }

        // Super admins can force delete all products
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only force delete products from their stores
        return $user->stores()->where('stores.id', $product->store_id)->exists();
    }
}
