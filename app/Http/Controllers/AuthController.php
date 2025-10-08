<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        ) {}

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

    public function register(RegisterUserRequest $request)
    {
        try {
            $data = $request->validated();

            $user = $this->authService->register($data);
            return response()->json([
                "message" => "Usuário registrado com sucesso",
                "data" => UserResource::make($user)
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
