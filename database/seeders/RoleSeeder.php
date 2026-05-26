<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use App\Models\User;

use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        // Create roles
        $roles = ['admin', 'chef_instructor', 'student', 'staff', 'unapproved'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Assign "access hive" permission to "unapproved" role
        Permission::firstOrCreate(['name' => 'access hive']);
        Role::findByName('unapproved')->givePermissionTo('access hive');

        // Create one admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@hbci.ac.ls',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'approved_at' => now(),
        ]);
        $admin->assignRole('admin');
    }
}
