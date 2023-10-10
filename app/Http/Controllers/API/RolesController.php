<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'auth']);
    }

    public function getroles(Request $request)
    {
        $data['paginate'] = Role::with('permissions')->where('name', 'LIKE', "%$request->search%")
            ->paginate($request->per_page);

        return response()->json($data, 200);
    }

    public function getallroles(Request $request)
    {
        $data['paginate'] = Role::all()->pluck('name');

        return response()->json($data, 200);
    }

    public function createroles(Request $request)
    {
        try {

            $rules = [
                'role.required' => 'Please enter role',
                'role.unique' => 'This role already exists.',
            ];

            $valid_fields = [
                'role' => 'required|string|max:255|unique:roles,name',
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
                'name'  => $request->role,
                'guard_name' => 'web'
            );

            $role = Role::create($attr);

            if ($request->permission != null) {
                $role->syncPermissions($request->permission);
            }

            return response()->json([
                'success' => true,
                'message' => 'Record has been saved',
                'error' => [],
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save role. Please try again.',
                'error' => [],
            ], 500);
        }
    }

    public function selectroles(Request $request)
    {
        try {
            $role = Role::with('permissions')->find($request->id);

            if (empty($role->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role Not Found. Please try again.',
                ], 200);
            }

            return response()->json([
                'success' => true,
                'role' => $role
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get data. Please try again.',
            ], 500);
        }
    }

    public function updateroles(Request $request)
    {
        try {

            $role = Role::find($request->id);

            if ($role->name == $request->role) {

                $rules = [
                    'role.required' => 'Please enter role',
                ];

                $valid_fields = [
                    'role' => 'required|string|max:255',
                ];
            } else {
                $rules = [
                    'role.required' => 'Please enter role',
                    'role.unique' => 'This role already exists.',
                ];

                $valid_fields = [
                    'role' => 'required|string|max:255|unique:roles,name',
                ];
            }


            $validator = Validator::make($request->all(), $valid_fields, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to update the record.",
                    'error' => $validator->errors()
                ], 200);
            }

            if ($request->id != 0) {
                $role = Role::where('id', $request->id)->first();

                $role->update(['name' => $request->role]);
                $role->syncPermissions($request->permission);

                return response()->json([
                    'success' => true,
                    'message' => 'Record has been saved',
                    'error' => [],
                    'attr' => $request->permission
                ], 200);
            } else {
                throw new \Exception("ID not found");
            }
        } catch (\Exception $err) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save roles. Please try again.',
                'error' => [],
                'err' => $err
            ], 500);
        }
    }

    public function deleteroles(Request $request)
    {
        try {
            if (!empty($request->id)) {
                Role::where('id', $request->id)->delete();
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
                'message' => 'Failed to delete role. Please try again.',
                'error' => [],
            ], 500);
        }
    }
}
