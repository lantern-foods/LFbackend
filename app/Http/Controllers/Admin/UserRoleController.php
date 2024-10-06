<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * List assignable roles
     */
    public function index()
    {
        $roles=Role::select('id','name')->get();

        if(!$roles->isEmpty()){

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $roles
            ];

        }else{

            $data = [
                'status' => 'no_data',
                'message' => 'No roles available!',
            ];

        }

        return response()->json($data);
    }
}
