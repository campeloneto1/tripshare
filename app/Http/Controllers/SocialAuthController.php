<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redireciona para o provider OAuth
     */
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Callback do provider OAuth (primeiro login social)
     */
    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Falha na autenticação com ' . $provider
            ], 401);
        }

        // Verifica se já existe uma conta social vinculada
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            // Atualiza tokens e faz login
            $socialAccount->update([
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
            ]);

            $token = $socialAccount->user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $socialAccount->user,
                'token' => $token,
            ]);
        }

        // Verifica se já existe um usuário com este email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user && $user->password !== null) {
            // Usuário já tem conta com senha - precisa confirmar senha para vincular
            return response()->json([
                'error' => 'Este email já está cadastrado. Por segurança, faça login com sua senha e vincule a conta social nas configurações.',
                'requires_password' => true,
            ], 409);
        }

        if (!$user) {
            // Cria novo usuário sem senha (login apenas via social)
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'username' => $this->generateUsername($socialUser->getEmail()),
                'cpf' => null, // CPF pode ser solicitado depois
                'password' => null,
                'email_verified_at' => now(),
            ]);
        }

        // Verifica se o usuário já tem esta rede social vinculada
        $existingLink = $user->socialAccounts()
            ->where('provider', $provider)
            ->first();

        if ($existingLink) {
            // Já vinculado, apenas atualiza tokens e faz login
            $existingLink->update([
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
            ]);
        } else {
            // Vincula conta social ao usuário
            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Vincula conta social a um usuário autenticado (requer senha)
     */
    public function linkAccount(Request $request, string $provider)
    {
        $this->validateProvider($provider);

        $request->validate([
            'password' => 'required|string',
            'provider_token' => 'required|string', // Token retornado pelo provider no frontend
        ]);

        $user = $request->user();

        // Verifica senha se o usuário tiver uma
        if ($user->password && !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Senha incorreta.'
            ], 401);
        }

        // Busca os dados do usuário social usando o token fornecido
        try {
            $socialUser = Socialite::driver($provider)->userFromToken($request->provider_token);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Token inválido ou expirado.'
            ], 401);
        }

        // Verifica se já está vinculada
        if ($user->socialAccounts()->where('provider', $provider)->exists()) {
            return response()->json([
                'error' => 'Conta ' . $provider . ' já está vinculada.'
            ], 422);
        }

        // Verifica se esta conta social já está vinculada a outro usuário
        $existingSocial = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingSocial) {
            return response()->json([
                'error' => 'Esta conta ' . $provider . ' já está vinculada a outro usuário.'
            ], 422);
        }

        // Vincula conta social
        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
        ]);

        return response()->json([
            'message' => 'Conta ' . $provider . ' vinculada com sucesso.',
            'social_accounts' => $user->socialAccounts,
        ]);
    }

    /**
     * Remove vinculação de conta social
     */
    public function unlinkAccount(Request $request, string $provider)
    {
        $this->validateProvider($provider);

        $user = $request->user();

        $socialAccount = $user->socialAccounts()->where('provider', $provider)->first();

        if (!$socialAccount) {
            return response()->json([
                'error' => 'Conta ' . $provider . ' não está vinculada.'
            ], 404);
        }

        // Verifica se tem senha antes de desvincular (segurança)
        if (!$user->password && $user->socialAccounts()->count() === 1) {
            return response()->json([
                'error' => 'Você precisa definir uma senha antes de desvincular sua única conta social.'
            ], 422);
        }

        $socialAccount->delete();

        return response()->json([
            'message' => 'Conta ' . $provider . ' desvinculada com sucesso.',
        ]);
    }

    /**
     * Define senha para usuário que usa apenas login social
     */
    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Senha definida com sucesso. Agora você pode fazer login com email e senha.',
        ]);
    }

    /**
     * Valida se o provider é suportado
     */
    private function validateProvider(string $provider)
    {
        $allowedProviders = ['google', 'facebook', 'github'];

        if (!in_array($provider, $allowedProviders)) {
            abort(404);
        }
    }

    /**
     * Gera um username único baseado no email
     */
    private function generateUsername(string $email): string
    {
        $base = Str::before($email, '@');
        $username = Str::slug($base);

        $count = 1;
        while (User::where('username', $username)->exists()) {
            $username = Str::slug($base) . $count;
            $count++;
        }

        return $username;
    }
}
