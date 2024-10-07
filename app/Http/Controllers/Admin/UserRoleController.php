<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * List all assignable roles
     */
    public function index()
    {
        // Retrieve all roles
        $roles = Role::select('id', 'name')->get();

        // Check if roles are available
        return !$roles->isEmpty() ? response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $roles,
        ]) : response()->json([
            'status' => 'no_data',
            'message' => 'No roles available!',
        ], 404);
    }
}
