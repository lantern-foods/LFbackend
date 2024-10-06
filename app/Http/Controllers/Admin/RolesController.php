<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Traits\Roles;
use DB;

class RolesController extends Controller
{
    use Roles;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles=Role::select('id','name','created_at','updated_at')
                        ->where('is_default',0)
                        ->get();

        if(!$roles->isEmpty()){
           
            $data = [
                'status' => 'success',
                'message' => 'Request successful!',
                'data' => $roles
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
    public function store(Request $request)
    {
        $this->validate($request,[
            'role_name'=>'required'
        ],
        [
            'role_name.required'=>'Role Name is required!'
        ]);

        $payload = $request->json()->all();

        $role_name = $payload['role_name'];
        $permissions = $payload['permissions'];


        if($this->roleExists($role_name)){

            $data = [
                'status' => 'error',
                'message' => 'Role already exists!',
            ];

            return response()->json($data);
        }
        
        $role = Role::create([
            'name' => $role_name,
            'guard_name' => 'api',
        ]);
        
        //Assign permissions to the role
        if(!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        if($role){

            $data = [
                'status' => 'success',
                'message' => 'Role created successfully!',
                'data' => $role
            ];

        }else{

            $data = [
                'status' => 'error',
                'message' => 'Unable to create role. Please try again!',
            ];

        }

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role=Role::select('id','name')->findOrFail($id);

        $permissions =DB::table('role_has_permissions')->join('permissions','role_has_permissions.permission_id','=','permissions.id')->where('role_id',$id)->get()->toArray();
            
        $role_permissions = [];
        foreach ($permissions as $role_perm) {
            $role_permissions[] = $role_perm->name;
        }

        $data=[
            'status' => 'success',
            'role'=>$role,
            'permissions'=>$role_permissions
        ];

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request,[
            'role_name'=>'required'
        ],
        [
            'role_name.required'=>'Role Name is required!'
        ]);

        $payload = $request->json()->all();

        $role_name = $payload['role_name'];
        $permissions = $payload['permissions'];
        
        $role = Role::findOrFail($id);
        $role->name=$role_name;
        $role->update();
        
        //Assign permissions to the role
        if(!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        if($role){

            $data = [
                'status' => 'success',
                'message' => 'Role updated successfully!',
                'data' => $role
            ];

        }else{

            $data = [
                'status' => 'error',
                'message' => 'Unable to update role. Please try again!',
            ];

        }

        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        $data = [
            'status' => 'success',
            'message' => 'Role deleted successfully!',
        ];

        return response()->json($data);
    }
}
