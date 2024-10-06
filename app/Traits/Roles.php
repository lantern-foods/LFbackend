<?php

namespace App\Traits;
use DB;

trait Roles{
    
    /*
    * Checks if role exists
    */
    public function roleExists($role_name)
    {
        $role_exists = DB::table('roles')->where('name', '=', $role_name)->first();

        if (!empty($role_exists)) {
            return true;
        } else {
            return false;
        }
    }
}