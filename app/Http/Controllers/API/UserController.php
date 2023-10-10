<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function rolespermissions()
    {
        $user_roles = Auth::user()->roles->pluck('name')->all();

        $user_permissions = Auth::user()->getAllPermissions()->pluck('name');

        return response()->json([
            'user_roles' => $user_roles,
            'user_permissions' => $user_permissions,
        ], 200);
    }

    public function getusers(Request $request)
    {
        
        $data['paginate'] = User::with(['roles'])
            ->where('name', 'LIKE', "%$request->search%")
            ->orWhere('email', 'LIKE', "%$request->search%")
            ->orWhere('last_login', 'LIKE', "%$request->search%")
            ->paginate($request->per_page);

        $data['roles'] = Role::with('permissions')->orderBy('id', 'Asc')->get();
        return response()->json($data, 200);
    }

    public function createuser(UserCreateRequest $request)
    {
        try {
            $attr = array(
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'active' => $request->active
            );

            $user = User::create($attr);

            $user->assignRole($request->roles);

            return response()->json([
                'success' => true,
                'message' => 'Record has been saved',
                'error' => [],
                'attr' => $user
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save role. Please try again.',
                'error' => [],
                'err' => $err
            ], 500);
        }
    }

    public function selectuser(Request $request)
    {

        try {
            $user = User::with('roles', 'permissions')->find($request->id);

            if (empty($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User Not Found. Please try again.',
                ], 200);
            }

            return response()->json([
                'success' => true,
                'user' => $user
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data. Please try again.',
            ], 500);
        }
    }


    public function selectuserrolespermission(Request $request)
    {
        try {
            $user = User::with('roles')->find($request->id);

            if (empty($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User Not Found. Please try again.',
                ], 200);
            }

            $data['roles'] = [];

            foreach ($user->roles as $key => $role) {
                $data['roles'][$key] = Role::with('permissions')->where('id', $role->id)->get();
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data. Please try again.',
            ], 500);
        }
    }


    public function updateuser(UserUpdateRequest $request)
    {
        if ($request->password == null || $request->password == "") {
            $attr = array(
                'name' => $request->name,
                'active' => $request->active
            );
        } else {
            $attr = array(
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'active' => $request->active
            );
        }


        $user = User::where('id', $request->id)->first();
        $user->syncRoles($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'Record has been saved',
            'error' => [],
        ], 200);
        try {

            
        } catch (\Exception $err) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save role. Please try again.',
                'error' => [],
            ], 500);
        }
    }

    public function updateprofile(Request $request)
    {
        $rules = [
            'name.required' => 'Please enter name',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be atleast 8 characters',
            'password.same' => 'Password and Confirm Password did not match',
            'confirmPassword.required' => 'Confirm Password is required',
        ];

        $valid_fields = [
            'name' => 'required|string|max:255',
        ];

        if ($request->get('password') || $request->get('confirmPassword')) {
            $valid_fields['password'] = 'required|string|min:8|same:confirmPassword';
            $valid_fields['confirmPassword'] = 'required';
        }

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Failed to update the record.",
                'error' => $validator->errors()
            ], 200);
        }

        try {
            $user = User::find($request->id);
            $toUpdate = [
                "name" => $request->name,
            ];

            if (!empty($request->get('password'))) {
                $toUpdate['password'] = Hash::make($request->get('password'));
            }

            $user->update($toUpdate);

            return response()->json([
                'success' => true,
                'message' => 'Record has been updated',
                'error' => [],
                'display' => $user
            ], 200);
        } catch (\Exception $er) {
            abort(500, 'Something Went Wrong');
        }
    }

    public function deleteuser(Request $request)
    {
        try {
            if (!empty($request->id)) {
                User::where('id', $request->id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Record has been deleted',
                    'error' => [],
                ], 200);
            } else {
                throw new \Exception("ID not found");
            }
        } catch (\Exception $err) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user. Please try again.',
                'error' => [],
            ], 500);
        }
    }
}
