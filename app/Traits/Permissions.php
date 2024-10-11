<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait Permissions
{
    /**
     * Returns an array of all available permissions.
     *
     * @return array
     */
    public function permissions(): array
    {
        $cookPermissions = ['add-meal', 'edit-meal'];
        $clientPermissions = ['place-orders'];
        $adminPermissions = ['apprv-cook-profile', 'apprv-meal', 'mng-users', 'mng-roles'];

        return array_merge($cookPermissions, $clientPermissions, $adminPermissions);
    }

    /**
     * Check if a permission exists in the database.
     *
     * @param string $permission
     * @return bool
     */
    public function permissionExists(string $permission): bool
    {
        return DB::table('permissions')
            ->where('name', $permission)
            ->exists();
    }
}
