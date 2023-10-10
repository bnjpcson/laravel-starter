<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function getpermission(Request $request)
    {
        $data['paginate'] = Permission::where('name', 'LIKE', "%$request->search%")
            // ->orWhere('email', 'LIKE', "%$request->search%")
            // ->orWhere('last_login', 'LIKE', "%$request->search%")
            ->paginate($request->per_page);

        return response()->json($data, 200);
    }

    public function getallpermission(Request $request)
    {
        $data['paginate'] = Permission::all()->pluck('name');

        return response()->json($data, 200);
    }

    public function createpermission(Request $request)
    {
        try {

            $rules = [
                'permission.required' => 'Please enter permission',
                'permission.unique' => 'This permission already exists.',
            ];

            $valid_fields = [
                'permission' => 'required|string|max:255|unique:permissions,name',
            ];

            $validator = Validator::make($request->all(), $valid_fields, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to update the record.",
                    'error' => $validator->errors()
                ], 200);
            }

            $attr = array(
                'name'  => $request->permission,
                'guard_name' => 'web'
            );

            Permission::create($attr);

            $role = Role::find(1);

            $permissions = Permission::pluck('id', 'id')->all();

            $role->syncPermissions($permissions);

            return response()->json([
                'success' => true,
                'message' => 'Record has been saved',
                'error' => [],
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permission. Please try again.',
                'error' => [],
            ], 500);
        }
    }

    public function selectpermission(Request $request)
    {
        try {


            $permission = Permission::find($request->id);


            if (empty($permission->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission Not Found. Please try again.',
                ], 200);
            }

            return response()->json([
                'success' => true,
                'permission' => $permission
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get permission. Please try again.',
            ], 500);
        }
    }

    public function updatepermission(Request $request)
    {
        try {

            $rules = [
                'permission.required' => 'Please enter permission',
                'permission.unique' => 'This permission already exists.',

            ];

            $valid_fields = [
                'permission' => 'required|string|max:255|unique:permissions,name',
            ];

            $validator = Validator::make($request->all(), $valid_fields, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to update the record.",
                    'error' => $validator->errors()
                ], 200);
            }

            if ($request->id != 0) {
                Permission::where('id', $request->id)->update(['name' => $request->permission]);
                return response()->json([
                    'success' => true,
                    'message' => 'Record has been saved',
                    'error' => [],
                ], 200);
            } else {
                throw new \Exception("ID not found");
            }
        } catch (\Exception $err) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permission. Please try again.',
                'error' => [],
                'err' => $err
            ], 500);
        }
    }

    public function deletepermission(Request $request)
    {
        try {
            if (!empty($request->id)) {
                Permission::where('id', $request->id)->delete();
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
                'message' => 'Failed to delete permission. Please try again.',
                'error' => [],
            ], 500);
        }
    }
}
