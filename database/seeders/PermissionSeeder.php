<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define the resources and their corresponding permission names
        $resources = [
            'products',
            'stores',
            'city_store_deliveries',
            'permissions',
            'roles',
            'orders',
            'users',
            'customers',
            'api_requests',
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

        // Widget permissions
        $widget_permissions = [
            'view_dashboard_stats',
            'view_orders_chart',
            'view_orders_by_status_chart',
            'view_latest_orders',
            'view_api_metrics',
        ];

        foreach ($widget_permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create a super-admin role and assign all permissions
        $super_admin_role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $super_admin_role->givePermissionTo(Permission::all());

        $this->command->info('Permissions seeded successfully!');
    }
}
