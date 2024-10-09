<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use App\Models\Deliverycompany;
use Illuminate\Http\Request;

class DeliveryCompanyVerificationController extends Controller
{
    /**
     * Verify OTP for delivery company.
     */
    public function verifyDelOtp(Request $request)
    {
        // Validate OTP input
        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'OTP is required',
            'otp.digits' => 'OTP must be exactly 6 digits',
        ]);

        // Find the delivery company using the OTP
        $deliverycompany = Deliverycompany::where('delvry_otp', $request->input('otp'))->first();

        // If no matching delivery company is found
        if (!$deliverycompany) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No records found for the provided OTP',
            ], 404);
        }

        // Clear OTP after successful verification
        $deliverycompany->update(['delvry_otp' => null]);

        // Return success response with the delivery company's email
        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully',
            'email' => $deliverycompany->email,
        ]);
    }

    /**
     * Set password for delivery company after OTP verification.
     */
    public function setDeliveryCompanyPassword(Request $request)
    {
        // Validate email and password input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Find the delivery company by email
        $deliverycompany = Deliverycompany::where('email', $request->input('email'))->first();

        // If delivery company not found
        if (!$deliverycompany) {
            return response()->json([
                'status' => 'no_data',
                'message' => 'No records found for the provided email',
            ], 404);
        }

        // Update the password with encryption
        $deliverycompany->update(['password' => bcrypt($request->input('password'))]);

        // Return success response after setting the password
        return response()->json([
            'status' => 'success',
            'message' => 'Password set successfully',
        ]);
    }
}
