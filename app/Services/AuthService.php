<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Autenticar usuário e gerar token
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \InvalidArgumentException('Credenciais inválidas.');
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout: revogar todos os tokens do usuário
     */
    public function logout(User $user): bool
    {
        $user->tokens()->delete();
        return true;
    }
}
