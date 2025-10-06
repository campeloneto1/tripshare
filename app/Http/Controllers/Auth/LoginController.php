<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/horizon';

    protected function authenticated(Request $request, $user)
    {
        if ($user->role_id !== 1) {
            auth()->logout();

            throw ValidationException::withMessages([
                $this->username() => ['Você não tem permissão para acessar esta área.'],
            ]);
        }
    }
}
