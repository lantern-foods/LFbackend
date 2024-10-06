<?php

namespace App\Traits;

use DB;

trait Permissions
{
    /**
    * Returns an array of available permissions
    */
    public function permissions()
    {
        $cook_permissions = [
           'add-meal','edit-meal'
        ];

        $client_permissions=[
            'place-orders'
        ];

        $administration_permissions = [
            'apprv-cook-profile','apprv-meal','mng-users','mng-roles'
        ];

        $permissions=array_merge(
            $cook_permissions,
            $client_permissions,
            $administration_permissions,
        );

        return $permissions;
    }

    /**
    * Checks if permission exists before seeding
    */
    public function permissionExists($permission)
    {
        $permission_exists = DB::table('permissions')->where('name', '=', $permission)->first();

        if (!empty($permission_exists)) {
            return true;
        } else {
            return false;
        }
    }
}