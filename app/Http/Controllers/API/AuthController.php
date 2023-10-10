<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function init()
    {
        $user = Auth::user();
        return response()->json(['user' => $user], 200);
    }

    public function login(AuthLoginRequest $request)
    {

        if (!Auth::attempt(['email' => $request->get('email'), 'password' => $request->get('password')], true)) {
            return response()->json(['success' => false, 'error' => ['Invalid credentials']], 200);
        }

        
        $user = Auth::user();
        $user->last_login = Carbon::now()->toDateTimeString();
        $user->save();

        $accessToken = Auth::user()->createToken('authToken')->accessToken;

        $user_roles = Auth::user()->roles->pluck('name')->all();


        $user_permissions = Auth::user()->getAllPermissions()->pluck('name');

        return response()->json([
            'success' => true,
            'error' => [],
            'user' => Auth::user(),
            'access_token' => $accessToken,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['success' => 'You have been successfully logged out'], 200);
    }
}
