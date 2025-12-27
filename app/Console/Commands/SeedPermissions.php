<?php

namespace App\Console\Commands;

use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;

class SeedPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:seed
                            {--fresh : Clear existing permissions before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed all Filament permissions (resources, widgets, and dashboard)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🌱 Seeding all Filament permissions...');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('⚠️  Clearing existing permissions...');
            \Spatie\Permission\Models\Permission::query()->delete();
            \Spatie\Permission\Models\Role::query()->delete();
            $this->info('✅ Permissions cleared.');
            $this->newLine();
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('📋 Seeding permissions from PermissionSeeder...');
        $this->newLine();

        $seeder = new PermissionSeeder;
        $seeder->setCommand($this);
        $seeder->run();

        $this->newLine();
        $this->info('✅ All permissions seeded successfully!');
        $this->newLine();

        // Show summary
        $permission_count = \Spatie\Permission\Models\Permission::count();
        $role_count = \Spatie\Permission\Models\Role::count();

        $this->table(
            ['Type', 'Count'],
            [
                ['Permissions', $permission_count],
                ['Roles', $role_count],
            ]
        );

        $this->newLine();
        $this->info('💡 Tip: Assign the "super_admin" role to users to grant all permissions.');
        $this->info('   Example: $user->assignRole("super_admin");');

        return Command::SUCCESS;
    }
}
