<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class AuthController extends Controller
{
    /**
     * Authenticate admin
     */
    public function authenticate(Request $request)
    {
        $username=$request->input('username');
        $password=$request->input('password');

        if(empty($username)){

            $data = [
                'status' => 'error',
                'message' => 'Username is required!',
            ];

            return response()->json($data);

        }elseif(empty($password)){

            $data = [
                'status' => 'error',
                'message' => 'Password is required!',
            ];

            return response()->json($data);
        }

        if (Auth::guard('web')->attempt(['username' => $username, 'password' => $password,'is_active' => 1])) {

            $token = auth('web')->user()->createToken('Auth Token')->accessToken;

            $response=[
                'status'=>'success',
                'status_code'=>200,
                'token'=>$token,
                'user'=>DB::table('users')->where('username', '=', $username)->first(),
            ];
        }else{
            $response=[
                'status'=>'Unauthorized',
                'status_code'=>401
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

        $response=[
            'status'=>'success',
            'message'=>'Logged out successfully'
        ];

        return response()->json($response);
    }

}
