<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        //User Record
        if ($request->is('api/users/')) {
            if ($user->can('user-list')) {
                return $next($request);
            }
        }

        //User Delete
        if ($request->is('api/user/delete')) {
            if ($user->can('user-delete')) {
                return $next($request);
            }
        }

        //User Roles Permissions
        if ($request->is('api/user/roles_permissions')) {
            return $next($request);
        }

        return abort(401, 'Unauthorized');
    }
}
