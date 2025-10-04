<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $data = $this->authService->login($request->only('email', 'password'));
            return response()->json([
                'user' => UserResource::make($data['user']),
                'token' => $data['token'],
            ]);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'email' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(null, 204);
    }

    public function check(){
        return Auth::check();
    }

    public function user(){
        return UserResource::make(Auth::user());
    }
}
