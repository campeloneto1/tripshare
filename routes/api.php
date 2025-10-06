<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripDayCityController;
use App\Http\Controllers\TripDayController;
use App\Http\Controllers\TripDayEventController;
use App\Http\Controllers\TripUserController;
use App\Http\Controllers\UserFollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;


// Login com throttle agressivo (5 tentativas por minuto)
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Grupo de rotas protegidas com rate limiting (60 requisições por minuto)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('check', [AuthController::class, 'check']);
        Route::get('profile', [AuthController::class, 'user']);
    });

    Route::apiResources([
        'permissions' => PermissionController::class,
        'posts' => PostController::class,
        'posts.comments' => PostCommentController::class,
        'posts.likes' => PostLikeController::class,
        'roles' => RoleController::class,
        'trips' => TripController::class,
        'trips.days' => TripDayController::class,
        'trips.days.cities' => TripDayCityController::class,
        'trips.days.cities.events' => TripDayEventController::class,
        'trips.users' => TripUserController::class,
        'users' => UserController::class,
        'users.follows' => UserFollowController::class,
        
    ]);

    Route::post('roles/{role}/permissions', [RoleController::class, 'permissions']);
    Route::post('roles/{role}/permissions/sync', [RoleController::class, 'syncPermissions']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread', [NotificationController::class, 'unread']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('{id}', [NotificationController::class, 'destroy']);
    });

     // Places API com rate limiting mais restritivo (API externa)
     Route::prefix('places')->middleware('throttle:30,1')->group(function () {
        Route::get('search', [PlaceController::class, 'search']);
        Route::get('nearby', [PlaceController::class, 'nearby']);
        Route::get('{id}', [PlaceController::class, 'details']);
     });
});
