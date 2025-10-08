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

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Callback do provider OAuth
     */
    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'Falha na autenticação com ' . $provider]);
        }

        // Verifica se já existe uma conta social vinculada
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            // Atualiza tokens
            $socialAccount->update([
                'provider_token' => $socialUser->token,
                'provider_refresh_token' => $socialUser->refreshToken,
            ]);

            Auth::login($socialAccount->user);
            return redirect('/dashboard');
        }

        // Verifica se já existe um usuário com este email
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            // Cria novo usuário
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'username' => $this->generateUsername($socialUser->getEmail()),
                'cpf' => '', // CPF pode ser solicitado depois
                'password' => Hash::make(Str::random(32)), // Senha aleatória
                'email_verified_at' => now(),
            ]);
        }

        // Vincula conta social ao usuário
        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
        ]);

        Auth::login($user);
        return redirect('/dashboard');
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
