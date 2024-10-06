<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate driver/rider
     */
    public function authenticate(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        // Assuming these are passed in the request
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email is required!',
            ]);
        } elseif (empty($password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password is required!',
            ]);
        }

        if (Auth::guard('drivers')->attempt(['email' => $email, 'password' => $password])) {
            $user = auth('drivers')->user();

            // Update login status and location
            $loginLocation = $latitude . ',' . $longitude; // Format as needed
            $user->update([
                'login_status' => 1, // Assuming you want to set this status
                'login_location' => $loginLocation,
            ]);

            $token = $user->createToken('driver Token')->accessToken;

            return response()->json([
                'status' => 'success',
                'status_code' => 200,
                'token' => $token,
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'status' => 'Unauthorized',
                'status_code' => 401,
            ]);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $response = [
            'status' => 'success',
            'message' => 'Logged out successfully',
        ];

        return response()->json($response);
    }
}
