<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates all permissions used in Filament:
     * - Resource permissions (view_any, view, create, update, delete, etc.)
     * - Widget permissions (view_dashboard_stats, view_orders_chart, etc.)
     * - Creates a super_admin role with all permissions
     *
     * Run via: php artisan permissions:seed
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all Filament resources that need permissions
        // These match the Resource classes in app/Filament/Resources/
        $resources = [
            'products',              // ProductResource
            'stores',                 // StoreResource
            'city_store_deliveries',  // CityStoreDeliveryResource
            'permissions',            // PermissionResource
            'roles',                  // RoleResource
            'orders',                 // OrderResource
            'users',                  // UserResource
            'customers',              // CustomerResource
            'api_requests',           // ApiRequestResource
            'discount_plans',         // DiscountPlanResource
        ];

        // Standard Filament permissions for each resource
        $permission_types = [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
        ];

        $permissions = [];

        foreach ($resources as $resource) {
            foreach ($permission_types as $type) {
                $permissions[] = "{$type}_{$resource}";
            }
        }

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Widget permissions (found in Filament widgets and pages)
        $widget_permissions = [
            'view_dashboard_stats',           // StatsOverview, Dashboard
            'view_orders_chart',              // OrdersChart
            'view_orders_by_status_chart',    // OrdersByStatusChart
            'view_latest_orders',             // LatestOrders
            'view_api_metrics',               // API Metrics widgets (if used)
        ];

        foreach ($widget_permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // System permissions (API documentation, etc.)
        $system_permissions = [
            'view_api_docs',                  // Scramble API documentation access
        ];

        foreach ($system_permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create a super-admin role and assign all permissions
        $super_admin_role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $super_admin_role->givePermissionTo(Permission::all());

        $this->command->info('Permissions seeded successfully!');
    }
}
