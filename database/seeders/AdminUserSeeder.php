<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create or find the admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('12345678'), // Default password
                'mobile' => '0123456789',
                'business_name' => 'MiniBee',
                'status' => 1,
                'is_seller' => 0,
            ]
        );

        // Ensure the 'admin' role exists and assign it
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($role);
        }
    }
}
