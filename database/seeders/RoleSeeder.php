<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

use App\Models\User;

use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        // Create roles
        $roles = ['admin', 'instructor', 'student', 'hr_staff', 'unapproved'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Assign "access intranet" permission to "unapproved" role
        Role::findByName('unapproved')->givePermissionTo('access intranet');

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