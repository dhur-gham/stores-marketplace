<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_stores');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Store $store): bool
    {
        if (! $user->checkPermissionTo('view_stores')) {
            return false;
        }

        // Super admins can view all stores
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only view stores they manage
        return $user->stores()->where('stores.id', $store->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_stores');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Store $store): bool
    {
        if (! $user->checkPermissionTo('update_stores')) {
            return false;
        }

        // Super admins can update all stores
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only update stores they manage
        return $user->stores()->where('stores.id', $store->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Store $store): bool
    {
        if (! $user->checkPermissionTo('delete_stores')) {
            return false;
        }

        // Super admins can delete all stores
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only delete stores they manage
        return $user->stores()->where('stores.id', $store->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Store $store): bool
    {
        if (! $user->checkPermissionTo('restore_stores')) {
            return false;
        }

        // Super admins can restore all stores
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only restore stores they manage
        return $user->stores()->where('stores.id', $store->id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Store $store): bool
    {
        if (! $user->checkPermissionTo('force_delete_stores')) {
            return false;
        }

        // Super admins can force delete all stores
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only force delete stores they manage
        return $user->stores()->where('stores.id', $store->id)->exists();
    }
}
