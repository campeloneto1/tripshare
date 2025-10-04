<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermissaoController;

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
        Route::get('user', [AuthController::class, 'user']);
    });
    Route::apiResources([
        'users' => UserController::class,
        'perfis' => PerfilController::class,
        'permissoes' => PermissaoController::class,
    ]);
});
