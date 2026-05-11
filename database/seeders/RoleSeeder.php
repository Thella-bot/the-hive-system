<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = ['admin', 'instructor', 'student', 'hr_staff', 'unapproved'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Optional: specific permissions
        Permission::create(['name' => 'access intranet']);
        Role::findByName('unapproved')->givePermissionTo('access intranet'); // will be removed later? Not needed.

        // Create one admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hbculinaryinstitute.co.ls',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'approved_at' => now(),
        ]);
        $admin->assignRole('admin');
    }
}