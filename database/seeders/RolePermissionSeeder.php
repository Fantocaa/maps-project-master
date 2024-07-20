<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'create pin']);
        Permission::create(['name' => 'edit pin']);
        Permission::create(['name' => 'delete pin']);
        Permission::create(['name' => 'see pin']);

        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'superuser']);
        Role::create(['name' => 'user']);

        $roleSuperAdmin = Role::findByName('superadmin');
        $roleSuperAdmin->givePermissionTo(Permission::all());

        $roleAdmin = Role::findByName('admin');
        $roleAdmin->givePermissionTo(Permission::all());

        $roleSuperUser = Role::findByName('superuser');
        $roleSuperUser->givePermissionTo('create pin');
        $roleSuperUser->givePermissionTo('edit pin');
        $roleSuperUser->givePermissionTo('see pin');

        $roleUser = Role::findByName('user');
        $roleUser->givePermissionTo('see pin');
    }
}
