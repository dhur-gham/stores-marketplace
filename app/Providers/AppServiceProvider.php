<?php

namespace App\Providers;

use App\Models\ApiRequest;
use App\Models\CityStoreDelivery;
use App\Models\Customer;
use App\Models\DiscountPlan;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Policies\ApiRequestPolicy;
use App\Policies\CityStoreDeliveryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DiscountPlanPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\StorePolicy;
use App\Policies\UserPolicy;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
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
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ApiRequest::class, ApiRequestPolicy::class);
        Gate::policy(DiscountPlan::class, DiscountPlanPolicy::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'ar'])
                ->defaultLocale('ar');
        });
    }
}
