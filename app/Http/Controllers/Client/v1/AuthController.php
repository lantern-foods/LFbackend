<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Authenticate client
     * CHANGE: CHECK IF CLIENT IS AN INSTANCE OF ELOQUENT MODEL AND LOAD RELATIONSHIPS
     * ADD LOGS FOR DEBUGGING
     */
    public function authenticate(Request $request)
    {
    $email = $request->input('email');
    $password = $request->input('password');

    // Validate required fields
    if (empty($email)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Email is required!',
        ], 400);
    }

    if (empty($password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Password is required!',
        ], 400);
    }

    // Attempt authentication
    if (Auth::guard('clients')->attempt(['email_address' => $email, 'password' => $password])) {
        $client = Auth::guard('clients')->user();

        // Debugging statement
        if ($client instanceof \Illuminate\Database\Eloquent\Model) {
            Log::info('Client is an instance of Eloquent model.');
        } else {
            Log::error('Client is not an instance of Eloquent model.');
        }

        // Check if $client is an instance of Eloquent model
        if ($client instanceof \Illuminate\Database\Eloquent\Model) {
            $client->load('cook', 'customerAddresses');

            // Generate access token
            $token = $client->createToken('Client Token')->accessToken;

            return response()->json([
                'status' => 'success',
                'status_code' => 200,
                'token' => $token,
                'user' => $client,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Server error. User is not an Eloquent model.',
            ], 500);
        }
    }

    return response()->json([
        'status' => 'error',
        'status_code' => 401,
        'message' => 'Unauthorized. Invalid credentials.',
    ], 401);
    }

    /**
     * Logout client
     */
    public function logout(Request $request)
    {
        // Revoke the token
        $request->user()->token()->revoke();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
}
