<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authenticate client
     */
    public function authenticate(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if (empty($email)) {
            $data = [
                'status' => 'error',
                'message' => 'Email is required!',
            ];

            return response()->json($data);
        } elseif (empty($password)) {
            $data = [
                'status' => 'error',
                'message' => 'Password is required!',
            ];

            return response()->json($data);
        }

        if (Auth::guard('clients')->attempt(['email_address' => $email, 'password' => $password])) {
            $client = Auth::guard('clients')->user()->load('cook', 'customeraddress');

            $token = $client->createToken('Client Token')->accessToken;

            $response = [
                'status' => 'success',
                'status_code' => 200,
                'token' => $token,
                'user' => $client,
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
        $request->user()->token()->revoke();

        $response = [
            'status' => 'success',
            'message' => 'Logged out successfully',
        ];

        return response()->json($response);
    }
}
