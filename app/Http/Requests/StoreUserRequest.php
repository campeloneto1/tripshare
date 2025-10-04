<?php

namespace App\Http\Requests;


class StoreUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'telefone' => 'nullable|string|max:15',
            'cpf' => 'required|string|size:11|unique:users,cpf',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'perfil_id' => 'required|exists:perfis,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 150 caracteres.',

            'username.required' => 'O campo usuário é obrigatório.',
            'username.max' => 'O usuário deve ter no máximo 50 caracteres.',
            'username.unique' => 'Este nome de usuário já está em uso.',

            'telefone.max' => 'O telefone deve ter no máximo 15 caracteres.',

            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 11 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',

            'perfil_id.required' => 'O perfil do usuário é obrigatório.',
            'perfil_id.exists' => 'O perfil selecionado não existe.',
        ];
    }
}
