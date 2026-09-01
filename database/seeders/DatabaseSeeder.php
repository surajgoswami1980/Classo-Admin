<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);
        $this->call(ResourcePermissionSeeder::class);

        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'superadmin@schoolerp.com')],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'password')),
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super-admin');
    }
}
