<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Traits\Users;

class UsersController extends Controller
{
    use Users;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users=User::where('is_admin',0)->select('id','name','email','username','is_active')->get();

        if(!$users->isEmpty()){
           
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $users
            ];

        }else{

            $data = [
                'status' => 'no_data',
                'message' => 'No records!',
            ];
        }

        return response()->json($data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $request->validated();

        $name=$request->input('name');
        $email=$request->input('email');
        $username=$request->input('username');
        $password=$request->input('password');
        $role_id = $request->input('role_id');

        if($this->usernameExists($username)){

             $data = [
                'status' => 'error',
                'message' => 'Username is already in use by another user!'
            ];

            return response()->json($data);

        }elseif(str_contains($username, ' ')) {

                $data = [
                    'status' => 'error',
                    'message' => 'Username cannot contain spaces!',
                ];

                return response()->json($data);

        }elseif($this->emailAddressExists($email)) {

            $data = [
                'status' => 'error',
                'message' => 'The email has already been taken!',
            ];

            return response()->json($data);

        }

        $user=User::create([
            "name"=>$name,
            "email"=>$email,
            "username"=>$username,
            "password"=>bcrypt($password),
        ]);

        if($user){

            $role = Role::findOrFail($role_id);
            $user->assignRole($role->name);

            $data = [
                'status' => 'success',
                'message' => 'User created successfully!'
            ];

        }else{

            $data = [
                'status' => 'error',
                'message' => 'An error occurred. User was NOT created. Please try again!'
            ];

        }

        return response()->json($data);
    }


    /**
     * Fetch resource for editing.
     */
    public function edit(string $id)
    {
        $user=User::where('is_admin',0)->where('id',$id)->select('name','email','username','is_active')->first();

        if(!empty($user)){

            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data'=>$user
            ];

        }else{

            $data = [
                'status' => 'no_data',
                'message' => 'User record not found or access not allowed!'
            ];

        }

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        $request->validated();

        $name=$request->input('name');
        $email=$request->input('email');
        $username=$request->input('username');
        $role_id = $request->input('role_id');

        if($this->emailAddressExists($email) && !$this->emailBelongsToUser($id,$email)){

            $data = [
                'status' => 'error',
                'message' => 'Email is already in use by another user!'
            ];

            return response()->json($data);

        }elseif($this->usernameExists($username) && !$this->usernameBelongsToUser($id,$username)){

            $data = [
                'status' => 'error',
                'message' => 'Username is already in use by another user!'
            ];

            return response()->json($data);
        }

        $user=User::where('is_admin',0)->where('id',$id)->first();

        if(!empty($user)){
            $user->name=$name;
            $user->email=$email;
            $user->username=$username;

            if($user->update()){

                $user_role = $user->roles->first();
            
                if(!empty($user_role)){
                    if($user_role->id != $role_id) {
                        $user->removeRole($user_role->name);

                        $role = Role::findOrFail($role_id);
                        $user->assignRole($role->name);
                    }
                }else{
                    $role = Role::findOrFail($role_id);
                    $user->assignRole($role->name);
                }

                $data = [
                    'status' => 'success',
                    'message' => 'User updated successfully!'
                ];

            }else{

                $data = [
                    'status' => 'error',
                    'message' => 'An error occurred. User was NOT updated. Please try again!'
                ];

            }
        }else{
            $data = [
                'status' => 'no_data',
                'message' => 'User record not found or access not allowed!'
            ];
        }

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user=User::where('is_admin',0)->where('id',$id)->first();

        if(!empty($user)){

            if($user->delete()){

                $data = [
                    'status' => 'success',
                    'message' => 'User deleted successfully!'
                ];

            }else{

                $data = [
                    'status' => 'error',
                    'message' => 'An error occurred. User was NOT deleted. Please try again!'
                ];

            }
        }else{

            $data = [
                'status' => 'no_data',
                'message' => 'User record not found or access not allowed!'
            ];
        }

        return response()->json($data);
    }
}
