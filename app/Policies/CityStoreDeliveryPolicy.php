<?php

namespace App\Policies;

use App\Models\CityStoreDelivery;
use App\Models\User;

class CityStoreDeliveryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view_any_city_store_deliveries');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CityStoreDelivery $city_store_delivery): bool
    {
        return $user->checkPermissionTo('view_city_store_deliveries');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create_city_store_deliveries');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CityStoreDelivery $city_store_delivery): bool
    {
        return $user->checkPermissionTo('update_city_store_deliveries');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CityStoreDelivery $city_store_delivery): bool
    {
        return $user->checkPermissionTo('delete_city_store_deliveries');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CityStoreDelivery $city_store_delivery): bool
    {
        return $user->checkPermissionTo('restore_city_store_deliveries');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CityStoreDelivery $city_store_delivery): bool
    {
        return $user->checkPermissionTo('force_delete_city_store_deliveries');
    }
}
