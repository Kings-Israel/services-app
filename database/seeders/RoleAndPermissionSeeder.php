<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = ['admin', 'user', 'vendor'];

        $permissions = ['manage all', 'create service', 'update service', 'view service', 'delete service'];

        collect($roles)->each(fn ($role) => Role::create(['name' => $role]));
        collect($permissions)->each(fn ($permission) => Permission::create(['name' => $permission]));

        Role::findByName('admin')->givePermissionTo('manage all');
        Role::findByName('vendor')->givePermissionTo('create service');
        Role::findByName('vendor')->givePermissionTo('update service');
        Role::findByName('vendor')->givePermissionTo('view service');
        Role::findByName('vendor')->givePermissionTo('delete service');
        Role::findByName('user')->givePermissionTo('view service');
    }
}
