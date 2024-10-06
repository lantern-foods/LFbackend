<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Traits\Permissions;
use App\Traits\Roles;
use App\Models\User;

class RolesAndPermissionsTableSeeder extends Seeder
{
    use Permissions,Roles;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //create permissions
        $permissions=$this->permissions();

        foreach($permissions as $permission)
        {
            if(!$this->permissionExists($permission)){
                Permission::create(['name' => $permission,'guard_name' => 'api']);
            }
        }

        
        if(!$this->roleExists('Administrator')) //Seed admin role and attach permissions
        {
            $role = Role::create(['name' => 'Administrator','guard_name' => 'api','is_default'=>1]);
            $role->syncPermissions(Permission::all());

            //Assign Role to default admin user
            $user=User::first();
            $user->assignRole($role->name);
            
        }else{ //Get the default Admin role and update permissions
            
            $role=Role::where('name','Administrator')->first();

            if(!empty($role)) {
                $role->syncPermissions(Permission::all());
            }
        }
    }
}
