<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Traits\Permissions;
use App\Traits\Roles;
use App\Models\User;

class RolesAndPermissionsTableSeeder extends Seeder
{
    use Permissions, Roles;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions from the list in Permissions trait
        $permissions = $this->permissions();

        foreach ($permissions as $permission) {
            if (!$this->permissionExists($permission)) {
                Permission::create(['name' => $permission, 'guard_name' => 'api']);
            }
        }

        // Create or update the Administrator role and assign all permissions
        if (!$this->roleExists('Administrator')) {
            // Create a new role
            $role = Role::create(['name' => 'Administrator', 'guard_name' => 'api', 'is_default' => 1]);

            // Assign all permissions to this role
            $role->syncPermissions(Permission::all());

            // Assign role to the default admin user, if available
            $user = User::first();
            if ($user) {
                $user->assignRole($role->name);
            }
        } else {
            // Fetch the existing Administrator role
            $role = Role::where('name', 'Administrator')->first();
            if ($role) {
                // Sync the role with all permissions
                $role->syncPermissions(Permission::all());
            }
        }
    }
}
