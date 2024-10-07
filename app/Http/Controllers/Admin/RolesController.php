<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Traits\Roles;
use DB;

class RolesController extends Controller
{
    use Roles;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::select('id', 'name', 'created_at', 'updated_at')
                     ->where('is_default', 0)
                     ->get();

        return !$roles->isEmpty() ? response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $roles,
        ]) : response()->json([
            'status' => 'no_data',
            'message' => 'No records found!',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
            'permissions' => 'array',
        ], [
            'role_name.required' => 'Role name is required!',
        ]);

        try {
            $role_name = $request->input('role_name');
            $permissions = $request->input('permissions', []);

            if ($this->roleExists($role_name)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role already exists!',
                ], 400);
            }

            $role = Role::create([
                'name' => $role_name,
                'guard_name' => 'api',
            ]);

            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Role created successfully!',
                'data' => $role,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to create role. Please try again!',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $role = Role::select('id', 'name')->findOrFail($id);
            $permissions = DB::table('role_has_permissions')
                             ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                             ->where('role_id', $id)
                             ->pluck('permissions.name');

            return response()->json([
                'status' => 'success',
                'role' => $role,
                'permissions' => $permissions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found!',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
            'permissions' => 'array',
        ], [
            'role_name.required' => 'Role name is required!',
        ]);

        try {
            $role = Role::findOrFail($id);

            $role->update([
                'name' => $request->input('role_name'),
            ]);

            $permissions = $request->input('permissions', []);
            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Role updated successfully!',
                'data' => $role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to update role. Please try again!',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Role deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to delete role. Please try again!',
            ], 500);
        }
    }
}
