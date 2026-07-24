<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Seller']);
        Role::create(['name' => 'Customer']);

        // Here you can also create permissions and assign them to roles if needed in the future.
        // e.g. Permission::create(['name' => 'edit articles']);
    }
}
