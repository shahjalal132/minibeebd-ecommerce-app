<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // The modules/resources that require CRUD permissions
        $modules = [
            'user',
            'product',
            'type',
            'size',
            'color',
            'slider',
            'role',
            'purchase',
            'permission',
            'discount',
            'category',
            'order',
            'page',
            'image',
            'landing_page',
            'honey_landing_page',
            'incomplete_order',
            'delivery_charge',
            'setting',
            'coupon',
            'report',
            'social_icon',
            'supplier',
            'courier',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];
        $allPermissions = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = $module . '.' . $action;
                $allPermissions[] = $permissionName;
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }
        
        // Also add arbitrary/standalone permissions you might have observed
        $standalonePermissions = [
            // add any specific ones if needed, otherwise CRUD is fine
        ];

        foreach ($standalonePermissions as $permissionName) {
            $allPermissions[] = $permissionName;
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Assign all generated permissions to the admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($allPermissions);
    }
}
