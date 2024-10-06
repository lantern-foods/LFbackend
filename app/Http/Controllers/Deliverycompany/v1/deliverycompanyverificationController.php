<?php

namespace App\Http\Controllers\Deliverycompany\v1;

use App\Http\Controllers\Controller;
use App\Models\Deliverycompany;
use Illuminate\Http\Request;

class deliverycompanyverificationController extends Controller
{
    public function verifyDelOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required | digits:6',
        ],
            [
                'otp.required' => 'OTP is required',
                'otp.digits' => 'OTP must be 6 digits',
            ]);

        $deliverycompany = Deliverycompany::where('delvry_otp', '=', $request->input('otp'))->first();

        if (!$deliverycompany) {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        } else {
            $deliverycompany->update(['delvry_otp' => null]);

            $data = [
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'email' => $deliverycompany->email,
            ];
        }
        return response()->json($data);
    }

    public function setdeliverycompanyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6',
            'email' => 'required',
        ]);

        $deliverycompany = Deliverycompany::where('email', '=', $request->input('email'))->first();

        if (!$deliverycompany) {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        } else {
            $password = $request->input('password');
            $deliverycompany->update(['password' => bcrypt($password)]);

            $data = [
                'status' => 'success',
                'message' => 'Password set successfully',
            ];
        }
        return response()->json($data);
    }
}
