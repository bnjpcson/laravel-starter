<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PermissionsController;
use App\Http\Controllers\API\RolesController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('auth')->group(function () {
    // api/auth/

    Route::middleware('auth:api')->group(function () {
        Route::get('/init', [AuthController::class, 'init'])->name('auth.init');
        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
});


Route::prefix('user')->group(function () {
    // api/user/

    Route::middleware('can:user-list')->group(function () {
        Route::get('/getusers', [UserController::class, 'getusers'])->name('user.getusers');
        Route::post('/createuser', [UserController::class, 'createuser'])->middleware('can:user-create')->name('user.createuser');
        Route::get('/selectuser', [UserController::class, 'selectuser'])->name('user.selectuser');
        Route::get('/selectuserrolespermission', [UserController::class, 'selectuserrolespermission'])->name('user.selectuserrolespermission');
        Route::post('/updateuser', [UserController::class, 'updateuser'])->middleware('can:user-edit')->name('user.updateuser');
        Route::post('/deleteuser', [UserController::class, 'deleteuser'])->middleware('can:user-delete')->name('user.deleteuser');
    });

    Route::get('/rolespermissions', [UserController::class, 'rolespermissions'])->name('user.rolespermissions');
    Route::post('/updateprofile/{id}', [UserController::class, 'updateprofile'])->name('user.updateprofile');
});

Route::prefix('permissions')->group(function () {
    // api/permissions/
    Route::get('/getallpermission', [PermissionsController::class, 'getallpermission'])->name('permissions.getallpermission');

    Route::middleware('can:permission-list')->group(function () {
        Route::get('/getpermission', [PermissionsController::class, 'getpermission'])->name('permissions.getpermission');
        Route::post('/createpermission', [PermissionsController::class, 'createpermission'])->middleware('can:permission-create')->name('permissions.createpermission');
        Route::get('/selectpermission', [PermissionsController::class, 'selectpermission'])->name('permissions.selectpermission');
        Route::post('/updatepermission', [PermissionsController::class, 'updatepermission'])->middleware('can:permission-edit')->name('permissions.updatepermission');
        Route::post('/deletepermission', [PermissionsController::class, 'deletepermission'])->middleware('can:permission-delete')->name('permissions.deletepermission');
    });
});

Route::prefix('roles')->middleware('can:role-list')->group(function () {
    // api/roles/
    Route::get('/getallroles', [RolesController::class, 'getallroles'])->name('roles.getallroles');

    Route::middleware('can:role-list')->group(function () {
        Route::get('/getroles', [RolesController::class, 'getroles'])->name('roles.getroles');
        Route::post('/createroles', [RolesController::class, 'createroles'])->middleware('can:role-create')->name('roles.createroles');
        Route::get('/selectroles', [RolesController::class, 'selectroles'])->middleware('can:role-edit')->name('roles.selectroles');
        Route::post('/updateroles', [RolesController::class, 'updateroles'])->middleware('can:role-edit')->name('roles.updateroles');
        Route::post('/deleteroles', [RolesController::class, 'deleteroles'])->middleware('can:role-delete')->name('roles.deleteroles');
    });
});
