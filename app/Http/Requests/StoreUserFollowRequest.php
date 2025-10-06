<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class StoreUserFollowRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'following_id' => [
                'required',
                'integer',
                'exists:users,id',
                'different:auth_id', // não pode seguir a si mesmo
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'following_id.required' => 'O campo do usuário a ser seguido é obrigatório.',
            'following_id.integer' => 'O identificador do usuário deve ser um número inteiro.',
            'following_id.exists' => 'O usuário que você está tentando seguir não foi encontrado.',
            'following_id.different' => 'Você não pode seguir a si mesmo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auth_id' => Auth::id(),
        ]);
    }
}
