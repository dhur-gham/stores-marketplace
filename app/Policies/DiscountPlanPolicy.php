<?php

namespace App\Policies;

use App\Models\DiscountPlan;
use App\Models\User;

class DiscountPlanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_discount_plans');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DiscountPlan $discount_plan): bool
    {
        if (! $user->checkPermissionTo('view_discount_plans')) {
            return false;
        }

        // Super admins can view all discount plans
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only view discount plans from their stores
        return $user->stores()->where('stores.id', $discount_plan->store_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_discount_plans');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DiscountPlan $discount_plan): bool
    {
        if (! $user->checkPermissionTo('update_discount_plans')) {
            return false;
        }

        // Super admins can update all discount plans
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only update discount plans from their stores
        return $user->stores()->where('stores.id', $discount_plan->store_id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DiscountPlan $discount_plan): bool
    {
        if (! $user->checkPermissionTo('delete_discount_plans')) {
            return false;
        }

        // Super admins can delete all discount plans
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only delete discount plans from their stores
        return $user->stores()->where('stores.id', $discount_plan->store_id)->exists();
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete_discount_plans');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DiscountPlan $discount_plan): bool
    {
        if (! $user->checkPermissionTo('restore_discount_plans')) {
            return false;
        }

        // Super admins can restore all discount plans
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only restore discount plans from their stores
        return $user->stores()->where('stores.id', $discount_plan->store_id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DiscountPlan $discount_plan): bool
    {
        if (! $user->checkPermissionTo('force_delete_discount_plans')) {
            return false;
        }

        // Super admins can force delete all discount plans
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Other users can only force delete discount plans from their stores
        return $user->stores()->where('stores.id', $discount_plan->store_id)->exists();
    }
}
