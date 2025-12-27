<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_orders');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if (! $user->checkPermissionTo('view_orders')) {
            return false;
        }

        // Super admins can view all orders
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only view orders from their stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_orders');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        if (! $user->checkPermissionTo('update_orders')) {
            return false;
        }

        // Super admins can update all orders
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only update orders from their stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        if (! $user->checkPermissionTo('delete_orders')) {
            return false;
        }

        // Super admins can delete all orders
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only delete orders from their stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete_any_orders');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        if (! $user->checkPermissionTo('restore_orders')) {
            return false;
        }

        // Super admins can restore all orders
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only restore orders from their stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can bulk restore models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore_any_orders');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        if (! $user->checkPermissionTo('force_delete_orders')) {
            return false;
        }

        // Super admins can force delete all orders
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only force delete orders from their stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can bulk permanently delete models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force_delete_any_orders');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Order $order): bool
    {
        if (! $user->checkPermissionTo('replicate_orders')) {
            return false;
        }

        // Super admins can replicate all orders
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only replicate orders from their stores
        return $user->stores()->where('stores.id', $order->store_id)->exists();
    }

    /**
     * Determine whether the user can reorder models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder_orders');
    }
}
