<?php

namespace App\Http\Requests;


class UpdatePerfilRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $perfilId = $this->route('perfil')->id;

        return [
            'nome' => 'required|string|max:100|unique:perfis,nome,' . $perfilId,
            'descricao' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do perfil é obrigatório.',
            'nome.unique' => 'Este perfil já existe.',
            'nome.max' => 'O nome do perfil deve ter no máximo 100 caracteres.',
        ];
    }
}
