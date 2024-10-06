<?php

namespace App\Http\Controllers\Client\v1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientverificationController extends Controller
{
    //
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        // Find the client based on the provided email address
        $client = Client::where('email_address', '=', $request->input('email_address'))->first();

        if (!$client) {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];

        }

        // Check if the provided OTP matches the stored OTP for the client
        if ($client->client_otp != $request->input('otp')) {
            $data = [
                'status' => 'error',
                'message' => 'Invalid OTP',
            ];

        } else {
            // Clear the OTP after successful verification
            $client->update(['client_otp' => null]);

            $data = [
                'status' => 'success',
                'message' => 'OTP verified successfully',
            ];
        }

        return response()->json($data);
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6',
            'email_address' => 'required',
        ]);

        $client = Client::where('email_address','=', $request->input('email_address'))->first();

        if (!$client) {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        } else {
            $password = $request->input('password');
            $client->update(['password' => bcrypt($password)]);

            $data = [
                'status' => 'success',
                'message' => 'Password set successfully',
            ];
        }

        return response()->json($data);

    }
}
