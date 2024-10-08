<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientVerificationController extends Controller
{
    /**
     * Verify OTP for client email address.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email_address' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        // Find the client by the provided email address
        $client = Client::where('email_address', $request->input('email_address'))->first();

        if (!$client) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No client record found.',
            ], 404);
        }

        // Verify if the OTP matches
        if ($client->client_otp != $request->input('otp')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP.',
            ], 400);
        }

        // Clear the OTP after successful verification
        $client->update(['client_otp' => null]);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully.',
        ], 200);
    }

    /**
     * Set a new password for the client.
     */
    public function setPassword(Request $request)
    {
        $request->validate([
            'email_address' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Find the client by the provided email address
        $client = Client::where('email_address', $request->input('email_address'))->first();

        if (!$client) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No client record found.',
            ], 404);
        }

        // Update the password
        $client->update(['password' => bcrypt($request->input('password'))]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password set successfully.',
        ], 200);
    }
}
