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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (Auth::guard('drivers')->attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::guard('drivers')->user();

            // Update driver's login status and location
            $user->update([
                'login_status' => 1, // Assuming '1' indicates the driver is online
                'login_location' => "{$latitude},{$longitude}", // Format location as "latitude,longitude"
            ]);

            $token = $user->createToken('Driver Token')->accessToken;

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
                'message' => 'Invalid email or password',
            ], 401);
        }
    }

    /**
     * Logout the driver
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}
