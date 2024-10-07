<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Traits\Users;

class UsersController extends Controller
{
    use Users;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('is_admin', 0)
                     ->select('id', 'name', 'email', 'username', 'is_active')
                     ->get();

        return !$users->isEmpty() ? response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $users,
        ]) : response()->json([
            'status' => 'no_data',
            'message' => 'No records found!',
        ], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $request->validated();

        $username = $request->input('username');
        $email = $request->input('email');
        $role_id = $request->input('role_id');

        if ($this->usernameExists($username)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username is already in use by another user!',
            ], 400);
        }

        if (str_contains($username, ' ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username cannot contain spaces!',
            ], 400);
        }

        if ($this->emailAddressExists($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The email has already been taken!',
            ], 400);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $email,
            'username' => $username,
            'password' => bcrypt($request->input('password')),
        ]);

        if ($user) {
            $role = Role::findOrFail($role_id);
            $user->assignRole($role->name);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully!',
            ], 201);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'An error occurred. User was NOT created. Please try again!',
        ], 500);
    }

    /**
     * Fetch resource for editing.
     */
    public function edit(string $id)
    {
        $user = User::where('is_admin', 0)
                    ->where('id', $id)
                    ->select('name', 'email', 'username', 'is_active')
                    ->first();

        return $user ? response()->json([
            'status' => 'success',
            'message' => 'Request successful!',
            'data' => $user,
        ]) : response()->json([
            'status' => 'no_data',
            'message' => 'User record not found!',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $request->validated();

        $email = $request->input('email');
        $username = $request->input('username');
        $role_id = $request->input('role_id');

        if ($this->emailAddressExists($email) && !$this->emailBelongsToUser($id, $email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email is already in use by another user!',
            ], 400);
        }

        if ($this->usernameExists($username) && !$this->usernameBelongsToUser($id, $username)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username is already in use by another user!',
            ], 400);
        }

        $user = User::where('is_admin', 0)->where('id', $id)->firstOrFail();

        $user->update([
            'name' => $request->input('name'),
            'email' => $email,
            'username' => $username,
        ]);

        $currentRole = $user->roles->first();
        if ($currentRole && $currentRole->id != $role_id) {
            $user->removeRole($currentRole->name);
            $newRole = Role::findOrFail($role_id);
            $user->assignRole($newRole->name);
        } elseif (!$currentRole) {
            $newRole = Role::findOrFail($role_id);
            $user->assignRole($newRole->name);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::where('is_admin', 0)->where('id', $id)->first();

        if ($user) {
            $user->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully!',
            ]);
        }

        return response()->json([
            'status' => 'no_data',
            'message' => 'User record not found!',
        ], 404);
    }
}
