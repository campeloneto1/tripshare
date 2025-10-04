<?php

namespace App\Http\Requests;


class UpdateUserRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        $userId = $this->route('user')->id ?? null; // pega o id do usuário da rota

        return [
            'name' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username,' . $userId,
            'phone' => 'nullable|string|max:15',
            'cpf' => 'required|string|size:11|unique:users,cpf,' . $userId,
            'email' => 'required|email|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ];
    }

     public function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.max' => 'O nome deve ter no máximo 150 caracteres.',

            'username.required' => 'O campo usuário é obrigatório.',
            'username.max' => 'O usuário deve ter no máximo 50 caracteres.',
            'username.unique' => 'Este nome de usuário já está em uso.',

            'phone.max' => 'O telefone deve ter no máximo 15 caracteres.',

            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.size' => 'O CPF deve ter exatamente 11 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',

            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',

            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',

             'role_id.required' => 'O role do usuário é obrigatório.',
            'role_id.exists' => 'O role selecionado não existe.',
        ];
    }
}
