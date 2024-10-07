<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate admin
     */
    public function authenticate(Request $request)
    {
        // Validate input fields
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        // Attempt to authenticate the user
        if (Auth::guard('web')->attempt(array_merge($credentials, ['is_active' => 1]))) {
            // Generate a token for the authenticated user
            $token = auth()->user()->createToken('Auth Token')->accessToken;

            $response = [
                'status' => 'success',
                'status_code' => 200,
                'token' => $token,
                'user' => auth()->user()->only(['id', 'username', 'email', 'name']), // Limit exposed user data
            ];
        } else {
            $response = [
                'status' => 'Unauthorized',
                'status_code' => 401,
            ];
        }

        return response()->json($response);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // Revoke the token for the authenticated user
        $request->user()->token()->revoke();

        $response = [
            'status' => 'success',
            'message' => 'Logged out successfully',
        ];

        return response()->json($response);
    }
}
