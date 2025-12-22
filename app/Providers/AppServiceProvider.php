<?php

namespace App\Providers;

use App\Models\CityStoreDelivery;
use App\Models\Product;
use App\Models\Store;
use App\Policies\CityStoreDeliveryPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\StorePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Store::class, StorePolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(CityStoreDelivery::class, CityStoreDeliveryPolicy::class);
    }
}
