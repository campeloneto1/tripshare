<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripDayController;

// Teste rápido
Route::get('teste', function () {
    return ['ok' => true];
});

// Login com throttle
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');

// Grupo de rotas protegidas
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('check', [AuthController::class, 'check']);
        Route::get('profile', [AuthController::class, 'user']);
    });

    Route::apiResources([
        'users' => UserController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
        'trips' => TripController::class,
        'trips.days' => TripDayController::class,
    ]);

    Route::post('roles/{role}/permissions', [RoleController::class, 'permissions']);
    Route::post('roles/{role}/permissions/sync', [RoleController::class, 'syncPermissions']);
});
