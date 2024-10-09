<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverVerificationController extends Controller
{
    /**
     * Verify the driver's OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyDrivOtp(Request $request)
    {
        // Validate OTP input
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        // Find the driver based on OTP
        $driver = Driver::where('drive_otp', $request->input('otp'))->first();

        if (!$driver) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No records found',
            ], 404);
        }

        // Clear the OTP after successful verification
        $driver->update(['drive_otp' => null]);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully',
            'phone_number' => $driver->phone_number,
        ]);
    }

    /**
     * Set a new password for the driver.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDriverPassword(Request $request)
    {
        // Validate password and phone number
        $request->validate([
            'password' => 'required|min:6',
            'phone_number' => 'required|exists:drivers,phone_number',
        ]);

        // Find the driver by phone number
        $driver = Driver::where('phone_number', $request->input('phone_number'))->first();

        if (!$driver) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No records found',
            ], 404);
        }

        // Update the driver's password
        $driver->update(['password' => bcrypt($request->input('password'))]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password set successfully',
        ]);
    }
}
