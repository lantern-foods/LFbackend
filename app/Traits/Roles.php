<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait Roles
{
    /**
     * Check if a role exists in the database.
     *
     * @param string $roleName
     * @return bool
     */
    public function roleExists(string $roleName): bool
    {
        return DB::table('roles')
            ->where('name', $roleName)
            ->exists();
    }
}
