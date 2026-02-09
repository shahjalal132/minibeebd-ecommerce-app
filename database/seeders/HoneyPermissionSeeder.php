<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HoneyPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'honey_landing_page.view',
            'honey_landing_page.create',
            'honey_landing_page.edit',
            'honey_landing_page.delete',
            'incomplete_order.view',
            'incomplete_order.create',
            'incomplete_order.edit',
            'incomplete_order.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $role = Role::where('name', 'admin')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }
}
