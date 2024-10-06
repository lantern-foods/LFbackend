<?php

namespace App\Http\Controllers\Driver\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class driververificationController extends Controller
{

    public function verifyDrivOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $driver = Driver::where('drive_otp', '=', $request->input('otp'))->first();

        if (!$driver) {
            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];
        } else {
            $driver->update(['drive_otp' => null]);

            $data = [
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'phone_number' => $driver->phone_number,
            ];
        }

        return response()->json($data);
    }

    public function setdriverPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6',
            'phone_number' => 'required',
        ]);

        $driver = Driver::where('phone_number', '=', $request->input('phone_number'))->first();

        if (!$driver) {

            $data = [
                'status' => 'no_data',
                'message' => 'No records',
            ];

        } else {

            $password = $request->input('password');
            $driver->update(['password' => bcrypt($password)]);

            $data = [
                'status' => 'success',
                'message' => 'Password set successfully',
            ];
        }
        return response()->json($data);
    }
}
